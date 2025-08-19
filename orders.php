<?php
require __DIR__ . '/bot/db_mysqli.php';

$user_id = $_GET['user_id'] ?? null;
if (!$user_id) die("Foydalanuvchi aniqlanmadi.");

// c.full_name -> c.name ga o'zgartirilgan SQL so'rovi
$sql = "(SELECT 
            o.items, o.total, o.payment_type, o.created_at, o.status, 
            c.name AS courier_name 
         FROM orders o
         LEFT JOIN couriers c ON o.courier_id = c.id
         WHERE o.tg_user_id = ?)
        UNION ALL
        (SELECT 
            o.items, o.total, o.payment_type, o.created_at, o.status, 
            c.name AS courier_name 
         FROM orders_archive o
         LEFT JOIN couriers c ON o.courier_id = c.id
         WHERE o.tg_user_id = ?)
        ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="uz">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Barcha buyurtmalar</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
   <link rel="stylesheet" href="style.css?v=<?= time(); ?>">
  <style>
    body {
      background: #111;
      color: #fff;
      font-family: 'Nunito', sans-serif;
    }
    .order-card {
      background: #1f1f1f;
      border: 1px solid #333;
      border-radius: 12px;
      padding: 15px;
      margin-bottom: 15px;
    }
    .order-card h6 {
      color: #d4af37;
    }
    .badge-status {
      font-size: 0.85rem;
    }
  </style>
</head>


    <header class="d-flex justify-content-between align-items-center px-3 py-2 fixed-top dark-header ">
    <div class="d-flex align-items-center gap-2">
       <img src="logo.png" alt="Chinor" style="max-width: 50px; margin-left: 10px;" />
       <img src="logoT.png" alt="Chinor" style="max-width: 100px; margin-left: 20px;" />
    </div>
    <button class="btn btn-back" onclick="history.back()">Ortga</button>
  </header>

<body>
  <div class="container py-4">
    <h3 class="text-warning mb-4"><i class="bi bi-clock-history me-2"></i>Barcha buyurtmalar</h3>

    <?php if ($orders): ?>
      <?php foreach ($orders as $order): ?>
        <?php $decoded = json_decode($order['items'], true); ?>
        <div class="order-card">
          <h6 class="mb-2">Buyurtmam:</h6>
          <ul>
            <?php foreach ($decoded as $item): ?>
              <li><?= htmlspecialchars($item['name']) ?> x<?= $item['qty'] ?> - 1 donasi <?= number_format($item['price'], 0, '', ' ') ?> so'm</li>
            <?php endforeach; ?>
          </ul>
          <p class="mb-1">Umumiy: <strong><?= number_format($order['total'], 0, '', ' ') ?> so'm</strong></p>
          <p class="mb-1">To'lov turi: <?= htmlspecialchars($order['payment_type']) ?></p>
          <p class="mb-1">Sana: <?= $order['created_at'] ?></p>
          <p class="mb-1">Kuryer: <strong><?= htmlspecialchars($order['courier_name'] ?? 'Noma\'lum') ?></strong></p>
          <?php
    // Holatga qarab yozuv va rangni aniqlaymiz
    $status_text = '';
    $status_color_class = '';

    // Arxivdagi buyurtmalar uchun status odatda 0 yoki "Yakunlandi" kabi bo'lishi mumkin
    if ($order['status'] == 0 || strtolower($order['status']) === 'yakunlandi') {
        $status_text = 'Yakunlandi';
        $status_color_class = 'bg-success'; // Yashil rang
    } elseif (strtolower($order['status']) === 'bekor qilindi') {
        $status_text = 'Bekor qilindi';
        $status_color_class = 'bg-danger'; // Qizil rang
    } else {
        $status_text = htmlspecialchars($order['status']); // Boshqa holatlar uchun asl qiymat
        $status_color_class = 'bg-warning text-dark'; // Sariq rang
    }
?>
<span class="badge rounded-pill <?= $status_color_class ?> badge-status">📍 Holat: <?= $status_text ?></span>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="text-muted">Hech qanday buyurtma mavjud emas.</p>
    <?php endif; ?>
  </div>
</body>
</html>
