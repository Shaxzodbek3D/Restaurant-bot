<?php
// admin/dashboard.php
// Markaziy sozlamalar va sessiyani chaqiramiz
require_once __DIR__ . '/../bot/config.php';
session_start(); 
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyurtmalar Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { background: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .container-fluid { max-width: 1600px; }
        .table thead { background-color: #212529; color: white; }
        ul { padding-left: 18px; margin-bottom: 0; list-style-type: none; }
       tbody tr.status-new td {
        background-color: #d1e7dd !important; /* Yashil rang */
   }
   tbody tr.status-accepted td {
        background-color: #fff3cd !important; /* Sariq rang (Qabul qilinganlar uchun) */
   }
   /* KURYERGA BERILGANLAR UCHUN YANGI STIL */
   tbody tr.status-courier td {
        background-color: #ffe8cc !important; /* Sabzi rang / To'q sariq */
   }
   tbody tr.status-completed td {
        background-color: #ffffff !important; /* Oq rang (Arxiv uchun) */
   }
        .card { box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<div class="container-fluid my-4">

    <?php
    // Sessiyadan kelgan "flash xabar"ni ko'rsatish bloki
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $alert_type = $message['type'] === 'success' ? 'alert-success' : 'alert-danger';
        
        echo "<div class='alert {$alert_type} alert-dismissible fade show' role='alert'>
                " . htmlspecialchars($message['text']) . "
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
              </div>";
        
        unset($_SESSION['flash_message']);
    }
    ?>

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h2 class="mb-0">Buyurtmalar Paneli
        <span class="badge bg-success ms-2" id="newCountBadge">0</span>
    </h2>
    <div class="d-flex flex-wrap gap-2">
        <a href="menu.php" class="btn btn-outline-primary">🍴 Menyuni boshqarish</a>
        <a href="archive.php" class="btn btn-outline-secondary">📁 Arxiv</a>
        <a href="courier_stats.php" class="btn btn-outline-info">📊 Kuryerlar</a>
        <a href="feedback.php" class="btn btn-outline-warning">💬 Fikr-lar</a>
        
        <form action="export_excel.php" method="GET" target="_blank" class="d-inline">
            <input type="hidden" name="source" value="dashboard">
           <a href="export_dashboard.php" class="btn btn-outline-success">📥 Bugungi hisobot</a>
        </form>
        
        <a href="yakunlash.php" class="btn btn-danger">🌙 Ish kunini yakunlash</a>
        </div>
</div>
    
    <div id="summary-container" class="row mb-4"></div>

   <form id="filterForm" class="mb-4 d-flex align-items-center gap-2 flex-wrap">
    <input type="text" id="searchInput" class="form-control" placeholder="Ism, telefon yoki manzil orqali qidiruv..." style="max-width: 350px;">
    <select id="filterSelect" class="form-select" style="max-width: 250px;">
        <option value="">Bugungi aktiv buyurtmalar</option>
        <option value="barchasi">Bugungi barcha buyurtmalar</option>
        <option value="Yangi">Faqat Yangilar</option>
        <option value="Qabul qilindi">Faqat Qabul qilinganlar</option>
        <option value="Kuryerga berildi">Faqat Kuryerga berilganlar</option>
        <option value="Yakunlandi">Bugun Yakunlanganlar</option>
    </select>
</form>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th><th>Mijoz</th><th>Telefon</th><th>Manzil</th><th>Buyurtmalar</th>
                    <th>Jami</th><th>To'lov</th><th>Turi</th><th>Kuryer</th><th>Holat</th>
                </tr>
            </thead>
            <tbody id="orders-table-body">
                </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="assignCourierModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">🚚 Kuryerni tanlang</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" id="assignOrderId">
                <select id="courierSelect" class="form-select">
                    <option value="">-- Kuryerni tanlang --</option>
                    <?php
                    // Kuryerlar ro'yxatini bazadan olamiz
                    $couriers_res = $conn->query("SELECT id, name FROM couriers WHERE is_active = 1 ORDER BY name ASC");
                    if ($couriers_res) {
                        while ($c = $couriers_res->fetch_assoc()) {
                            echo "<option value='{$c['id']}'>{$c['name']}</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="modal-footer"><button class="btn btn-success" onclick="assignCourier()">✅ Biriktirish</button></div>
        </div>
    </div>
</div>

<audio id="notifSound" src="ding.mp3" preload="auto"></audio>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tbody = document.getElementById('orders-table-body');
    const summaryContainer = document.getElementById('summary-container');
    const searchInput = document.getElementById('searchInput');
    const filterSelect = document.getElementById('filterSelect');
    let lastOrdersHTML = ''; // Qayta chizishning oldini olish uchun
    let newOrdersCount = 0;

    function fetchData() {
        const search = searchInput.value;
        const filter = filterSelect.value;
        const params = new URLSearchParams({ search, filter }).toString();

        // Jadvalni yangilash
        fetch(`load_orders.php?${params}`)
            .then(res => res.text())
            .then(html => {
                if (tbody.innerHTML !== html) {
                    tbody.innerHTML = html;
                }
            })
            .catch(err => console.error("Jadvalni yuklashda xatolik:", err));
        
        // Statistikani yangilash
        fetch('load_summary.php')
            .then(res => res.json())
            .then(data => {
                // Agar yangi buyurtma kelsa, ovoz chiqarish
                if (newOrdersCount !== null && data.yangi_count > newOrdersCount) {
                    document.getElementById('notifSound').play();
                }
                newOrdersCount = data.yangi_count;

                document.getElementById('newCountBadge').innerText = `${data.yangi_count} ta yangi`;
                summaryContainer.innerHTML = `
                    <div class="col-md-6 mb-3 mb-md-0"><div class="card h-100"><div class="card-body">
                        <h5 class="card-title">Bugungi naqd tushum (yakunlangan)</h5>
                        <p class="card-text fs-4">Soni: <strong>${data.naqd_count}</strong> / Summa: <strong>${new Intl.NumberFormat('uz-UZ').format(data.naqd_total)} so'm</strong></p>
                    </div></div></div>
                    <div class="col-md-6"><div class="card h-100"><div class="card-body">
                        <h5 class="card-title">Bugungi plastik tushum (yakunlangan)</h5>
                        <p class="card-text fs-4">Soni: <strong>${data.plastik_count}</strong> / Summa: <strong>${new Intl.NumberFormat('uz-UZ').format(data.plastik_total)} so'm</strong></p>
                    </div></div></div>`;
            }).catch(err => console.error("Statistikani yuklashda xatolik:", err));
    }
    
    // Filtrlar o'zgarganda ma'lumotni qayta yuklash
    searchInput.addEventListener('input', fetchData);
    filterSelect.addEventListener('change', fetchData);

    // Sahifa ochilganda va har 5 soniyada ma'lumotlarni yangilab turish
    fetchData();
    setInterval(fetchData, 5000);
});

// Dinamik qo'shilgan elementlar uchun hodisalarni document ga qo'shamiz
document.addEventListener('change', function(e) {
    if (e.target && e.target.classList.contains('status-select')) {
        const id = e.target.dataset.id;
        const status = e.target.value;
        fetch('update_status.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `id=${id}&status=${encodeURIComponent(status)}`
        })
        .then(res => res.json())
        .then(data => {
            if(!data.success) {
                Swal.fire('Xatolik!', 'Statusni o\'zgartirishda xatolik yuz berdi.', 'error');
            }
            // Muvaffaqiyatli bo'lsa, interval o'zi yangilaydi. Tezroq bo'lishi uchun bu yerda ham yangilash mumkin.
        });
    }
});

function openCourierModal(orderId) {
    document.getElementById('assignOrderId').value = orderId;
    const courierModal = new bootstrap.Modal(document.getElementById('assignCourierModal'));
    courierModal.show();
}

function assignCourier() {
    const orderId = document.getElementById("assignOrderId").value;
    const courierId = document.getElementById("courierSelect").value;
    if (!courierId) {
        Swal.fire('Xatolik!', 'Iltimos, kuryerni tanlang!', 'error');
        return;
    }
    
    const modalElement = document.getElementById('assignCourierModal');
    const modal = bootstrap.Modal.getInstance(modalElement);
    if(modal) modal.hide();

    fetch('assign_courier.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `order_id=${orderId}&courier_id=${courierId}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire({title: 'Bajarildi!', text: 'Kuryer muvaffaqiyatli biriktirildi!', icon: 'success', timer: 1500, showConfirmButton: false});
            // Ma'lumotni darhol yangilash uchun fetchData() ni chaqiramiz
            // Aslida setInterval o'zi yangilaydi, lekin bu tezroq natija beradi.
        } else {
            Swal.fire('Xatolik!', data.error || 'Noma\'lum xatolik', 'error');
        }
    });
}
</script>

</body>
</html>