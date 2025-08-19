<?php
// admin/load_orders.php (Filtrlar to'liq yangilangan)
require_once __DIR__ . '/../bot/config.php';

$filter = $_GET['filter'] ?? '';
$search = $conn->real_escape_string($_GET['search'] ?? '');
$today = date('Y-m-d');

// ===== ASOSIY O'ZGARISH: Filtrga qarab WHERE shartini aqlli yasash =====
$whereConditions = [];

// Barcha holatlarda faqat bugungi kun uchun ishlaymiz
$whereConditions[] = "DATE(o.created_at) = '$today'";

if ($filter === 'barchasi') {
    // Hech qanday qo'shimcha status filteri qo'shilmaydi
} elseif ($filter === 'Yakunlandi') {
    // Faqat yakunlanganlarni ko'rsatamiz
    $whereConditions[] = "o.status = 'Yakunlandi'";
} elseif (!empty($filter)) {
    // Aniq bir status bo'yicha filter (Yangi, Qabul qilindi...)
    $whereConditions[] = "o.status = '$filter'";
} else {
    // Standart holat (filtr tanlanmaganda) - faqat aktiv buyurtmalar
    $whereConditions[] = "o.status NOT IN ('Yakunlandi', 'Yetkazib berildi')";
}

// Qidiruv shartini qo'shamiz
if (!empty($search)) {
    $whereConditions[] = "(o.name LIKE '%$search%' OR o.phone LIKE '%$search%' OR o.address LIKE '%$search%')";
}

$whereClause = "WHERE " . implode(' AND ', $whereConditions);


$sql = "SELECT o.*, c.name AS courier_name FROM orders AS o
        LEFT JOIN couriers AS c ON o.courier_id = c.id
        $whereClause ORDER BY o.id DESC";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        
        $status_class = match($row['status']) {
            'Yangi' => 'new',
            'Qabul qilindi' => 'accepted',
            'Kuryerga berildi' => 'courier',
            'Yakunlandi' => 'completed',
            default => ''
        };

        $courier_info = htmlspecialchars($row['courier_name_snapshot'] ?? ($row['courier_name'] ?? '-'));

        echo "<tr class='status-{$status_class}'>";
        echo "<td>{$row['id']}</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
        echo "<td>" . htmlspecialchars($row['address']) . "</td>";
        echo "<td><ul>";
        $items = json_decode($row['items'], true);
        if (is_array($items)) { foreach ($items as $item) echo "<li>" . htmlspecialchars($item['name']) . " x " . intval($item['qty']) . "</li>"; }
        echo "</ul></td>";
        echo "<td>" . number_format($row['total'], 0, '.', ' ') . " so'm</td>";
        echo "<td>" . htmlspecialchars($row['payment_type'] ?? '-') . "</td>";
        echo "<td>" . htmlspecialchars($row['order_type'] ?? '-') . "</td>";
        echo "<td>" . $courier_info . "</td>";
        
        echo "<td>";
        if ($row['status'] === 'Yakunlandi' || $row['status'] === 'Yetkazib berildi') {
            echo '<span class="badge bg-success">Yakunlandi</span>';
        } else {
            echo "<div class='d-flex flex-column gap-1'>
                    <select class='form-select form-select-sm status-select' data-id='{$row['id']}'>
                        <option value='Yangi'" . ($row['status'] === 'Yangi' ? ' selected' : '') . ">Yangi</option>
                        <option value='Qabul qilindi'" . ($row['status'] === 'Qabul qilindi' ? ' selected' : '') . ">Qabul qilindi</option>
                        <option value='Kuryerga berildi'" . ($row['status'] === 'Kuryerga berildi' ? ' selected' : '') . ">Kuryerga berildi</option>
                    </select>";
            if ($row['status'] === 'Qabul qilindi') {
                echo "<button class='btn btn-sm btn-outline-primary mt-1' onclick='openCourierModal({$row['id']})'>🚚 Kuryerga</button>";
            }
        }
        echo "</div></td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='10' class='text-center'>🤷 Tanlangan filtr bo'yicha buyurtmalar yo'q.</td></tr>";
}
$conn->close();
?>