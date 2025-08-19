<?php
// admin/couriers_list.php
require_once __DIR__ . '/../bot/config.php';
session_start();

if (!isset($_SESSION["admin"])) {
    header("Location: admin.php");
    exit();
}

// Barcha kuryerlarni olish
$result = $conn->query("SELECT id, name, phone, is_active FROM couriers ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuryerlarni Boshqarish</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>🚚 Kuryerlarni Boshqarish</h2>
        <div>
            <a href="courier_stats.php" class="btn btn-outline-primary">← Ortga qaytish</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ism</th>
                            <th>Telefon</th>
                            <th>Holati</th>
                            <th>Amallar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td><?= htmlspecialchars($row['name']) ?></td>
                                    <td><?= htmlspecialchars($row['phone']) ?></td>
                                    <td>
                                        <?php if ($row['is_active']): ?>
                                            <span class="badge bg-success">Aktiv</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Noaktiv</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="edit_courier.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm">✏️ Tahrirlash</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">Hech qanday kuryer topilmadi.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php $conn->close(); ?>
</body>
</html>