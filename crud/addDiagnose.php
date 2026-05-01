<?php
session_start();
require_once '../config.php';

if (!isLogged() || !isDoctor()) {
    http_response_code(403);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$patient_id = (int)($data['patient_id'] ?? 0);
$doctor_id = (int)($data['doctor_id'] ?? 0);
$diagnose_text = trim($data['diagnose_text'] ?? '');
$date = $data['date'] ?? '';

if (!$patient_id || !$doctor_id || !$diagnose_text || !$date) {
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

// Проверяем пациента
$stmt = $pdo->prepare("SELECT user_id FROM users WHERE user_id = ? AND role = 'Пользователь'");
$stmt->execute([$patient_id]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Пациент не найден']);
    exit;
}

// Проверяем врача
$stmt = $pdo->prepare("SELECT doctor_id FROM doctors WHERE doctor_id = ?");
$stmt->execute([$doctor_id]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Врач не найден']);
    exit;
}

// Добавляем диагноз
$stmt = $pdo->prepare("INSERT INTO diagnose (user_id, doctor_id, date, diagnose_text) VALUES (?, ?, ?, ?)");
if ($stmt->execute([$patient_id, $doctor_id, $mysqlDate, $diagnose_text])) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка при добавлении диагноза']);
}