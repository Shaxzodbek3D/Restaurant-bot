<?php
require_once __DIR__ . '/../bot/config.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Rasm manzilini olish
    $res = $conn->query("SELECT image FROM products WHERE id = $id");
    if ($res && $row = $res->fetch_assoc()) {
        $imagePath = "../" . $row['image']; // upload/img_xxx.jpg

        // Fayl mavjud bo‘lsa, o‘chiramiz
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    // Mahsulotni bazadan o‘chirish
    $conn->query("DELETE FROM products WHERE id = $id");
}

header("Location: menu.php");
exit();
