<?php
require_once __DIR__ . '/../bot/config.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Kategoriya nomini olish
    $res = $conn->query("SELECT name FROM categories WHERE id = $id");
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $categoryName = $row['name'];

        // Mahsulotlardan ushbu kategoriyani o‘chirish (yoki tozalash)
        $conn->query("DELETE FROM products WHERE category = '$categoryName'");

        // Kategoriyani o‘chirish
        $conn->query("DELETE FROM categories WHERE id = $id");
    }
}

header("Location: menu.php");
exit;
