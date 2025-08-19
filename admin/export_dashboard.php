<?php
// admin/export_dashboard.php

require_once __DIR__ . '/../bot/config.php';

$filename = "bugungi_buyurtmalar_" . date('Y-m-d') . ".xls";

header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
echo "\xEF\xBB\xBF"; // UTF-8 uchun

$today = date('Y-m-d');
$sql = "SELECT o.*, c.name AS courier_name FROM orders AS o
        LEFT JOIN couriers AS c ON o.courier_id = c.id
        WHERE DATE(o.created_at) = '$today' 
        ORDER BY o.id DESC";
$result = $conn->query($sql);

echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><meta charset="utf-8"><style>table, th, td { border: 1px solid black; border-collapse: collapse; padding: 5px; font-family: "Times New Roman"; }</style></head><body>';
echo "<table><thead><tr>
        <th>ID</th><th>Mijoz</th><th>Telefon</th><th>Manzil</th><th>Buyurtmalar</th><th>Jami</th>
        <th>To'lov turi</th><th>Buyurtma turi</th><th>Kuryer</th><th>Status</th><th>Sana</th>
    </tr></thead><tbody>";

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $itemList = "";
        $items = json_decode($row['items'], true);
        if (is_array($items)) { foreach ($items as $item) $itemList .= htmlspecialchars($item['name']) . " x " . $item['qty'] . "; "; }
        
        $courier_name = htmlspecialchars($row['courier_name_snapshot'] ?? ($row['courier_name'] ?? '-'));

        echo "<tr>
                <td>{$row['id']}</td>
                <td>" . htmlspecialchars($row['name']) . "</td>
                <td>'" . htmlspecialchars($row['phone']) . "</td>
                <td>" . htmlspecialchars($row['address']) . "</td>
                <td>" . $itemList . "</td>
                <td>" . $row['total'] . "</td>
                <td>" . htmlspecialchars($row['payment_type'] ?? '-') . "</td>
                <td>" . htmlspecialchars($row['order_type'] ?? '-') . "</td>
                <td>" . $courier_name . "</td>
                <td>" . htmlspecialchars($row['status']) . "</td>
                <td>" . date("Y-m-d H:i", strtotime($row['created_at'])) . "</td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='11'>Bugun uchun buyurtmalar topilmadi</td></tr>";
}

echo "</tbody></table></body></html>";
$conn->close();
?>