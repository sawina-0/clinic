<?php
session_start();
require_once '../config.php';

if (!isLogged() || !isStuff()) {
    http_response_code(403);
    exit;
}

$patient_id = (int)($_POST['patient_id'] ?? 0);
$service_id = (int)($_POST['service_id'] ?? 0);
$doctor_id = (int)($_POST['doctor_id'] ?? 0);
$date = $_POST['date'] ?? '';

if (!$patient_id || !$service_id || !$doctor_id || !$date || !isset($_FILES['file'])) {
    echo json_encode(['success' => false, 'message' => 'Заполните все поля']);
    exit;
}

// Проверка файла
$allowed = ['application/pdf', 'image/jpeg', 'image/png'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $_FILES['file']['tmp_name']);
finfo_close($finfo);

if (!in_array($mime, $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Можно загружать только PDF, JPG, PNG']);
    exit;
}

$ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
$fileName = 'analysis_' . time() . '_' . rand(100, 999) . '.' . $ext;
move_uploaded_file($_FILES['file']['tmp_name'], "../files/" . $fileName);

$dateObj = DateTime::createFromFormat('d.m.Y', $date);
if (!$dateObj) {
    echo json_encode(['success' => false, 'message' => 'Неверный формат даты']);
    exit;
}
$mysqlDate = $dateObj->format('Y-m-d');

$stmt = $pdo->prepare("INSERT INTO analyzes (user_id, service_id, doctor_id, date, file_name) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$patient_id, $service_id, $doctor_id, $mysqlDate, $fileName]);

echo json_encode(['success' => true]);