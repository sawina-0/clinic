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

$stmt = $pdo->prepare("UPDATE doctor_details SET education = ?, qualification = ?, awards = ? WHERE doctor_id = ?");
if ($stmt->execute([$education, $qualification, $awards, $doctor_id])) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка при обновлении']);
}