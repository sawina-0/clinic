<?php
session_start();
require_once '../config.php';

if (!isLogged() || !isAdmin()) {
    http_response_code(403);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$doctor_id = (int)($data['doctor_id'] ?? 0);
$days = $data['days'] ?? [];

if (!$doctor_id || empty($days)) {
    echo json_encode(['success' => false, 'message' => 'Выберите хотя бы один день']);
    exit;
}

// Проверяем существование врача
$stmt = $pdo->prepare("SELECT doctor_id FROM doctors WHERE doctor_id = ?");
$stmt->execute([$doctor_id]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Врач не найден']);
    exit;
}

// Удаляем старый график
$stmt = $pdo->prepare("DELETE FROM doctor_schedule WHERE doctor_id = ?");
$stmt->execute([$doctor_id]);

// Добавляем новые дни
$insertStmt = $pdo->prepare("INSERT INTO doctor_schedule (doctor_id, schedule_id) VALUES (?, ?)");
foreach ($days as $day) {
    $insertStmt->execute([$doctor_id, (int)$day]);
}

echo json_encode(['success' => true]);