<?php
require __DIR__ . '/bot/db_mysqli.php';
require_once __DIR__ . '/map_config.php';

$user_id = $_GET['user_id'] ?? null;
if (!$user_id) die("Foydalanuvchi aniqlanmadi.");

// O‘chirish (agar queryda delete_address_id bo‘lsa)
if (isset($_GET['delete_address_id'])) {
    $delete_id = (int) $_GET['delete_address_id'];
    $stmt = $conn->prepare("DELETE FROM user_addresses WHERE id = ? AND tg_user_id = ?");
    $stmt->bind_param("ii", $delete_id, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: edit_profile.php?user_id=$user_id");
    exit;
}

// Foydalanuvchini olish
$stmt = $conn->prepare("SELECT * FROM users WHERE tg_user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// So‘nggi 5 ta manzil
$stmt = $conn->prepare("SELECT * FROM user_addresses WHERE tg_user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$addresses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Yangilash
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $payment_method = $_POST['payment_method'] ?? '';
    $latitude = $_POST['latitude'] ?? null;
    $longitude = $_POST['longitude'] ?? null;

    // users jadvalini latitude va longitude ni *qo'shgan holda* yangilang
    $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ?, address = ?, payment_method = ?, latitude = ?, longitude = ? WHERE tg_user_id = ?");
    // bind_param da latitude va longitude uchun qo'shimcha "ss" qo'shilganiga e'tibor bering
    // String (s) : full_name, phone, address, payment_method, latitude, longitude
    // Integer (i) : user_id (tg_user_id int bo'lsa)
    // agar tg_user_id varchar bo'lsa, oxirgi "i" ni "s" ga o'zgartiring
    $stmt->bind_param("ssssssi", $full_name, $phone, $address, $payment_method, $latitude, $longitude, $user_id);
    $stmt->execute();
    $stmt->close();

    // Bu qism user_addresses ga *yangi* manzilni kiritadi, bu to'g'ri.
    $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM user_addresses WHERE tg_user_id = ? AND address = ?");
    $stmt->bind_param("is", $user_id, $address);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();

    if ($row['cnt'] == 0 && !empty($address)) {
        $stmt = $conn->prepare("INSERT INTO user_addresses (tg_user_id, address, latitude, longitude, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("isss", $user_id, $address, $latitude, $longitude);
        $stmt->execute();
        $stmt->close();
    }

    echo "<script>window.location.href = 'index.php';</script>";
    exit;
}

?>

<!DOCTYPE html>
<html lang="uz">
<head>
  <meta charset="UTF-8">
  <title>Profilni Tahrirlash</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css?v=<?= time(); ?>">
  <style>
    body {
      background: #111;
      color: #fff;
      font-family: 'Nunito', sans-serif;
    }
    .container {
      max-width: 480px;
      margin: auto;
      background: #1f1f1f;
      padding: 30px;
      border-radius: 16px;
      box-shadow: 0 0 10px rgba(212, 175, 55, 0.2);
    }
    h2 { color: #d4af37; margin-bottom: 25px; }
    .form-control, .form-select {
      background: #222;
      color: #fff;
      border: 1px solid #444;
    }
    .form-control:focus, .form-select:focus {
      border-color: #d4af37;
      box-shadow: 0 0 5px #d4af37;
    }
    .btn-gold { background: #d4af37; color: #000; font-weight: bold; }
    .address-block {
      background: #2a2a2a;
      padding: 10px;
      border-radius: 8px;
      margin-bottom: 10px;
    }
    .address-block small { font-size: 12px; color: #aaa; }
  </style>
</head>
<body style="margin-top: 80px;">
    
     <header class="d-flex justify-content-between align-items-center px-3 py-2 fixed-top dark-header ">
    <div class="d-flex align-items-center gap-2">
       <img src="logo.png" alt="Chinor" style="max-width: 50px; margin-left: 10px;" />
       <img src="logoT.png" alt="Chinor" style="max-width: 100px; margin-left: 20px;" />
    </div>
    <button class="btn btn-back" onclick="history.back()">Ortga</button>
  </header>

    
  <div class="container">
    <h2>⚙️ Profilni Tahrirlash</h2>
    <form method="POST">
      <div class="mb-3">
        <label>Ism</label>
        <input type="text" name="full_name" class="form-control" required value="<?= htmlspecialchars($user['full_name']) ?>">
      </div>
      <div class="mb-3">
        <label>Telefon</label>
        <input type="text" name="phone" class="form-control" required value="<?= htmlspecialchars($user['phone']) ?>">
      </div>

    <div class="mb-4">
  <label class="form-label fw-bold text-gold">📍 So‘nggi manzillar:</label>
  <div class="d-flex flex-column gap-3">
    <?php if ($addresses): ?>
      <?php foreach ($addresses as $addr): ?>
        <div class="address-block rounded border border-secondary p-3">
          <div class="mb-2 text-light fw-semibold">
            <?= htmlspecialchars($addr['address']) ?>
          </div>
          <div class="d-flex justify-content-start gap-2">
            <button type="button" class="btn btn-sm btn-outline-warning"
              onclick="document.getElementById('address').value='<?= htmlspecialchars($addr['address']) ?>';">
              ⭐ Asosiy qilish
            </button>
            <a href="?user_id=<?= $user_id ?>&delete_address_id=<?= $addr['id'] ?>"
               class="btn btn-sm btn-outline-danger"
               onclick="return confirm('Ushbu manzilni o‘chirmoqchimisiz?')">
              🗑 O‘chirish
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="text-muted">Manzil mavjud emas</p>
    <?php endif; ?>
  </div>
  
  
   <!-- Yangi manzil qo‘shish tugmasi -->
 <button type="button" class="btn btn-outline-warning" onclick="window.location.href='edit_address.php?user_id=<?= htmlspecialchars($user_id) ?>&redirect=<?= urlencode(basename($_SERVER['PHP_SELF']) . '?user_id=' . $user_id) ?>'">
  Yangi manzil qo'shish
</button>

  
  
</div>
    
      <button type="submit" class="btn btn-gold w-100">💾 Saqlash</button>
    </form>
  </div>
  
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
