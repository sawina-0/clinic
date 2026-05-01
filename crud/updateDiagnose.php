<?php
session_start();
require_once '../config.php';

if (!isLogged() || !isDoctor()) {
    http_response_code(403);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$diagnose_id = (int)($data['diagnose_id'] ?? 0);
$patient_id = (int)($data['patient_id'] ?? 0);
$doctor_id = (int)($data['doctor_id'] ?? 0);
$date = $data['date'] ?? '';
$diagnose_text = trim($data['diagnose_text'] ?? '');

if (!$diagnose_id || !$patient_id || !$doctor_id || !$date || !$diagnose_text) {
    echo json_encode(['success' => false, 'message' => 'Заполните все поля']);
    exit;
}

// Преобразуем дату
$dateObj = DateTime::createFromFormat('d.m.Y', $date);
if (!$dateObj) {
    echo json_encode(['success' => false, 'message' => 'Неверный формат даты']);
    exit;
}
$mysqlDate = $dateObj->format('Y-m-d');

// Проверяем, что диагноз принадлежит текущему врачу
$stmt = $pdo->prepare("SELECT doctor_id FROM diagnose WHERE diagnose_id = ?");
$stmt->execute([$diagnose_id]);
$oldDoctorId = $stmt->fetchColumn();

if ($oldDoctorId != $doctor_id) {
    echo json_encode(['success' => false, 'message' => 'Нет прав для редактирования этого диагноза']);
    exit;
}

// Обновляем диагноз
$stmt = $pdo->prepare("UPDATE diagnose SET user_id = ?, doctor_id = ?, date = ?, diagnose_text = ? WHERE diagnose_id = ?");
if ($stmt->execute([$patient_id, $doctor_id, $mysqlDate, $diagnose_text, $diagnose_id])) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка при обновлении']);
}