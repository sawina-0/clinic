<?php
session_start();
require_once '../config.php';

if (!isLogged() || !isAdmin()) {
    http_response_code(403);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$cabinet_id = (int)($data['cabinet_id'] ?? 0);
$floor = (int)($data['floor'] ?? 0);
$number = trim($data['number'] ?? '');

if (!$cabinet_id || !$floor || !$number) {
    echo json_encode(['success' => false, 'message' => 'Заполните все поля']);
    exit;
}

// Проверяем уникальность номера кабинета (исключая текущий)
$stmt = $pdo->prepare("SELECT cabinet_id FROM cabinets WHERE number = ? AND cabinet_id != ?");
$stmt->execute([$number, $cabinet_id]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Кабинет с таким номером уже существует']);
    exit;
}

$stmt = $pdo->prepare("UPDATE cabinets SET floor = ?, number = ? WHERE cabinet_id = ?");
if ($stmt->execute([$floor, $number, $cabinet_id])) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка при обновлении']);
}