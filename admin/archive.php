<?php
// admin/archive.php
require_once __DIR__ . '/../bot/config.php';

// Sana filtrlarini olish
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyurtmalar arxivi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; background-color: #f8f9fa; }
        .table thead { background-color: #6c757d; color: white; }
        ul { padding-left: 18px; margin-bottom: 0; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📁 Buyurtmalar arxivi</h2>
        <a href="dashboard.php" class="btn btn-outline-primary">← Dashboardga qaytish</a>
    </div>

    <div class="card card-body mb-4">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label for="start_date" class="form-label">Boshlanish sanasi</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?= htmlspecialchars($start_date) ?>">
            </div>
            <div class="col-md-5">
                <label for="end_date" class="form-label">Tugash sanasi</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?= htmlspecialchars($end_date) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-success w-100">🔍 Ko‘rsatish</button>
            </div>
        </form>
    </div>

    <div class="card card-body mb-4 bg-light">
        <form method="GET" action="export_archive_excel.php" target="_blank">
            <input type="hidden" name="source" value="archive">
            <input type="hidden" name="start_date" value="<?= htmlspecialchars($start_date) ?>">
            <input type="hidden" name="end_date" value="<?= htmlspecialchars($end_date) ?>">
            <p class="mb-1"><strong>Ko'rsatilayotgan hisobotni Excelda yuklab olish</strong></p>
            <button type="submit" class="btn btn-outline-success w-100">📥 Excelga olish</button>
        </form>
    </div>


    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead>
                <tr>
                    <th>ID</th><th>Ism</th><th>Telefon</th><th>Manzil</th><th>Buyurtmalar</th><th>Jami</th>
                    <th>To'lov turi</th><th>Buyurtma turi</th><th>Kuryer</th><th>Yaratilgan vaqt</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $whereConditions = [];
                if (!empty($start_date)) {
                    $whereConditions[] = "DATE(o.created_at) >= '{$conn->real_escape_string($start_date)}'";
                }
                if (!empty($end_date)) {
                    $whereConditions[] = "DATE(o.created_at) <= '{$conn->real_escape_string($end_date)}'";
                }
                $whereClause = !empty($whereConditions) ? "WHERE " . implode(' AND ', $whereConditions) : "";

                $sql = "SELECT o.*, c.name AS courier_name FROM orders_archive AS o
                        LEFT JOIN couriers AS c ON o.courier_id = c.id
                        $whereClause ORDER BY o.id DESC";

                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $courier_name = htmlspecialchars($row['courier_name_snapshot'] ?? ($row['courier_name'] ?? '-'));
                        echo "<tr>";
                        echo "<td>{$row['id']}</td>";
                        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['address']) . "</td>";
                        echo "<td><ul>";
                        $items = json_decode($row['items'], true);
                        if (is_array($items)) {
                            foreach ($items as $item) { echo "<li>" . htmlspecialchars($item['name']) . " x " . intval($item['qty']) . "</li>"; }
                        }
                        echo "</ul></td>";
                        echo "<td>" . number_format($row['total'], 0, '.', ' ') . " so'm</td>";
                        echo "<td>" . htmlspecialchars($row['payment_type'] ?? '-') . "</td>";
                        echo "<td>" . htmlspecialchars($row['order_type'] ?? '-') . "</td>";
                        echo "<td>{$courier_name}</td>";
                        echo '<td>' . date("d.m.Y H:i", strtotime($row['created_at'])) . '</td>';
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='10' class='text-center'>🤷Arxivda ma'lumot topilmadi.</td></tr>";
                }
                $conn->close();
                ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>