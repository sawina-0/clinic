<?php
session_start();
require_once '../config.php';

if (!isLogged() || !isAdmin()) {
    http_response_code(403);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$service_id = (int)($data['service_id'] ?? 0);
$name = trim($data['name'] ?? '');
$direction_id = (int)($data['direction_id'] ?? 0);
$price = (float)($data['price'] ?? 0);
$is_public = (int)($data['is_public'] ?? 1);

if (!$service_id || !$name || !$direction_id || $price <= 0) {
    echo json_encode(['success' => false, 'message' => 'Заполните все поля']);
    exit;
}

// Проверяем уникальность названия (исключая текущую услугу)
$stmt = $pdo->prepare("SELECT service_id FROM services WHERE name = ? AND service_id != ?");
$stmt->execute([$name, $service_id]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Услуга с таким названием уже существует']);
    exit;
}
$price = (float)($data['price'] ?? 0);
if ($price <= 0 || $price > 999999.99) {
    echo json_encode(['success' => false, 'message' => 'Цена должна быть от 0.01 до 999999.99']);
    exit;
}

// Проверяем направление
$stmt = $pdo->prepare("SELECT direction_id FROM directions WHERE direction_id = ?");
$stmt->execute([$direction_id]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Направление не найдено']);
    exit;
}

$stmt = $pdo->prepare("UPDATE services SET name = ?, direction_id = ?, price = ?, is_public = ? WHERE service_id = ?");
if ($stmt->execute([$name, $direction_id, $price, $is_public, $service_id])) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка при обновлении']);
}