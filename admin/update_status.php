<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../bot/db_mysqli.php';
require __DIR__ . '/../bot/send_telegram.php';

header("Content-Type: application/json");

$id = $_POST['id'] ?? null;
$status = $_POST['status'] ?? null;

if (!$id || !$status) {
    echo json_encode(["success" => false, "error" => "ID yoki holat yo'q"]);
    exit;
}

// "Kuryerga berildi" statusini bu faylda qayta ishlashni to'xtatamiz.
// Bu status faqat assign_courier.php orqali o'rnatilishi kerak.
if ($status === 'Kuryerga berildi') {
    // Hech narsa qilmaymiz va muvaffaqiyatli deb qaytaramiz, chunki
    // front-end bu statusni o'zi bekor qiladi va modal oynani ochadi.
    echo json_encode(["success" => true, "message" => "Courier assignment handled by modal."]);
    exit;
}

$stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $id);

if (!$stmt->execute()) {
    echo json_encode(["success" => false, "error" => "UPDATE bajarilmadi: " . $stmt->error]);
    $stmt->close();
    exit;
}
$stmt->close();

// ----- Telegram xabar (faqat "Qabul qilindi" uchun) -----
try {
    // Agar status "Qabul qilindi" bo'lsa
    if ($status === 'Qabul qilindi') {
        // Foydalanuvchi ID sini olish
        $stmt = $conn->prepare("SELECT tg_user_id FROM orders WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($order && !empty($order['tg_user_id'])) {
            $message = "✅ Buyurtmangiz qabul qilindi va tayyorlashga yuborildi!";
            sendTelegramToUser($order['tg_user_id'], $message);
        }
    }
} catch (Throwable $e) {
    // Xatolikni log faylga yozish mumkin, lekin jarayonni to'xtatmaymiz
    file_put_contents("telegram_error_log.txt", "Telegram xabarda xato: " . $e->getMessage() . "\n", FILE_APPEND);
}

echo json_encode(["success" => true]);
?>