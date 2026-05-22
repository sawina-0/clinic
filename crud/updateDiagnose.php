<?php
session_start();
require_once '../config.php';

if (!isLogged() || !isDoctor()) {
    http_response_code(403);
    exit;
}

// Получаем doctor_id текущего пользователя
$stmt = $pdo->prepare("SELECT doctor_id FROM doctors WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$currentDoctorId = $stmt->fetchColumn();

if (!$currentDoctorId) {
    echo json_encode(['success' => false, 'message' => 'Врач не найден']);
    exit;
}

// Определяем, откуда пришли данные
$isJson = false;
$input = json_decode(file_get_contents('php://input'), true);
if ($input && isset($input['diagnose_id'])) {
    $isJson = true;
    $diagnose_id = (int)$input['diagnose_id'];
    $patient_id = (int)$input['patient_id'];
    $date = $input['date'];
    $diagnose_text = trim($input['diagnose_text']);
} else {
    $diagnose_id = (int)($_POST['diagnose_id'] ?? 0);
    $patient_id = (int)($_POST['patient_id'] ?? 0);
    $date = $_POST['date'] ?? '';
    $diagnose_text = trim($_POST['diagnose_text'] ?? '');
}

if (!$diagnose_id || !$patient_id || !$date || !$diagnose_text) {
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

// Проверяем, принадлежит ли диагноз текущему врачу
$stmt = $pdo->prepare("SELECT doctor_id FROM diagnose WHERE diagnose_id = ?");
$stmt->execute([$diagnose_id]);
$oldDoctorId = $stmt->fetchColumn();

if ($oldDoctorId != $currentDoctorId) {
    echo json_encode(['success' => false, 'message' => 'Нет прав для редактирования этого диагноза']);
    exit;
}

// Обработка файла (только если пришёл через FormData)
$file_name = null;
if (!$isJson && isset($_FILES['diagnoseFile']) && $_FILES['diagnoseFile']['error'] === UPLOAD_ERR_OK) {
    $allowed = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $_FILES['diagnoseFile']['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Можно загружать только PDF, JPG, PNG']);
        exit;
    }

    $ext = pathinfo($_FILES['diagnoseFile']['name'], PATHINFO_EXTENSION);
    $file_name = 'diag_' . $diagnose_id . '_' . time() . '.' . $ext;
    move_uploaded_file($_FILES['diagnoseFile']['tmp_name'], "../files/" . $file_name);

    // Удаляем старый файл, если был
    $stmt = $pdo->prepare("SELECT file_name FROM diagnose WHERE diagnose_id = ?");
    $stmt->execute([$diagnose_id]);
    $oldFile = $stmt->fetchColumn();
    if ($oldFile && file_exists("../files/" . $oldFile)) {
        unlink("../files/" . $oldFile);
    }
}

// Обновляем диагноз
if ($file_name) {
    $stmt = $pdo->prepare("UPDATE diagnose SET user_id = ?, date = ?, diagnose_text = ?, file_name = ? WHERE diagnose_id = ?");
    $stmt->execute([$patient_id, $mysqlDate, $diagnose_text, $file_name, $diagnose_id]);
} else {
    $stmt = $pdo->prepare("UPDATE diagnose SET user_id = ?, date = ?, diagnose_text = ? WHERE diagnose_id = ?");
    $stmt->execute([$patient_id, $mysqlDate, $diagnose_text, $diagnose_id]);
}

if ($stmt->rowCount() >= 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка при обновлении']);
}