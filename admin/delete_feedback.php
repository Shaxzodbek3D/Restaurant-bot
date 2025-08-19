<?php
// admin/delete_feedback.php

header('Content-Type: application/json');

// Markaziy sozlamalarni chaqiramiz
require_once __DIR__ . '/../bot/config.php';

// POST so'rov orqali kelgan ID ni olamiz
$id = $_POST['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'error' => 'ID topilmadi.']);
    exit;
}

// Xavfsizlik uchun tayyorlangan so'rovdan (prepared statement) foydalanamiz
$stmt = $conn->prepare("DELETE FROM feedback WHERE id = ?");
if ($stmt === false) {
    echo json_encode(['success' => false, 'error' => 'So\'rovni tayyorlashda xatolik: ' . $conn->error]);
    exit;
}

$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    // Muvaffaqiyatli o'chirilganda
    echo json_encode(['success' => true]);
} else {
    // Xatolik yuz berganda
    echo json_encode(['success' => false, 'error' => 'Ma\'lumotni o\'chirishda xatolik: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>