<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION["admin"])) {
    header("Location: admin.php");
    exit();
}

require_once __DIR__ . '/../bot/config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $price = intval($_POST["price"] ?? 0);
    $category = trim($_POST["category"] ?? "");
    $description = trim($_POST["description"] ?? "");

    // Rasmni yuklash
    $uploadDir = "../upload/";
    $imageUrl = "";

    if (isset($_FILES["image"]) && $_FILES["image"]["error"] === UPLOAD_ERR_OK) {
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = basename($_FILES["image"]["name"]);
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        $safeName = uniqid("img_") . "." . strtolower($ext);
        $targetPath = $uploadDir . $safeName;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetPath)) {
            $imageUrl = "upload/" . $safeName;
        }
    }

    // Ma’lumotlarni bazaga yozish
    if (!empty($name) && $price > 0 && !empty($category) && !empty($imageUrl)) {
        $stmt = $conn->prepare("INSERT INTO products (name, price, category, image, description) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sisss", $name, $price, $category, $imageUrl, $description);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: menu.php");
    exit();
}
