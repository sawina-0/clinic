<?php
session_start();
require_once '../config.php';

if (!isLogged()) {
    http_response_code(403);
    exit;
}

// Только админ, врач или персонал могут редактировать записи
if (!isAdmin() && !isDoctor() && !isStuff()) {
    http_response_code(403);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$appointment_id = (int)($data['appointment_id'] ?? 0);
$patient_id = (int)($data['patient_id'] ?? 0);
$doctor_id = (int)($data['doctor_id'] ?? 0);
$service_id = (int)($data['service_id'] ?? 0);
$status = $data['status'] ?? '';
$date = $data['date'] ?? null;
$time = $data['time'] ?? null;

if (!$appointment_id || !$patient_id || !$doctor_id || !$service_id || !$status) {
    echo json_encode(['success' => false, 'message' => 'Заполните все поля']);
    exit;
}

// Проверяем соответствие направления
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

// Если врач — проверяем, что запись его
if (isDoctor() || isStuff()) {
    $stmt = $pdo->prepare("SELECT doctor_id FROM appointments WHERE appointment_id = ?");
    $stmt->execute([$appointment_id]);
    $appDoctorId = $stmt->fetchColumn();
    
    $userDoctorId = $pdo->prepare("SELECT doctor_id FROM doctors WHERE user_id = ?");
    $userDoctorId->execute([$_SESSION['user_id']]);
    $currentDoctorId = $userDoctorId->fetchColumn();
    
    if ($appDoctorId != $currentDoctorId) {
        echo json_encode(['success' => false, 'message' => 'Нет прав для редактирования этой записи']);
        exit;
    }
}

// Формируем datetime, если есть дата и время
$mysqlDatetime = null;
if ($date && $time) {
    $datetime = DateTime::createFromFormat('d.m.Y H:i', "$date $time");
    if (!$datetime) {
        echo json_encode(['success' => false, 'message' => 'Неверный формат даты/времени']);
        exit;
    }
    $mysqlDatetime = $datetime->format('Y-m-d H:i:s');
    
    // Проверяем, свободно ли время (исключая текущую запись)
    $stmt = $pdo->prepare("
        SELECT appointment_id FROM appointments
        WHERE doctor_id = ? AND app_datetime = ? AND appointment_id != ?
    ");
    $stmt->execute([$doctor_id, $mysqlDatetime, $appointment_id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Это время уже занято']);
        exit;
    }
}

// Если указана дата, но нет времени — ошибка
if ($date && !$time) {
    echo json_encode(['success' => false, 'message' => 'Выберите время']);
    exit;
}

// Проверяем, нет ли у пациента другой записи в это же время (исключая текущую)
if ($mysqlDatetime) {
    $stmt = $pdo->prepare("
        SELECT appointment_id FROM appointments
        WHERE user_id = ? AND app_datetime = ? AND appointment_id != ?
    ");
    $stmt->execute([$patient_id, $mysqlDatetime, $appointment_id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'У пациента уже есть запись на это время']);
        exit;
    }
}

// Получаем кабинет врача
$stmt = $pdo->prepare("SELECT cabinet_id FROM doctors WHERE doctor_id = ?");
$stmt->execute([$doctor_id]);
$cabinet_id = $stmt->fetchColumn();

if (!$cabinet_id) {
    echo json_encode(['success' => false, 'message' => 'У врача не назначен кабинет']);
    exit;
}

// Обновляем запись
if ($mysqlDatetime) {
    $stmt = $pdo->prepare("
        UPDATE appointments 
        SET user_id = ?, doctor_id = ?, cabinet_id = ?, service_id = ?, app_datetime = ?, status = ?
        WHERE appointment_id = ?
    ");
    $stmt->execute([$patient_id, $doctor_id, $cabinet_id, $service_id, $mysqlDatetime, $status, $appointment_id]);
} else {
    $stmt = $pdo->prepare("
        UPDATE appointments 
        SET user_id = ?, doctor_id = ?, cabinet_id = ?, service_id = ?, status = ?
        WHERE appointment_id = ?
    ");
    $stmt->execute([$patient_id, $doctor_id, $cabinet_id, $service_id, $status, $appointment_id]);
}

if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка при обновлении']);
}