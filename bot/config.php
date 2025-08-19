<?php
// config.php - Barcha sozlamalar uchun yagona markaz

// === BOT TOKENLARI ===
define("USER_BOT_TOKEN", "7750478746:AAGNFN7NamsQ-0AK4G5HZf1hGTS4RiuTN30");
define("COURIER_BOT_TOKEN", "7530390435:AAGsSLeixrACOkaO9IjK29t4JTZ5-6TwznA");

// === MA'LUMOTLAR BAZASI ===
$db_host = "localhost";
$db_user = "bbhubuz_appuser";
$db_pass = "$#@xzod1312";
$db_name = "bbhubuz_appetito";
$db_port = 3306;

// Ulanishni o'rnatish
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
if ($conn->connect_error) {
    die("Bazaga ulanishda xatolik: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Eskirgan db_mysqli.php va token.php fayllarini endi ishlatmaymiz.