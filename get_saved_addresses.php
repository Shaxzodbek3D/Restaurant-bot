<?php
// PHP xatolarini yashirish (Ishlab chiqarish muhiti uchun tavsiya etiladi)
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL); // Xatolarni logga yozish uchun

require_once __DIR__ . '/bot/db_mysqli.php'; // Bu qator mavjud bo'lishi kerak

header('Content-Type: application/json'); // JSON javob qaytarishni belgilaymiz

$user_id = $_GET['user_id'] ?? null;
if (empty($user_id)) { // Bo'shlikni ham tekshiramiz
    echo json_encode(['status' => 'error', 'message' => 'Foydalanuvchi IDsi topilmadi.']);
    exit;
}

// QATOR 1: tg_user_id VARCHAR bo'lgani uchun 's' ishlatamiz.
// QATOR 2: SELECT so'roviga latitude va longitude maydonlarini qo'shamiz.
$stmt = $conn->prepare("SELECT id, address, latitude, longitude, created_at FROM user_addresses WHERE tg_user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->bind_param("s", $user_id); // 'i' (integer) o'rniga 's' (string) qo'yamiz
$stmt->execute();
$result = $stmt->get_result();

$addresses = [];
while ($row = $result->fetch_assoc()) {
    $addresses[] = $row;
}

// JSON_UNESCAPED_UNICODE Unicode belgilarini to'g'ri kodlash uchun foydali
echo json_encode($addresses, JSON_UNESCAPED_UNICODE);
?>