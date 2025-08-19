<?php
require __DIR__ . '/bot/db_mysqli.php';

$user_id = $_GET['user_id'] ?? null;

// Agar foydalanuvchi ID berilmagan bo‘lsa, to‘xtatamiz
if (!$user_id || !is_numeric($user_id)) {
    die("❌ Foydalanuvchi aniqlanmadi.");
}

$stmt = $conn->prepare("SELECT * FROM users WHERE tg_user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Agar user topilmasa
if (!$user) {
    die("❌ Foydalanuvchi topilmadi.");
}
?>

<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <title>Shaxsiy kabinet</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-dark text-white">
<div class="container mt-4">
    <h2 class="mb-4">👤 Shaxsiy Kabinet</h2>

    <div class="card bg-secondary text-white mb-3">
        <div class="card-body">
            <h5 class="card-title">Ismi: <?= htmlspecialchars($user['full_name']) ?></h5>
            <p class="card-text">📞 Telefon: <?= htmlspecialchars($user['phone']) ?></p>
            <p class="card-text">📍 Manzil: <?= !empty($user['address']) ? htmlspecialchars($user['address']) : "Manzil belgilanmagan" ?></p>
            <p class="card-text">💳 To‘lov turi: <?= !empty($user['payment_method']) ? htmlspecialchars($user['payment_method']) : "Tanlanmagan" ?></p>
        </div>
    </div>

    <a href="orders.php?user_id=<?= $user_id ?>" class="btn btn-warning w-100 mb-3">💼 Buyurtmalar Tarixi</a>
    <a href="edit_profile.php?user_id=<?= $user_id ?>" class="btn btn-outline-light w-100">⚙️ Profilni tahrirlash</a>
</div>

</body>
</html>
