<?php
// send_telegram.php

// === Foydalanuvchi (mijoz) bot uchun sozlamalar ===
define("USER_BOT_TOKEN", "7750478746:AAGNFN7NamsQ-0AK4G5HZf1hGTS4RiuTN30");

/**
 * Mijozga oddiy matnli xabar yuboradi.
 */
function sendTelegramToUser($chat_id, $message) {
    if (empty($chat_id) || empty($message)) return false;

    $url = "https://api.telegram.org/bot" . USER_BOT_TOKEN . "/sendMessage";
    $post = [
        'chat_id' => $chat_id,
        'text' => $message,
        'parse_mode' => 'HTML' // Markdown o'rniga HTML ishlatamiz, ancha qulay
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_POSTFIELDS => $post]);
    curl_exec($ch);
    curl_close($ch);
}


// === Kuryer bot uchun sozlamalar ===
define("COURIER_BOT_TOKEN", "7530390435:AAGsSLeixrACOkaO9IjK29t4JTZ5-6TwznA");

/**
 * Kuryerga "Qabul qildim" tugmasi bilan birga xabar yuboradi.
 */
function sendTelegramToCourier($chat_id, $message, $order_id) {
    if (empty($chat_id) || empty($message) || empty($order_id)) return false;

    $url = "https://api.telegram.org/bot" . COURIER_BOT_TOKEN . "/sendMessage";

    // Boshlang'ich "Qabul qildim" tugmasini yasaymiz
    $keyboard = [
        "inline_keyboard" => [
            [["text" => "✅ Qabul qildim", "callback_data" => "accepted_$order_id"]]
        ]
    ];

    $post = [
        "chat_id" => $chat_id,
        "text" => $message,
        "parse_mode" => "HTML",
        "reply_markup" => json_encode($keyboard)
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_POSTFIELDS => $post]);
    curl_exec($ch);
    curl_close($ch);
}