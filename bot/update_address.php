<?php
require '../admin/db.php'; // manzilga moslang

$data = json_decode(file_get_contents("php://input"), true);
$tg_user_id = $data['user_id'] ?? null;
$address = $data['address'] ?? null;

if ($tg_user_id && $address) {
    $stmt = $conn->prepare("UPDATE users SET address = ? WHERE tg_user_id = ?");
    $stmt->bind_param("si", $address, $tg_user_id);
    $stmt->execute();
    $stmt->close();

    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => "Missing user_id or address"]);
}
