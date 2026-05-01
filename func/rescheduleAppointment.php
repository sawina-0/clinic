<?php
session_start();
require_once '../config.php';

if (!isLogged()) {
    echo json_encode(['success' => false, 'message' => 'Не авторизован']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$appointment_id = (int)($data['appointment_id'] ?? 0);
$date = $data['date'] ?? '';
$time = $data['time'] ?? '';

if (!$appointment_id || !$date || !$time) {
    echo json_encode(['success' => false, 'message' => 'Не все данные переданы']);
    exit;
}

// Проверяем, что запись принадлежит текущему пользователю
$stmt = $pdo->prepare("SELECT user_id, doctor_id FROM appointments WHERE appointment_id = ?");
$stmt->execute([$appointment_id]);
$app = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$app || $app['user_id'] != $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'Запись не найдена или не принадлежит вам']);
    exit;
}

// Формируем новую дату и время
$datetime = DateTime::createFromFormat('d.m.Y H:i', "$date $time");
if (!$datetime) {
    echo json_encode(['success' => false, 'message' => 'Неверный формат даты/времени']);
    exit;
}
$mysqlDatetime = $datetime->format('Y-m-d H:i:s');

// Проверяем, свободно ли это время у врача
$stmt = $pdo->prepare("
    SELECT appointment_id FROM appointments 
    WHERE doctor_id = ? AND app_datetime = ? AND appointment_id != ?
");
$stmt->execute([$app['doctor_id'], $mysqlDatetime, $appointment_id]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Это время уже занято']);
    exit;
}

// Обновляем запись
$stmt = $pdo->prepare("UPDATE appointments SET app_datetime = ? WHERE appointment_id = ?");
if ($stmt->execute([$mysqlDatetime, $appointment_id])) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка при обновлении']);
}