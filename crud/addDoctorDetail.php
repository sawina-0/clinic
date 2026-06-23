<?php
session_start();
require_once '../config.php';

if (!isLogged() || !isAdmin()) {
    http_response_code(403);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$doctor_id = (int)($data['doctor_id'] ?? 0);
$education = trim($data['education'] ?? '');
$qualification = trim($data['qualification'] ?? '');
$awards = trim($data['awards'] ?? '');

$qualification = $qualification !== '' ? $qualification : null;
$awards = $awards !== '' ? $awards : null;

if (!$doctor_id || empty($education)) {
    echo json_encode(['success' => false, 'message' => 'Заполните образование']);
    exit;
}

// Проверяем, есть ли уже детали у врача
$stmt = $pdo->prepare("SELECT dd_id FROM doctor_details WHERE doctor_id = ?");
$stmt->execute([$doctor_id]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Детали уже существуют, используйте редактирование']);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO doctor_details (doctor_id, education, qualification, awards) VALUES (?, ?, ?, ?)");
if ($stmt->execute([$doctor_id, $education, $qualification, $awards])) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка при добавлении']);
}