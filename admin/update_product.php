<?php
require_once __DIR__ . '/../bot/config.php';

$id = intval($_POST['id']);
$name = trim($_POST['name']);
$price = intval($_POST['price']);
$category = trim($_POST['category']);
$description = trim($_POST['description']);

// Eski rasmni olish
$oldRes = $conn->query("SELECT image FROM products WHERE id = $id");
$old = $oldRes->fetch_assoc();
$oldImage = $old['image'];

$imagePath = $oldImage;

if (!empty($_FILES['image']['name'])) {
    $uploadDir = "../upload/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $fileName = basename($_FILES['image']['name']);
    $ext = pathinfo($fileName, PATHINFO_EXTENSION);
    $safeName = uniqid("img_") . "." . strtolower($ext);
    $targetPath = $uploadDir . $safeName;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
        $imagePath = "upload/" . $safeName;

        // Eski rasmni o‘chirish
        $oldFullPath = "../" . $oldImage;
        if (file_exists($oldFullPath)) {
            unlink($oldFullPath);
        }
    }
}

// Yangilash
$stmt = $conn->prepare("UPDATE products SET name=?, price=?, category=?, image=?, description=? WHERE id=?");
$stmt->bind_param("sisssi", $name, $price, $category, $imagePath, $description, $id);
$stmt->execute();

header("Location: menu.php");
exit();
