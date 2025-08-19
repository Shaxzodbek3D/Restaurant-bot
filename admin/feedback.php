<?php
// admin/feedback.php
require_once __DIR__ . '/../bot/config.php';
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mijozlarning Fikr-mulohazalari</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { padding: 20px; background-color: #f8f9fa; }
        .table thead { background-color: #ffc107; color: #333; }
        .card { margin-top: 20px; }
        .card-text { font-size: 1.1rem; }
        .btn-delete { font-size: 0.8rem; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>💬 Mijozlarning Fikr-mulohazalari</h2>
        <a href="dashboard.php" class="btn btn-outline-primary">← Dashboardga qaytish</a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead>
                        <tr>
                            <th style="width: 5%;">ID</th>
                            <th style="width: 50%;">Fikr-mulohaza matni</th>
                            <th style="width: 20%;">Mijoz</th>
                            <th style="width: 15%;">Yozilgan vaqt</th>
                            <th style="width: 10%;">Amallar</th> </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT f.id, f.feedback_text, f.created_at, u.full_name AS user_name
                                FROM feedback AS f
                                LEFT JOIN users AS u ON f.tg_user_id = u.tg_user_id
                                ORDER BY f.id DESC";

                        $result = $conn->query($sql);

                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr id='feedback-row-{$row['id']}'>"; // Har bir qatorga unikal ID beramiz
                                echo "<td>{$row['id']}</td>";
                                echo "<td>" . nl2br(htmlspecialchars($row['feedback_text'])) . "</td>";
                                echo "<td>" . htmlspecialchars($row['user_name'] ?? 'Noma\'lum') . "</td>";
                                echo "<td>" . date("d.m.Y H:i", strtotime($row['created_at'])) . "</td>";
                                // YANGI TUGMA QO'SHILDI
                                echo "<td>
                                        <button class='btn btn-danger btn-sm btn-delete' onclick='deleteFeedback({$row['id']})'>
                                            🗑️ O'chirish
                                        </button>
                                      </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center'>🤷‍♂️ Hozircha fikr-mulohazalar yo'q.</td></tr>";
                        }
                        $conn->close();
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function deleteFeedback(id) {
    Swal.fire({
        title: 'Ishonchingiz komilmi?',
        text: "Bu fikr-mulohazani qayta tiklab bo'lmaydi!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ha, o\'chirish!',
        cancelButtonText: 'Bekor qilish'
    }).then((result) => {
        if (result.isConfirmed) {
            // Serverga o'chirish uchun so'rov yuboramiz
            fetch('delete_feedback.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + id
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Qatorni sahifani qayta yuklamasdan olib tashlaymiz
                    document.getElementById('feedback-row-' + id).remove();
                    
                    Swal.fire(
                        'O\'chirildi!',
                        'Fikr-mulohaza muvaffaqiyatli o\'chirildi.',
                        'success'
                    );
                } else {
                    Swal.fire(
                        'Xatolik!',
                        'O\'chirishda xatolik yuz berdi: ' + data.error,
                        'error'
                    );
                }
            });
        }
    });
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>