<?php
session_start();
require_once '../config.php';

if (!isLogged()) {
    http_response_code(403);
    exit;
}

// Дополнительные проверки прав
if (!isAdmin() && !isDoctor() && !isStuff()) {
    http_response_code(403);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$patient_id = (int)($data['patient_id'] ?? 0);
$doctor_id = (int)($data['doctor_id'] ?? 0);
$service_id = (int)($data['service_id'] ?? 0);
$date = $data['date'] ?? '';
$time = $data['time'] ?? '';
$status = $data['status'] ?? 'запланирован';

if (!$patient_id || !$doctor_id || !$service_id || !$date || !$time) {
    echo json_encode(['success' => false, 'message' => 'Заполните все поля']);
    exit;
}

// Формируем datetime
$datetime = DateTime::createFromFormat('d.m.Y H:i', "$date $time");
if (!$datetime) {
    echo json_encode(['success' => false, 'message' => 'Неверный формат даты/времени']);
    exit;
}
$mysqlDatetime = $datetime->format('Y-m-d H:i:s');

// Проверяем, существует ли пациент
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

// Проверяем услугу
$stmt = $pdo->prepare("SELECT service_id FROM services WHERE service_id = ?");
$stmt->execute([$service_id]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Услуга не найдена']);
    exit;
}

// Получаем кабинет врача
$stmt = $pdo->prepare("SELECT cabinet_id FROM doctors WHERE doctor_id = ?");
$stmt->execute([$doctor_id]);
$cabinet_id = $stmt->fetchColumn();

if (!$cabinet_id) {
    echo json_encode(['success' => false, 'message' => 'У врача не назначен кабинет']);
    exit;
}

// Проверяем, свободно ли время
$stmt = $pdo->prepare("SELECT appointment_id FROM appointments WHERE doctor_id = ? AND app_datetime = ?");
$stmt->execute([$doctor_id, $mysqlDatetime]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Это время уже занято']);
    exit;
}

$stmt = $pdo->prepare("
    SELECT s.direction_id as service_dir, d.direction_id as doctor_dir
    FROM services s, doctors d 
    WHERE s.service_id = ? AND d.doctor_id = ?
");
$stmt->execute([$service_id, $doctor_id]);
$dirs = $stmt->fetch(PDO::FETCH_ASSOC);
if ($dirs['service_dir'] != $dirs['doctor_dir']) {
    echo json_encode(['success' => false, 'message' => 'Услуга и врач из разных направлений']);
    exit;
}

// Добавляем запись
$stmt = $pdo->prepare("INSERT INTO appointments (user_id, doctor_id, cabinet_id, app_datetime, service_id, status) VALUES (?, ?, ?, ?, ?, ?)");
if ($stmt->execute([$patient_id, $doctor_id, $cabinet_id, $mysqlDatetime, $service_id, $status])) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка при добавлении']);
}