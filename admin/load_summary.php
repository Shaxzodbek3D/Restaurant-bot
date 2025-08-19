<?php
// admin/load_summary.php (Tuzatilgan versiya)

require_once __DIR__ . '/../bot/config.php';
header('Content-Type: application/json');

$today = date('Y-m-d');
$response = [];

// 1. Bugungi YANGI buyurtmalar sonini olamiz
$yangi_res = $conn->query("SELECT COUNT(id) as count FROM orders WHERE status = 'Yangi' AND DATE(created_at) = '$today'");
// Natijani `yangi_count` nomi bilan massivga qo'shamiz
$response['yangi_count'] = $yangi_res->fetch_assoc()['count'] ?? 0;

// 2. Bugungi YAKUNLANGAN buyurtmalar bo'yicha statistikani olamiz
$sql = "SELECT payment_type, COUNT(id) AS soni, SUM(total) AS summa
        FROM orders 
        WHERE status = 'Yakunlandi' AND DATE(updated_at) = '$today'
        GROUP BY payment_type";

$res = $conn->query($sql);

// Boshlang'ich qiymatlarni beramiz
$summary = [
    'naqd' => ['count' => 0, 'total' => 0],
    'click' => ['count' => 0, 'total' => 0]
];

if ($res) {
    while ($row = $res->fetch_assoc()) {
        $payment_type_lower = strtolower($row['payment_type']);
        if (isset($summary[$payment_type_lower])) {
            $summary[$payment_type_lower] = ['count' => $row['soni'], 'total' => $row['summa']];
        }
    }
}

// Natijalarni asosiy massivga birlashtiramiz
$response['naqd_count'] = $summary['naqd']['count'];
$response['naqd_total'] = $summary['naqd']['total'];
$response['plastik_count'] = $summary['click']['count'];
$response['plastik_total'] = $summary['click']['total'];

$conn->close();

// Yakuniy ma'lumotlarni JSON formatida chiqaramiz
echo json_encode($response);
?>