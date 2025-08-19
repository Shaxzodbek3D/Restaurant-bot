<?php
require_once __DIR__ . '/../bot/config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $name = trim($_POST["name"]);
  if (!empty($name)) {
    $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
    $stmt->bind_param("s", $name);
    $stmt->execute();
  }
}

header("Location: menu.php");
exit();
