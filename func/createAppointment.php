<?php
session_start();
require_once '../config.php';

$data = json_decode(file_get_contents('php://input'), true);

$doctor_id = (int)$data['doctor_id'];
$service_id = (int)$data['service_id'];
$date = $data['date'];      // '21.03.2026'
$time = $data['time'];      // '14:30'

$user_id = $_SESSION['user_id'] ?? 0;

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Не авторизован']);
    exit;
}

// Собираем datetime
$datetime = DateTime::createFromFormat('d.m.Y H:i', "$date $time");
if (!$datetime) {
    echo json_encode(['success' => false, 'message' => 'Неверный формат даты/времени']);
    exit;
}
$mysqlDatetime = $datetime->format('Y-m-d H:i:s');

$stmtCab = $pdo->prepare("SELECT cabinet_id FROM doctors WHERE doctor_id = ?");
$stmtCab->execute([$doctor_id]);
$cabinet_id = $stmtCab->fetchColumn();

if (!$cabinet_id) {
    echo json_encode(['success' => false, 'message' => 'У врача не назначен кабинет']);
    exit;
}

$stmt = $pdo->prepare("
    INSERT INTO appointments 
    (user_id, doctor_id, cabinet_id, app_datetime, service_id, status) 
    VALUES (?, ?, ?, ?, ?, 'запланирован')
");

try {
    $stmt->execute([$user_id, $doctor_id, $cabinet_id, $mysqlDatetime, $service_id]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка БД: ' . $e->getMessage()]);
}