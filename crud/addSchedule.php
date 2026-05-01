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
    echo json_encode(['success' => false, 'message' => 'Не выбраны дни или врач']);
    exit;
}

// Проверяем, существует ли врач
$stmt = $pdo->prepare("SELECT doctor_id FROM doctors WHERE doctor_id = ?");
$stmt->execute([$doctor_id]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Врач не найден']);
    exit;
}
// Проверяем, есть ли уже график
$stmt = $pdo->prepare("SELECT COUNT(*) FROM doctor_schedule WHERE doctor_id = ?");
$stmt->execute([$doctor_id]);
if ($stmt->fetchColumn() > 0) {
    echo json_encode(['success' => false, 'message' => 'График уже существует']);
    exit;
}

// Добавляем новые дни
$scheduleStmt = $pdo->prepare("INSERT INTO doctor_schedule (doctor_id, schedule_id) VALUES (?, ?)");

foreach ($days as $day) {
    $schedule_id = (int)$day;
    $scheduleStmt->execute([$doctor_id, $schedule_id]);
}

echo json_encode(['success' => true]);