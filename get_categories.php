<?php
header('Content-Type: application/json');

$host = 'localhost';
$db   = 'bbhubuz_appetito';
$user = 'bbhubuz_appuser';
$pass = '$#@xzod1312';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SELECT DISTINCT category FROM products ORDER BY category ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (is_array($categories)) {
        echo json_encode($categories);
    } else {
        echo json_encode(["xatolik" => "kategoriya topilmadi"]);
    }

} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
