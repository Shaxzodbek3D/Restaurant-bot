<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
// admin/courier_stats.php
require_once __DIR__ . '/../bot/config.php';

// Filtrlar uchun o'zgaruvchilarni olamiz
$courier_id = $_GET['courier_id'] ?? null;
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

// Barcha kuryerlarni ro'yxat uchun olamiz
$couriers_res = $conn->query("SELECT id, name FROM couriers WHERE is_active = 1 ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuryerlar Statistikasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; background-color: #f8f9fa; }
        .table thead { background-color: #0dcaf0; color: white; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📊 Kuryerlar Statistikasi</h2>
        <a href="couriers_list.php" class="btn btn-secondary">Kuryerlarni Boshqarish</a>
        <a href="dashboard.php" class="btn btn-outline-primary">← Dashboardga qaytish</a>
    </div>

    <div class="card card-body mb-4">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="courier_id" class="form-label">Kuryerni tanlang:</label>
                <select name="courier_id" id="courier_id" class="form-select">
                    <option value="">-- Barcha kuryerlar --</option>
                    <?php 
                    // Kuryerlar ro'yxatini chiqarish va tanlanganini belgilash
                    if ($couriers_res->num_rows > 0) {
                        $couriers_res->data_seek(0); // Pointerni boshiga qaytarish
                        while($courier_row = $couriers_res->fetch_assoc()) {
                            $selected = ($courier_id == $courier_row['id']) ? 'selected' : '';
                            echo "<option value='{$courier_row['id']}' $selected>" . htmlspecialchars($courier_row['name']) . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="start_date" class="form-label">Boshlanish sanasi:</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?= htmlspecialchars($start_date) ?>">
            </div>
            <div class="col-md-3">
                <label for="end_date" class="form-label">Tugash sanasi:</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?= htmlspecialchars($end_date) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-info w-100 text-white">🔍 Ko‘rsatish</button>
            </div>
        </form>
    </div>

   <?php
// O'zgaruvchilarni oldindan e'lon qilib olamiz
$summary_result = ['total_orders' => 0, 'total_sum' => 0];
$details_result = null;
$show_stats = !empty($courier_id) || !empty($start_date) || !empty($end_date);

// Agar kamida bitta filtr tanlangan bo'lsa, so'rovlarni bajaramiz
if ($show_stats):
    
    // Xavfsizlik uchun tayyorlangan so'rovlardan (prepared statements) foydalanamiz
    $whereConditions = [];
    $params = [];
    $types = "";

    // Kuryer bo'yicha filtr
    if (!empty($courier_id)) {
        $whereConditions[] = "o.courier_id = ?";
        $params[] = $courier_id;
        $types .= "i";
    }
    // Boshlanish sanasi bo'yicha filtr
    if (!empty($start_date)) {
        $whereConditions[] = "DATE(o.created_at) >= ?";
        $params[] = $start_date;
        $types .= "s";
    }
    // Tugash sanasi bo'yicha filtr
    if (!empty($end_date)) {
        $whereConditions[] = "DATE(o.created_at) <= ?";
        $params[] = $end_date;
        $types .= "s";
    }
    
    // Agar biror shart qo'shilgan bo'lsa, WHERE kalit so'zini qo'shamiz
    $whereClause = "";
    if (count($whereConditions) > 0) {
        $whereClause = "WHERE " . implode(' AND ', $whereConditions);
    }

    // 1. Umumiy summa va sonni hisoblash uchun so'rov
    $sql_summary = "SELECT COUNT(o.id) AS total_orders, SUM(o.total) AS total_sum 
                    FROM orders_archive o 
                    $whereClause";
                    
    $stmt_summary = $conn->prepare($sql_summary);
    if (!empty($types)) {
        $stmt_summary->bind_param($types, ...$params);
    }
    $stmt_summary->execute();
    // fetch_assoc() dan keyin natija bo'lmasa, xatolik bermasligi uchun tekshiruv
    $summary_result = $stmt_summary->get_result()->fetch_assoc() ?: $summary_result;
    $stmt_summary->close();

    // 2. Batafsil ro'yxat uchun so'rov
    $sql_details = "SELECT o.*, c.name AS courier_name 
                    FROM orders_archive o 
                    LEFT JOIN couriers c ON o.courier_id = c.id 
                    $whereClause 
                    ORDER BY o.id DESC";
                    
    $stmt_details = $conn->prepare($sql_details);
    if (!empty($types)) {
        $stmt_details->bind_param($types, ...$params);
    }
    $stmt_details->execute();
    $details_result = $stmt_details->get_result();
?>
    
    <div class="card card-body mb-4 bg-light">
        </div>

    <div class="alert alert-info">
        <h4>Hisobot natijalari:</h4>
        <p class="mb-1"><strong>Jami yetkazilgan buyurtmalar soni:</strong> <?= $summary_result['total_orders'] ?? 0 ?> ta</p>
        <p class="mb-0"><strong>Jami summa:</strong> <?= number_format($summary_result['total_sum'] ?? 0, 0, '.', ' ') ?> so'm</p>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Kuryer</th>
                    <th>Mijoz</th>
                    <th>Manzil</th>
                    <th>Buyurtmalar</th>
                    <th>Jami</th>
                    <th>To'lov turi</th>
                    <th>Yaratilgan vaqt</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($details_result && $details_result->num_rows > 0): ?>
                <?php while($row = $details_result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['courier_name'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['address']) ?></td>
                    <td>
                        <ul>
                        <?php
                            $items = json_decode($row['items'], true);
                            if(is_array($items)) { foreach($items as $item) echo "<li>" . htmlspecialchars($item['name']) . " x " . $item['qty'] . "</li>"; }
                        ?>
                        </ul>
                    </td>
                    <td><?= number_format($row['total'], 0, '.', ' ') ?> so'm</td>
                    <td><?= htmlspecialchars($row['payment_type']) ?></td>
                    <td><?= date("d.m.Y H:i", strtotime($row['created_at'])) ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="8" class="text-center">Bu filtr bo'yicha ma'lumot topilmadi.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php  
    if ($details_result) {
        $stmt_details->close();
    }
    endif; // Bu if ($show_stats) ning yakuni
    $conn->close();
?>
</div>

</body>
</html>