<?php
require __DIR__ . '/bot/db_mysqli.php';
require __DIR__ . '/bot/send_telegram.php';

// Kuryer bot tokeni
define("COURIER_BOT_TOKEN", "7530390435:AAGsSLeixrACOkaO9IjK29t4JTZ5-6TwznA");

$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (!$update || !isset($update['callback_query'])) exit;

$callback = $update['callback_query'];
$data = $callback['data']; // masalan: accepted_123
$chat_id = $callback['message']['chat']['id'];
$message_id = $callback['message']['message_id'];

list($action, $order_id) = explode("_", $data);
$order_id = intval($order_id);

switch ($action) {
  case 'accepted':
    // Holatni "Qabul qilindi" deb yangilash
    $stmt = $conn->prepare("UPDATE orders SET status = 'Qabul qilindi' WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();

    // Foydalanuvchiga buyurtma tayyorlanmoqda degan xabar
    $stmt = $conn->prepare("SELECT tg_user_id FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && ($order = $res->fetch_assoc())) {
        sendTelegramToUser($order['tg_user_id'], "🛠 Buyurtmangiz tayyorlanmoqda!");
    }

    sendTelegramToCourier($chat_id, "✅ Buyurtma qabul qilindi.");
    break;

  case 'delivered':
    $stmt = $conn->prepare("UPDATE orders SET status = 'Yakunlandi' WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();

    // Foydalanuvchiga buyurtma yetkazildi degan xabar
    $stmt = $conn->prepare("SELECT tg_user_id FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && ($order = $res->fetch_assoc())) {
        sendTelegramToUser($order['tg_user_id'], "🎉 Buyurtmangiz yetkazildi. Yana murojaat qilganingiz uchun rahmat!");
    }

    sendTelegramToCourier($chat_id, "📦 Buyurtma yetkazildi.");
    break;

  case 'pay_cash':
    $stmt = $conn->prepare("UPDATE orders SET payment_type = 'Naqd' WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    sendTelegramToCourier($chat_id, "💰 To‘lov turi: Naqd");
    break;

  case 'pay_click':
    $stmt = $conn->prepare("UPDATE orders SET payment_type = 'Click' WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    sendTelegramToCourier($chat_id, "💳 To‘lov turi: Click");
    break;

  default:
    sendTelegramToCourier($chat_id, "❓ Noma'lum amal.");
    break;
}

// Callback tugmasiga javob berish (Loading tugmasi yo‘qolishi uchun)
file_get_contents("https://api.telegram.org/bot" . COURIER_BOT_TOKEN . "/answerCallbackQuery?callback_query_id=" . $callback['id']);
?>
