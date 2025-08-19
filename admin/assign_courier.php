<?php
// assign_courier.php

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Kerakli fayllarni to'g'ri yo'l bilan ulaymiz
require __DIR__ . '/../bot/db_mysqli.php';
require __DIR__ . '/../bot/send_telegram.php';

header("Content-Type: application/json");

$order_id = $_POST['order_id'] ?? null;
$courier_id = $_POST['courier_id'] ?? null;

if (!$order_id || !$courier_id) {
    echo json_encode(["success" => false, "error" => "Buyurtma ID yoki Kuryer ID yetishmayapti."]);
    exit;
}

// Kuryer ma'lumotlarini olamiz
$stmt = $conn->prepare("SELECT name, phone, telegram_id FROM couriers WHERE id = ?");
$stmt->bind_param("i", $courier_id);
$stmt->execute();
$courier = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$courier) {
    echo json_encode(["success" => false, "error" => "Kuryer ma'lumotlarini olib bo'lmadi."]);
    exit;
}

// Bazani yangilaymiz: status, kuryer IDsi va kuryer ismini yozamiz
$courier_name_for_db = $courier['name'];
$stmt = $conn->prepare("UPDATE orders SET status = 'Kuryerga berildi', courier_id = ?, courier_name_snapshot = ? WHERE id = ?");
$stmt->bind_param("isi", $courier_id, $courier_name_for_db, $order_id);
$update_success = $stmt->execute();
$stmt->close();

if (!$update_success) {
    echo json_encode(["success" => false, "error" => "Buyurtma statusini yangilab bo'lmadi."]);
    exit;
}

// Mijoz ma'lumotlarini olamiz
$stmt = $conn->prepare("SELECT tg_user_id, name, phone, address, total FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    echo json_encode(["success" => false, "error" => "Buyurtma ma'lumotlarini olib bo'lmadi."]);
    exit;
}

// Xabarlarni yuborish
try {
    // 1. MIJOZGA yuboriladigan xabar
    if (!empty($order['tg_user_id'])) {
        $message_for_customer = "🚚 Sizning buyurtmangiz kuryerga topshirildi.\n\n" .
                                "<b>Kuryer:</b> {$courier['name']}\n" .
                                "<b>Telefon:</b> {$courier['phone']}";
        sendTelegramToUser($order['tg_user_id'], $message_for_customer);
    }

    // 2. KURYERGA yuboriladigan xabar
    if (!empty($courier['telegram_id'])) {
        $message_for_courier = "🛵 Yangi buyurtma!\n\n" .
                               "<b>Mijoz:</b> {$order['name']}\n" .
                               "<b>Telefon:</b> {$order['phone']}\n" .
                               "<b>Manzil:</b> {$order['address']}\n" .
                               "<b>Jami summa:</b> " . number_format($order['total'], 0, '.', ' ') . " so'm";
        
        // Kuryerga xabar yuborish uchun yangi funksiyani chaqiramiz
        sendTelegramToCourier($courier['telegram_id'], $message_for_courier, $order_id);
    }

} catch (Throwable $e) {
    file_put_contents(__DIR__ . "/../bot/telegram_error_log.txt", "Assign courier telegram error: " . $e->getMessage() . "\n", FILE_APPEND);
}

echo json_encode(["success" => true, "message" => "Kuryer muvaffaqiyatli biriktirildi!"]);
?>