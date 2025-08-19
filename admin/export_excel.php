<?php
// admin/export_excel.php

// Ma'lumotlar bazasi ulanishi
require_once __DIR__ . '/../bot/config.php';

// Filtr parametrlarini GET so'rovidan olamiz
$courier_id = $_GET['courier_id'] ?? null;
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

// WHERE shartlarini tayyorlaymiz
$whereConditions = [];
$params = [];
$types = "";

if (!empty($courier_id)) {
    $whereConditions[] = "o.courier_id = ?";
    $params[] = $courier_id;
    $types .= "i";
}
if (!empty($start_date)) {
    $whereConditions[] = "DATE(o.created_at) >= ?";
    $params[] = $start_date;
    $types .= "s";
}
if (!empty($end_date)) {
    $whereConditions[] = "DATE(o.created_at) <= ?";
    $params[] = $end_date;
    $types .= "s";
}

$whereClause = "";
if (!empty($whereConditions)) {
    $whereClause = "WHERE " . implode(' AND ', $whereConditions);
}

// Ma'lumotlarni olish uchun SQL so'rovi
$sql = "SELECT 
            o.id, 
            c.name AS courier_name, 
            o.name AS customer_name, 
            o.address, 
            o.items, 
            o.total, 
            o.payment_type, 
            o.created_at 
        FROM orders_archive o
        LEFT JOIN couriers c ON o.courier_id = c.id
        $whereClause 
        ORDER BY o.id DESC";

$stmt = $conn->prepare($sql);
if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// --- CSV FAYLNI YARATISH BOSHLANDI ---

$filename = "kuryer_statistikasi_" . date('Y-m-d') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// UTF-8 bilan to'g'ri ishlashi uchun BOM qo'shamiz
fputs($output, "\xEF\xBB\xBF");

// Jadvalning sarlavha qatorini yozamiz (NUQTALI VERGUL BILAN)
fputcsv($output, [
    'Buyurtma ID', 
    'Kuryer', 
    'Mijoz Ismi', 
    'Manzil', 
    'Mahsulotlar', 
    'Jami Summa', 
    'To\'lov Turi', 
    'Sana'
], ';'); // <--- MUHIM O'ZGARISH

// Ma'lumotlarni qatorma-qator yozib chiqamiz (NUQTALI VERGUL BILAN)
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $items_text = '';
        $items_array = json_decode($row['items'], true);
        if (is_array($items_array)) {
            $temp_items = [];
            foreach ($items_array as $item) {
                $temp_items[] = "{$item['name']} x {$item['qty']}";
            }
            $items_text = implode(', ', $temp_items); // Mahsulotlar orasini vergul bilan qoldiramiz
        }

        fputcsv($output, [
            $row['id'],
            $row['courier_name'] ?? 'Noma\'lum',
            $row['customer_name'],
            $row['address'],
            $items_text,
            $row['total'],
            $row['payment_type'],
            $row['created_at']
        ], ';'); // <--- MUHIM O'ZGARISH
    }
}

$stmt->close();
$conn->close();
fclose($output);
exit;
?>