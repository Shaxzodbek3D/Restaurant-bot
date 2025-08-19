<?php
header('Content-Type: application/json');

// Faqat POST so‘rovga ruxsat
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'POST method required']);
    exit;
}

// JSON formatdagi ma'lumotni olish
$data = json_decode(file_get_contents('php://input'), true);

// Majburiy maydonlar tekshiruvi
if (
    !$data ||
    empty($data['name']) ||
    empty($data['phone']) ||
    empty($data['items']) ||
    !is_array($data['items']) ||
    empty($data['total'])
) {
    echo json_encode(['error' => 'Missing or invalid data']);
    exit;
}

// Ma'lumotlar bazasiga ulanish
$host = 'localhost';
$dbname = 'bbhubuz_appetito';
$username = 'bbhubuz_appuser';
$password = '$#@xzod1312';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("
        INSERT INTO orders 
        (user_id, name, phone, address, items, total, payment_type, change_needed, status, created_at)
        VALUES 
        (:user_id, :name, :phone, :address, :items, :total, :payment_type, :change_needed, 'Yangi', NOW())
    ");

    $stmt->execute([
        ':user_id'       => $data['user_id'] ?? null,
        ':name'          => $data['name'],
        ':phone'         => $data['phone'],
        ':address'       => $data['address'] ?? '',
        ':items'         => json_encode($data['items'], JSON_UNESCAPED_UNICODE),
        ':total'         => $data['total'],
        ':payment_type'  => $data['payment_type'] ?? 'Naqd',
        ':change_needed' => $data['change_needed'] ?? 0
    ]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'DB error: ' . $e->getMessage()]);
}
?>
