<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/bot/db_mysqli.php';

$user_id = $_GET['user_id'] ?? null;
if ($user_id === null && isset($_GET['tg_user_id'])) {
    $user_id = $_GET['tg_user_id'];
}

if (empty($user_id)) {
    echo json_encode(['error' => 'Foydalanuvchi aniqlanmadi']);
    exit;
}

// c.full_name -> c.name ga o'zgartirilgan SQL so'rovi
$sql = "(SELECT 
            o.items, o.total, o.created_at, 
            c.name AS courier_name 
         FROM orders o
         LEFT JOIN couriers c ON o.courier_id = c.id
         WHERE o.tg_user_id = ?)
        UNION ALL
        (SELECT 
            o.items, o.total, o.created_at, 
            c.name AS courier_name 
         FROM orders_archive o
         LEFT JOIN couriers c ON o.courier_id = c.id
         WHERE o.tg_user_id = ?)
        ORDER BY created_at DESC
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order) {
    echo json_encode([]);
    exit;
}

$decoded_items = json_decode($order['items'], true);
$item_names = [];

if (is_array($decoded_items)) {
    foreach ($decoded_items as $item) {
        $name = $item['name'] ?? 'Nomsiz mahsulot';
        $qty = $item['qty'] ?? 1;
        $item_names[] = "$name x$qty";
    }
}

$response = [
    'items'        => implode(", ", $item_names),
    'total_price'  => $order['total'],
    'created_at'   => $order['created_at'],
    'courier_name' => $order['courier_name'] ?? null // Yangi maydonni ham javobga qo'shamiz
];

header('Content-Type: application/json');
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>