<?php
session_start();
require_once '../config.php';

if (!isLogged() || !isAdmin()) {
    http_response_code(403);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$name = trim($data['name'] ?? '');
$direction_id = (int)($data['direction_id'] ?? 0);
$price = (float)($data['price'] ?? 0);
$is_public = (int)($data['is_public'] ?? 1);

if (empty($name) || $direction_id <= 0 || $price <= 0) {
    echo json_encode(['success' => false, 'message' => 'Заполните все поля']);
    exit;
}

// Проверяем, существует ли направление
$stmt = $pdo->prepare("SELECT direction_id FROM directions WHERE direction_id = ?");
$stmt->execute([$direction_id]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Направление не найдено']);
    exit;
}
$price = (float)($data['price'] ?? 0);
if ($price <= 0 || $price > 999999.99) {
    echo json_encode(['success' => false, 'message' => 'Цена должна быть от 0.01 до 999999.99']);
    exit;
}

// Проверяем уникальность названия
$stmt = $pdo->prepare("SELECT service_id FROM services WHERE name = ?");
$stmt->execute([$name]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Услуга с таким названием уже существует']);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO services (name, direction_id, price, is_public) VALUES (?, ?, ?, ?)");
if ($stmt->execute([$name, $direction_id, $price, $is_public])) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка при добавлении']);
}