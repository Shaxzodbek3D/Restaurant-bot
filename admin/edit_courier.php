<?php
// admin/edit_courier.php
require_once __DIR__ . '/../bot/config.php';
session_start();

if (!isset($_SESSION["admin"])) {
    header("Location: admin.php");
    exit();
}

$courier_id = $_GET['id'] ?? null;
$error_message = '';
$courier = null;

// Agar forma jo'natilsa (POST so'rovi)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $is_active = $_POST['is_active'];

    if (!empty($name) && !empty($phone)) {
        $stmt = $conn->prepare("UPDATE couriers SET name = ?, phone = ?, is_active = ? WHERE id = ?");
        $stmt->bind_param("ssii", $name, $phone, $is_active, $id);
        if ($stmt->execute()) {
            // Muvaffaqiyatli o'zgartirilsa, ro'yxatga qaytish
            header("Location: couriers_list.php");
            exit();
        } else {
            $error_message = "Ma'lumotlarni yangilashda xatolik: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error_message = "Barcha maydonlarni to'ldiring.";
    }
}

// Kuryer ma'lumotlarini olish uchun (GET so'rovi)
if ($courier_id) {
    $stmt = $conn->prepare("SELECT id, name, phone, is_active FROM couriers WHERE id = ?");
    $stmt->bind_param("i", $courier_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $courier = $result->fetch_assoc();
    $stmt->close();
    
    if (!$courier) {
        die("Bunday IDga ega kuryer topilmadi.");
    }
} else {
    die("Kuryer IDsi ko'rsatilmagan.");
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuryerni Tahrirlash</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>✏️ Kuryerni Tahrirlash</h2>
        <a href="couriers_list.php" class="btn btn-outline-secondary">Ro'yxatga qaytish</a>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?= $error_message ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="id" value="<?= htmlspecialchars($courier['id']) ?>">
                
                <div class="mb-3">
                    <label for="name" class="form-label">Ism</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($courier['name']) ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="phone" class="form-label">Telefon raqami</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($courier['phone']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="is_active" class="form-label">Holati</label>
                    <select class="form-select" id="is_active" name="is_active">
                        <option value="1" <?= $courier['is_active'] == 1 ? 'selected' : '' ?>>Aktiv</option>
                        <option value="0" <?= $courier['is_active'] == 0 ? 'selected' : '' ?>>Noaktiv</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-success">💾 Saqlash</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>