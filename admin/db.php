<?php
// Sessiyani faqat bir marta boshlash
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', 86400); // 24 soat
    session_set_cookie_params(86400);
    session_start();
}

$host = 'localhost';
$db = 'bbhubuz_appetito';
$user = 'bbhubuz_appuser';
$pass = '$#@xzod1312'; // Parolingiz

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Bazaga ulanishda xatolik: " . $e->getMessage());
}
?>
