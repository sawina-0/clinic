<?php
session_start();
require_once '../config.php';

if (!isLogged() || !isAdmin()) {
    http_response_code(403);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$floor = (int)($data['floor'] ?? 0);
$number = trim($data['number'] ?? '');

if ($floor <= 0 || empty($number)) {
    echo json_encode(['success' => false, 'message' => 'Заполните этаж и номер кабинета']);
    exit;
}

// Проверяем уникальность номера кабинета
$stmt = $pdo->prepare("SELECT cabinet_id FROM cabinets WHERE number = ?");
$stmt->execute([$number]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Кабинет с таким номером уже существует']);
    exit;
}


// Добавляем кабинет
$stmt = $pdo->prepare("INSERT INTO cabinets (floor, number) VALUES (?, ?)");
if ($stmt->execute([$floor, $number])) {
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка при добавлении']);
}