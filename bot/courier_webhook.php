<?php
// bot/courier_webhook.php (Kuryerlar uchun to'liq kod)

// Barcha xatoliklarni ko'rsatish
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Markaziy sozlamalar faylini chaqiramiz (baza ulanishi va tokenlar uchun)
require_once 'config.php';
// Mijozga yakuniy xabar yuborish uchun send_telegram.php kerak
require_once 'send_telegram.php';

// Telegramdan kelgan so'rovni olamiz
$update = json_decode(file_get_contents("php://input"), TRUE);
if (!$update) {
    exit();
}

// ===================================================================
// 1-BLOK: TUGMA BOSILISHLARINI BOSHQARISH (BUYURTMA JARAYONI)
// ===================================================================
if (isset($update["callback_query"])) {
    $callback_query = $update["callback_query"];
    
    $chat_id = $callback_query["message"]["chat"]["id"];
    $message_id = $callback_query["message"]["message_id"];
    $data = $callback_query["data"];

    list($action, $order_id_part) = explode('_', $data, 2);

    function editMessageKeyboard($chat_id, $message_id, $new_keyboard_json) {
        $url = "https://api.telegram.org/bot" . COURIER_BOT_TOKEN . "/editMessageReplyMarkup";
        $post = [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'reply_markup' => $new_keyboard_json
        ];
        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_POSTFIELDS => $post]);
        curl_exec($ch);
        curl_close($ch);
    }

    switch ($action) {
        case 'accepted':
            $order_id = $order_id_part;
            $keyboard = ["inline_keyboard" => [[["text" => "📦 Yetkazildi", "callback_data" => "delivered_$order_id"]]]];
            editMessageKeyboard($chat_id, $message_id, json_encode($keyboard));
            break;

        case 'delivered':
            $order_id = $order_id_part;
            $keyboard = [
                "inline_keyboard" => [[
                    ["text" => "💰 To'lov: Naqd", "callback_data" => "pay_cash_$order_id"],
                    ["text" => "💳 To'lov: Click", "callback_data" => "pay_click_$order_id"]
                ]]
            ];
            $conn->query("UPDATE orders SET status = 'Yetkazib berildi' WHERE id = $order_id");
            editMessageKeyboard($chat_id, $message_id, json_encode($keyboard));
            break;

        case 'pay':
            list($payment_type, $order_id) = explode('_', $order_id_part);
            $payment_type_text = ($payment_type == 'cash') ? 'Naqd' : 'Click';
            $final_status = 'Yakunlandi';

            $stmt = $conn->prepare("UPDATE orders SET payment_type = ?, status = ? WHERE id = ?");
            $stmt->bind_param("ssi", $payment_type_text, $final_status, $order_id);
            $stmt->execute();
            $stmt->close();

            $final_keyboard = ['inline_keyboard' => [[['text' => '✅ Yetkazib berildi', 'callback_data' => 'status_final']]]];
            editMessageKeyboard($chat_id, $message_id, json_encode($final_keyboard));

            $stmt = $conn->prepare("SELECT tg_user_id FROM orders WHERE id = ?");
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $order = $stmt->get_result()->fetch_assoc();
            if ($order && !empty($order['tg_user_id'])) {
                $final_message = "✅ Buyurtmangiz yetkazib berildi!\n\nBizning xizmatimiz haqida fikr-mulohazalaringiz bo'lsa, yozib yuborishingiz mumkin. Sizning fikringiz biz uchun muhim!";
                // Mijozga xabar yuborish uchun send_telegram.php dagi funksiya ishlatiladi
                sendTelegramToUser($order['tg_user_id'], $final_message);
            }
            break;
    }
}
// ===================================================================
// 2-BLOK: ODDIY XABARLARNI BOSHQARISH (KURYERNI RO'YXATGA OLISH)
// ===================================================================
elseif (isset($update["message"])) {
    $message = $update["message"];
    $chat_id = $message["chat"]["id"] ?? null;
    $text = mb_strtolower(trim($message["text"] ?? ""), 'UTF-8');
    $contact = $message["contact"] ?? null;

    if (!$chat_id) exit();

    function sendCourierMessage($chat_id, $text, $keyboard = null) {
        $url = "https://api.telegram.org/bot" . COURIER_BOT_TOKEN . "/sendMessage";
        $payload = ["chat_id" => $chat_id, "text" => $text, "parse_mode" => "HTML"];
        if ($keyboard) {
            $payload["reply_markup"] = json_encode($keyboard);
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
        curl_exec($ch);
        curl_close($ch);
    }
    
    if ($text === "/start") {
        $keyboard = ["keyboard" => [[["text" => "📞 Telefon raqamni yuborish", "request_contact" => true]]], "resize_keyboard" => true, "one_time_keyboard" => true];
        sendCourierMessage($chat_id, "👋 Salom, kuryer! Iltimos, telefon raqamingizni yuboring:", $keyboard);
    }
    elseif ($contact) {
        $phone = $contact["phone_number"] ?? "";
        $full_name = trim(($contact["first_name"] ?? "") . ' ' . ($contact["last_name"] ?? ""));
        $telegram_id = $contact["user_id"] ?? $chat_id;

        if (!$telegram_id || !$phone) {
            sendCourierMessage($chat_id, "❗ Telefon raqamingizni yuborishda xatolik.");
            exit();
        }

        $stmt = $conn->prepare("INSERT INTO couriers (telegram_id, name, phone) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name), phone = VALUES(phone)");
        $stmt->bind_param("iss", $telegram_id, $full_name, $phone);
        $stmt->execute();
        $stmt->close();
        
        $reply_keyboard_remove = ['remove_keyboard' => true];
        sendCourierMessage($chat_id, "✅ Rahmat, siz muvaffaqiyatli ro'yxatdan o'tdingiz!", json_encode($reply_keyboard_remove));
    }
}
?>