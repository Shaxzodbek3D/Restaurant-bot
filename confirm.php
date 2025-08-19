<?php
// PHP xatolarini yashirish (Ishlab chiqarish muhiti uchun tavsiya etiladi)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ma'lumotlar bazasi ulanishi va xarita konfiguratsiyasini yuklash
require __DIR__ . '/bot/db_mysqli.php';
require_once __DIR__ . '/map_config.php'; 

// Sessionni boshlash
session_start();

// User ID ni olish
$user_id = $_POST['user_id'] ?? $_GET['user_id'] ?? $_SESSION['user_id'] ?? null;

// User ID ni sessiyada saqlash
if (!empty($user_id)) {
    $_SESSION['user_id'] = $user_id;
}

// Items va total ni olish: avval POST dan, keyin sessiyadan
$items_json = $_POST['items'] ?? ($_SESSION['current_order_items'] ?? '[]');
$total = (int) ($_POST['total'] ?? ($_SESSION['current_order_total'] ?? 0));

// Itemlar va totalni sessiyada saqlash
$_SESSION['current_order_items'] = $items_json;
$_SESSION['current_order_total'] = $total;

if (empty($user_id)) {
    die("\u274c Foydalanuvchi aniqlanmadi.");
}

// Buyurtma yuborilsa (asosiy forma submit bo'lganda)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_type'])) {
    $full_name = $_POST['full_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $payment_method = $_POST['payment_method'] ?? '';
    $order_type = $_POST['order_type'] ?? '';
    $address = $_POST['address'] ?? '';
    $latitude = $_POST['latitude'] ?? null;
    $longitude = $_POST['longitude'] ?? null;

    $user_id_for_db = (string)$user_id;
    $status_val = 'Yangi';
    $created_at_val = date('Y-m-d H:i:s'); 

    // Prepared Statements orqali xavfsizroq yozish
    $stmt = $conn->prepare("INSERT INTO `orders` (`tg_user_id`, `name`, `phone`, `address`, `items`, `total`, `payment_type`, `order_type`, `status`, `created_at`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssissss", $user_id_for_db, $full_name, $phone, $address, $items_json, $total, $payment_method, $order_type, $status_val, $created_at_val);

    if ($stmt->execute()) {
        echo "<script>
                // Buyurtma jo'natilgandan so'ng localStorage'ni tozalash
                localStorage.removeItem('saved_checkout_name');
                localStorage.removeItem('saved_checkout_phone');
                // Yangi sahifaga o'tish
                window.location.href='confirm_success.php?user_id=" . urlencode($user_id_for_db) . "';
              </script>";
        exit;
    } else {
        die("\u274c Buyurtma yozishda xatolik: " . $stmt->error);
    }
}

// Foydalanuvchi ma'lumotlarini olish
$current_address = ['address' => '', 'latitude' => null, 'longitude' => null];
$stmt = $conn->prepare("SELECT full_name, phone, payment_method, address, latitude, longitude FROM users WHERE tg_user_id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($user && !empty($user['address'])) {
    $current_address['address'] = $user['address'];
    $current_address['latitude'] = $user['latitude'];
    $current_address['longitude'] = $user['longitude'];
} else {
    $stmt = $conn->prepare("SELECT address, latitude, longitude FROM user_addresses WHERE tg_user_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $latest_user_address = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($latest_user_address) {
        $current_address = $latest_user_address;
    }
}
?>
<!DOCTYPE html>
<html lang="uz">
<head>
  <meta charset="UTF-8">
  <title>Buyurtma</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css?v=<?= time(); ?>">
  <style>
    body { background: #111; color: #fff; font-family: 'Nunito', sans-serif; }
    .container { max-width: 480px; margin: auto; background: #1f1f1f; padding: 20px; border-radius: 16px; margin-top: 80px; }
    h2 { color: #d4af37; margin-bottom: 25px; font-size: 1.2rem; }
    .form-control, .form-select { background: #333; color: #fff; border: 1px solid #555; }
    .form-control::placeholder { color: #888; }
    .form-control:focus, .form-select:focus { background: #444; color: #fff; border-color: #d4af37; box-shadow: none; }
    .btn-gold { background: #d4af37; color: #000; font-weight: bold; border: none; }
    .dark-header { background-color: #1a1a1a; border-bottom: 1px solid #333; }
  </style>
</head>
<body>
  <header class="d-flex justify-content-between align-items-center px-3 py-2 fixed-top dark-header">
    <div class="d-flex align-items-center gap-2">
        <img src="logo.png" alt="Chinor" style="height: 40px;" />
        <img src="logoT.png" alt="Chinor" style="max-width: 100px; margin-left: 20px;" />
    </div>
    <form method="get" action="cart.php" class="d-inline">
        <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id) ?>">
        <button type="submit" class="btn btn-back">Ortga</button>
    </form>
  </header>

  <div class="container">
    <h2>Buyurtma yetib borishi uchun ma'lumotlarni aniq to'ldiring</h2>
    <form method="POST" action="confirm.php?user_id=<?= htmlspecialchars($user_id) ?>">
      
      <div class="mb-3">
        <label for="full_name_input" class="form-label">Ismingiz</label>
        <input type="text" id="full_name_input" name="full_name" class="form-control" required value="<?= htmlspecialchars($user['full_name'] ?? '') ?>">
      </div>
      
      <div class="mb-3">
          <label for="phone" class="form-label">Telefon raqamingiz</label>
          <div class="input-group">
            <span class="input-group-text bg-secondary border-secondary text-white">+998</span>
            <input type="number" id="phone" name="phone" class="form-control" placeholder="931234567" required value="<?= htmlspecialchars(preg_replace('/^\+998/', '', $user['phone'] ?? '')) ?>">
          </div>
      </div>

      <div class="mb-4">
        <input type="hidden" name="items" value="<?= htmlspecialchars($items_json) ?>">
        <input type="hidden" name="total" value="<?= htmlspecialchars($total) ?>">
        <label class="form-label fw-bold text-gold">📍 Yetkazib berish manzili:</label>
        <div class="d-flex flex-column gap-2">
          <input type="text" id="address" name="address" class="form-control" placeholder="Manzilni kiriting yoki tanlang" required value="<?= htmlspecialchars($current_address['address'] ?? '') ?>">
          <div class="d-flex gap-2">
             <button type="button" class="btn btn-outline-warning w-100" onclick="window.location.href='edit_address.php?user_id=<?= htmlspecialchars($user_id) ?>&redirect=confirm.php?user_id=<?= htmlspecialchars($user_id) ?>'">
                🗺️ Xaritadan tanlash
             </button>
             <button type="button" class="btn btn-outline-info w-100" onclick="openSavedAddresses()">
                🏠 Saqlanganlar
             </button>
          </div>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">To‘lov turi</label>
        <select name="payment_method" class="form-select">
          <option value="Naqd" <?= ($user['payment_method'] ?? '') === 'Naqd' ? 'selected' : '' ?>>Naqd</option>
          <option value="Click" <?= ($user['payment_method'] ?? '') === 'Click' ? 'selected' : '' ?>>Click (qabul qilinganda)</option>
          <option value="Payme" <?= ($user['payment_method'] ?? '') === 'Payme' ? 'selected' : '' ?>>Payme (qabul qilinganda)</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Buyurtma turi</label>
        <select name="order_type" id="orderType" class="form-select">
          <option value="dostavka">Yetkazib berish</option>
          <option value="olib_ketaman">Olib ketaman</option>
        </select>
      </div>

      <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id) ?>">
      <button type="submit" class="btn btn-gold w-100 p-2">🚀 Buyurtma berish</button>
    </form>
  </div>

  <div class="modal fade" id="savedAddressModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content bg-dark text-white border-gold rounded-4">
        <div class="modal-header border-0">
          <h5 class="modal-title text-gold">📍 Saqlangan manzillar</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="saved-address-list"><p class="text-muted">Yuklanmoqda...</p></div>
      </div>
    </div>
  </div>

  <footer id="pickupFooter" class="mt-4 text-center text-light small" style="display: none; padding: 15px 10px; background-color: #1a1a1a;">
    <div class="text-muted">
      📍 Bizning manzil: <strong>Buxoro shahri, Guliston ko‘chasi, 5-uy</strong><br>
      🕑 Ish vaqti: <strong>Har kuni 10:00 – 23:00</strong>
    </div>
  </footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Bu skriptlar o'zgarishsiz qoladi
    function selectAddress(address, latitude, longitude) {
        document.getElementById("address").value = address;
    }

    function openSavedAddresses() {
        fetch(`get_saved_addresses.php?user_id=<?= $user_id ?>`)
            .then(res => res.json())
            .then(data => {
                const container = document.getElementById("saved-address-list");
                container.innerHTML = "";
                if (!data || data.length === 0) {
                    container.innerHTML = "<p class='text-muted'>Saqlangan manzillar topilmadi</p>";
                    return;
                }
                data.forEach(addr => {
                    const div = document.createElement("div");
                    div.className = "border rounded p-2 mb-2 bg-secondary bg-opacity-10 cursor-pointer";
                    div.style.cursor = "pointer";
                    div.innerHTML = `<div>${addr.address}</div>`;
                    div.onclick = () => {
                        selectAddress(addr.address, addr.latitude, addr.longitude);
                        bootstrap.Modal.getInstance(document.getElementById("savedAddressModal")).hide();
                    };
                    container.appendChild(div);
                });
                new bootstrap.Modal(document.getElementById("savedAddressModal")).show();
            })
            .catch(err => {
                console.error("Manzillarni olishda xatolik:", err);
                document.getElementById("saved-address-list").innerHTML = "<p class='text-danger'>Xatolik yuz berdi</p>";
            });
    }

    const orderTypeSelect = document.getElementById("orderType");
    const pickupFooter = document.getElementById("pickupFooter");
    orderTypeSelect.addEventListener("change", function () {
        pickupFooter.style.display = this.value === "olib_ketaman" ? "block" : "none";
    });
    window.addEventListener("DOMContentLoaded", () => {
        pickupFooter.style.display = orderTypeSelect.value === "olib_ketaman" ? "block" : "none";
    });
</script>

<script>
    const nameInput = document.getElementById('full_name_input');
    const phoneInput = document.getElementById('phone');
    const mainForm = document.querySelector('form[action*="confirm.php"]');

    // Ma'lumotni saqlash
    if (nameInput) {
        nameInput.addEventListener('input', () => {
            localStorage.setItem('saved_checkout_name', nameInput.value);
        });
    }
    if (phoneInput) {
        phoneInput.addEventListener('input', () => {
            localStorage.setItem('saved_checkout_phone', phoneInput.value);
        });
    }

    // Sahifa yuklanganda ma'lumotni qayta yuklash
    window.addEventListener('load', () => {
        const savedName = localStorage.getItem('saved_checkout_name');
        if (savedName && nameInput) {
            // Agar bazadan kelgan ismdan farqli bo'lsa, saqlanganni ishlatamiz
            // Bu foydalanuvchi o'zgartirganini bildiradi
            nameInput.value = savedName;
        }

        const savedPhone = localStorage.getItem('saved_checkout_phone');
        if (savedPhone && phoneInput) {
            phoneInput.value = savedPhone;
        }
    });

    // Buyurtma jo'natilgach, xotirani tozalash
    if(mainForm) {
        mainForm.addEventListener('submit', () => {
            // Formadagi validatsiya to'g'ri o'tsa, submit bo'ladi va bu yer ishlaydi
            localStorage.removeItem('saved_checkout_name');
            localStorage.removeItem('saved_checkout_phone');
        });
    }
</script>

<!--<footer class="py-3 mt-5" style="background-color: #1a1a1a; border-top: 1px solid #333;">-->
<!--    <div class="container text-center text-muted small">-->
<!--        <p class="mb-1">-->
<!--            👨💻 Texnik qo'llab-quvvatlash va yangi loyihalar uchun:-->
<!--        </p>-->
<!--        <a href="https://t.me/Furqatovich_CG" target="_blank" class="link-light fw-bold text-decoration-none">Bog'lanish</a>-->
<!--    </div>-->
<!--</footer>-->

</body>
</html>