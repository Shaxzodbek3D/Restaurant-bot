<?php
require_once __DIR__ . '/../bot/config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = intval($_POST["id"]);
    $newName = trim($_POST["name"]);

    // eski nomni olish
    $old = $conn->query("SELECT name FROM categories WHERE id = $id")->fetch_assoc();
    $oldName = $old['name'];

    // kategoriyani yangilash
    $stmt = $conn->prepare("UPDATE categories SET name = ? WHERE id = ?");
    $stmt->bind_param("si", $newName, $id);
    $stmt->execute();

    // mahsulotlardagi eski kategoriya nomini yangisiga almashtirish
    $stmt2 = $conn->prepare("UPDATE products SET category = ? WHERE category = ?");
    $stmt2->bind_param("ss", $newName, $oldName);
    $stmt2->execute();

    header("Location: menu.php");
    exit();
}
?>
