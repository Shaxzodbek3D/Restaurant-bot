<?php
header("Content-Type: application/json");

$conn = new mysqli("localhost", "bbhubuz_appuser", "$#@xzod1312", "bbhubuz_appetito");
if ($conn->connect_error) {
  echo json_encode(["error" => "Bazaga ulanishda xatolik"]);
  exit;
}

$result = $conn->query("SELECT * FROM products ORDER BY id DESC");
$products = [];

while ($row = $result->fetch_assoc()) {
  $products[] = [
    "id" => $row["id"],
    "name" => $row["name"],
    "price" => $row["price"],
    "category" => $row["category"],
    "image" => $row["image"],
    "description" => $row['description']
  ];
}

echo json_encode($products);
