<?php
// webhook.php (Fikr-mulohaza saqlash xatoligi to'liq tuzatilgan)

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Markaziy sozlamalar faylini chaqiramiz
require_once 'config.php';

// ===================================================================
// 1. FUNKSIYALARNI E'LON QILISH
// ===================================================================

/**
 * Telegramga matnli xabar yuborish uchun markaziy funksiya.
 */
function sendMessage($chat_id, $text, $keyboard = null) {
    $url = "https://api.telegram.org/bot" . USER_BOT_TOKEN . "/sendMessage";
    $payload = ["chat_id" => $chat_id, "text" => $text, "parse_mode" => "HTML"];
    if ($keyboard) {
        $payload["reply_markup"] = json_encode($keyboard);
    }
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($payload)
    ]);
    curl_exec($ch);
    curl_close($ch);
}

// ===================================================================
// 2. ASOSIY KOD MANTIG'I
// ===================================================================

$content = file_get_contents("php://input");
if (!$content) exit();
$update = json_decode($content, true);

$message = $update["message"] ?? null;
$chat_id = $message["chat"]["id"] ?? null;
$text_original = $message["text"] ?? "";
$text_lower = mb_strtolower(trim($text_original), 'UTF-8');
$contact = $message["contact"] ?? null;

if (!$chat_id) exit();

// Kontakt yuborilgan holatni tekshiramiz
if ($contact) {
    $phone = $contact["phone_number"] ?? "";
    $full_name = trim(($contact["first_name"] ?? "") . ' ' . ($contact["last_name"] ?? ""));
    $user_id = $contact["user_id"] ?? null;

    if ($user_id && $conn) {
        $stmt = $conn->prepare("INSERT INTO users (tg_user_id, full_name, phone) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), phone = VALUES(phone)");
        $stmt->bind_param("iss", $user_id, $full_name, $phone);
        $stmt->execute();
        $stmt->close();
    }
    
    $keyboard = [
        "keyboard" => [
            [["text" => "🍔 Buyurtma berish"]],
            [["text" => "📍 Manzilimiz"], ["text" => "🛒 Buyurtmalarim"]],
            [["text" => "📞 Bog‘lanish"]]
        ],
        "resize_keyboard" => true
    ];
    sendMessage($chat_id, "✅ Rahmat! Quyidagi menyudan foydalanishingiz mumkin:", $keyboard);
    exit(); 
}

// Matnli xabarlarni tekshiramiz
switch ($text_lower) {
    case '/start':
        $keyboard = ["keyboard" => [[["text" => "📞 Kontakt yuborish", "request_contact" => true]]], "resize_keyboard" => true, "one_time_keyboard" => true];
        sendMessage($chat_id, "👋 Xush kelibsiz!\nIltimos, telefon raqamingizni yuboring:", $keyboard);
        break;

    case '🍔 buyurtma berish':
        $webapp_url = "https://bbhub.uz/appetito/index.php?user_id={$chat_id}";
        $inline_keyboard = ["inline_keyboard" => [[["text" => "🍔 Buyurtma berish uchun bosing", "web_app" => ["url" => $webapp_url]]]]];
        sendMessage($chat_id, "📲 Buyurtma berish uchun tugmani bosing:", $inline_keyboard);
        break;

    case '🛒 buyurtmalarim':
        $url = "https://bbhub.uz/appetito/orders.php?user_id={$chat_id}";
        $inline_keyboard = ["inline_keyboard" => [[["text" => "📦 Buyurtmalarim", "web_app" => ["url" => $url]]]]];
        sendMessage($chat_id, "🧾 Buyurtmalaringiz:", $inline_keyboard);
        break;
    
    case '📍 manzilimiz':
        $address_text = "📍 Bizning manzilimiz:\nToshkent shahri, Chilonzor tumani, Beshyog'och dahasi, 12-uy.\n\n" .
                        "Mo'ljal: \"Milliy\" stadioni ro'parasi.";
        sendMessage($chat_id, $address_text);
        break;

    case '📞 bog‘lanish':
    case "📞 bog'lanish":
        sendMessage($chat_id, "📞 Operator bilan bog‘lanish: @bbhub_admin");
        break;

    default: // ===== FIKR-MULOHAZA UCHUN YANGILANGAN MANTIQ =====
        if (!empty($text_original)) {
            // Foydalanuvchining oxirgi buyurtmasini har ikki jadvaldan qidiramiz
            $sql = "SELECT id, status, updated_at FROM (
                        (SELECT id, status, updated_at FROM orders WHERE tg_user_id = ? ORDER BY id DESC LIMIT 1)
                        UNION ALL
                        (SELECT id, status, updated_at FROM orders_archive WHERE tg_user_id = ? ORDER BY id DESC LIMIT 1)
                    ) AS last_orders
                    ORDER BY id DESC
                    LIMIT 1";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $chat_id, $chat_id);
            $stmt->execute();
            $order = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            // Agar oxirgi buyurtma topilsa, u yakunlangan bo'lsa va oradan ko'p vaqt o'tmagan bo'lsa
            if ($order && $order['status'] === 'Yakunlandi' && isset($order['updated_at']) && (time() - strtotime($order['updated_at'])) < 86400) { // 86400 soniya = 24 soat
                
                $stmt_feedback = $conn->prepare("INSERT INTO feedback (order_id, tg_user_id, feedback_text) VALUES (?, ?, ?)");
                $stmt_feedback->bind_param("iis", $order['id'], $chat_id, $text_original);
                $stmt_feedback->execute();
                $stmt_feedback->close();
                
                sendMessage($chat_id, "Fikr-mulohazangiz uchun katta rahmat!");
            } else {
                 sendMessage($chat_id, "Siz yuborgan buyruqni tushunmadim.");
            }
        }
        break;
}
?>