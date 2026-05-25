<?php
session_start();
require_once '../config.php';

if (!isLogged() || !isStuff()) {
    http_response_code(403);
    exit;
}

$analysis_id = (int)($_POST['analysis_id'] ?? 0);
$patient_id = (int)($_POST['patient_id'] ?? 0);
$service_id = (int)($_POST['service_id'] ?? 0);
$date = $_POST['date'] ?? '';

if (!$analysis_id || !$patient_id || !$service_id ||  !$date) {
    echo json_encode(['success' => false, 'message' => 'Заполните все поля']);
    exit;
}

$dateObj = DateTime::createFromFormat('d.m.Y', $date);
if (!$dateObj) {
    echo json_encode(['success' => false, 'message' => 'Неверный формат даты']);
    exit;
}
$mysqlDate = $dateObj->format('Y-m-d');

// Обработка нового файла
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $allowed = ['application/pdf', 'image/jpeg', 'image/png'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $_FILES['file']['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Можно загружать только PDF, JPG, PNG']);
        exit;
    }

    // Удаляем старый файл
    $stmt = $pdo->prepare("SELECT file_name FROM analyzes WHERE analysis_id = ?");
    $stmt->execute([$analysis_id]);
    $oldFile = $stmt->fetchColumn();
    if ($oldFile && file_exists("../files/" . $oldFile)) {
        unlink("../files/" . $oldFile);
    }

    // Сохраняем новый
    $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    $fileName = 'analysis_' . time() . '_' . rand(100, 999) . '.' . $ext;
    move_uploaded_file($_FILES['file']['tmp_name'], "../files/" . $fileName);

    $stmt = $pdo->prepare("UPDATE analyzes SET user_id = ?, service_id = ?, date = ?, file_name = ? WHERE analysis_id = ?");
    $stmt->execute([$patient_id, $service_id, $mysqlDate, $fileName, $analysis_id]);
} else {
    $stmt = $pdo->prepare("UPDATE analyzes SET user_id = ?, service_id = ?, date = ? WHERE analysis_id = ?");
    $stmt->execute([$patient_id, $service_id, $mysqlDate, $analysis_id]);
}

echo json_encode(['success' => true]);