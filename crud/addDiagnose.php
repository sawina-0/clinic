<?php
session_start();
require_once '../config.php';

if (!isLogged() || !isDoctor()) {
    http_response_code(403);
    exit;
}

// Получаем doctor_id текущего врача
$stmt = $pdo->prepare("SELECT doctor_id FROM doctors WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$doctor_id = $stmt->fetchColumn();

if (!$doctor_id) {
    echo json_encode(['success' => false, 'message' => 'Врач не найден']);
    exit;
}

$patient_id = (int)($_POST['patient_id'] ?? 0);
$date = $_POST['date'] ?? '';
$diagnose_text = trim($_POST['diagnose_text'] ?? '');
$file_name = null;

if (!$patient_id || !$date || !$diagnose_text) {
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

// Обработка файла
if (isset($_FILES['diagnoseFile']) && $_FILES['diagnoseFile']['error'] === UPLOAD_ERR_OK) {
    $allowed = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $_FILES['diagnoseFile']['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Можно загружать только PDF, JPG, PNG']);
        exit;
    }

    $ext = pathinfo($_FILES['diagnoseFile']['name'], PATHINFO_EXTENSION);
    $file_name = 'diag_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
    move_uploaded_file($_FILES['diagnoseFile']['tmp_name'], "../files/" . $file_name);
}

// Добавляем диагноз
if ($file_name) {
    $stmt = $pdo->prepare("INSERT INTO diagnose (user_id, doctor_id, date, diagnose_text, file_name) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$patient_id, $doctor_id, $mysqlDate, $diagnose_text, $file_name]);
} else {
    $stmt = $pdo->prepare("INSERT INTO diagnose (user_id, doctor_id, date, diagnose_text) VALUES (?, ?, ?, ?)");
    $stmt->execute([$patient_id, $doctor_id, $mysqlDate, $diagnose_text]);
}

if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка при добавлении диагноза']);
}