<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/bot/db_mysqli.php';
header('Content-Type: application/json');
// Bu yerda error_reporting va display_errors qayta takrorlangan, bittasi yetarli
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

// GET orqali user_id ni olish
$user_id = $_GET['user_id'] ?? null;

// Debuging uchun: Olingan $user_id qiymatini logga yozish
error_log("get_user.php ga kelgan User ID (DEBUG): " . print_r($user_id, true));


if (empty($user_id)) { // empty() funksiyasi null, bo'sh satr, 0 ni ham tekshiradi
    error_log("get_user.php: Foydalanuvchi IDsi topilmadi yoki bo'sh.");
    echo json_encode(["error" => "Foydalanuvchi ID yo‘q yoki bo‘sh"]);
    exit;
}

try {
    // tg_user_id INTEGER bo'lganligi uchun "i" (integer) ishlatiladi
    $stmt = $conn->prepare("SELECT full_name, phone, address, payment_method FROM users WHERE tg_user_id = ?");
    if (!$stmt) throw new Exception("Prepare error: " . $conn->error);

    $stmt->bind_param("i", $user_id); // <<< "s" o'rniga "i" qilingan
    $stmt->execute();
    $result = $stmt->get_result();
    if (!$result) throw new Exception("Execute error: " . $stmt->error);

    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user) {
        echo json_encode($user, JSON_UNESCAPED_UNICODE); // Kirill harflari uchun UNESCAPED_UNICODE qo'shildi
    } else {
        echo json_encode(["error" => "Foydalanuvchi topilmadi"]);
    }
} catch (Exception $e) {
    error_log("get_user.php: Database xatosi: " . $e->getMessage()); // Xatolikni logga yozish
    echo json_encode(["error" => "Xatolik: " . $e->getMessage()]);
}
?>