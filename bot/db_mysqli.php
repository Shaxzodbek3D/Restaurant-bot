<?php
$host = 'localhost';
$db = 'bbhubuz_appetito';
$user = 'bbhubuz_appuser';
$pass = '$#@xzod1312';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    file_put_contents(__DIR__ . '/log.txt', "❌ MYSQL ulanish xatosi: " . $conn->connect_error . "\n", FILE_APPEND);
    die("Ulanishda xatolik: " . $conn->connect_error);
} else {
    file_put_contents(__DIR__ . '/log.txt', "✅ MYSQL muvaffaqiyatli ulandi\n", FILE_APPEND);
}
?>
