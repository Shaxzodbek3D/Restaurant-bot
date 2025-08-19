<?php
// admin/yakunlash.php (YAKUNIY VERSIYA — 17 ta ustun to‘liq mos)

require_once __DIR__ . '/../bot/config.php';

$error_message = null;
$success_message = "✅ Bugun uchun arxivlanadigan yakunlangan buyurtmalar topilmadi.";
$archived_count = 0;

$conn->begin_transaction();

try {
    $today = date('Y-m-d');
    $sql_select = "SELECT * FROM orders WHERE status = 'Yakunlandi' AND DATE(updated_at) = '$today'";
    $result = $conn->query($sql_select);
    
    $orders_to_archive = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $orders_to_archive[] = $row;
        }
    }

    if (!empty($orders_to_archive)) {
        // ✅ TO‘G‘RI: 17 ta ustun aniq nom bilan kiritilmoqda
        $stmt_insert = $conn->prepare("
            INSERT INTO orders_archive (
                id, tg_user_id, name, phone, address,
                items, total, payment_type, change_needed,
                created_at, updated_at, status, order_type,
                courier_id, courier_name_snapshot, courier_status, courier_payment
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE id=id
        ");

        if ($stmt_insert === false) {
            throw new Exception("Arxivga yozish uchun so'rovni tayyorlashda xatolik: " . $conn->error);
        }

        foreach ($orders_to_archive as $order) {
            $stmt_insert->bind_param(
                'isssssisississsss', // 17 ta ustun = 17 ta tip
                $order['id'],
                $order['tg_user_id'],
                $order['name'],
                $order['phone'],
                $order['address'],
                $order['items'],
                $order['total'],
                $order['payment_type'],
                $order['change_needed'],
                $order['created_at'],
                $order['updated_at'],
                $order['status'],
                $order['order_type'],
                $order['courier_id'],
                $order['courier_name_snapshot'],
                $order['courier_status'],
                $order['courier_payment']
            );

            if (!$stmt_insert->execute()) {
                throw new Exception("Arxivga yozishda xatolik (ID: {$order['id']}): " . $stmt_insert->error);
            }
        }

        $stmt_insert->close();

        $sql_delete = "DELETE FROM orders WHERE status = 'Yakunlandi' AND DATE(updated_at) = '$today'";
        if (!$conn->query($sql_delete)) {
            throw new Exception("Arxivlangan buyurtmalarni o'chirishda xatolik: " . $conn->error);
        }

        $archived_count = count($orders_to_archive);
        $success_message = "✅ Ish kuni muvaffaqiyatli yakunlandi. $archived_count ta buyurtma arxivga o'tkazildi.";
    }

    $conn->commit();

} catch (Exception $e) {
    $conn->rollback();
    $error_message = "❌ Xatolik yuz berdi: " . $e->getMessage();
}

$conn->close();

session_start();
if ($error_message) {
    $_SESSION['flash_message'] = ['type' => 'danger', 'text' => $error_message];
} else {
    $_SESSION['flash_message'] = ['type' => 'success', 'text' => $success_message];
}

header("Location: dashboard.php");
exit();
?>
