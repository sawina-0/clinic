<?php
session_start();
require_once '../config.php';

if (!isLogged() || !isDoctor()) {
    http_response_code(403);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$keyword = trim($data['keyword'] ?? '');
$priority = (int)($data['priority'] ?? 0);
$direction_id = (int)($data['direction_id'] ?? 0);

if (empty($keyword)) {
    echo json_encode(['success' => false, 'message' => 'Симптом не может быть пустым']);
    exit;
}

if ($priority < 1 || $priority > 10) {
    echo json_encode(['success' => false, 'message' => 'Приоритет должен быть от 1 до 10']);
    exit;
}

if (!$direction_id) {
    echo json_encode(['success' => false, 'message' => 'Направление не указано']);
    exit;
}

// Проверяем, принадлежит ли направление этому врачу
$stmt = $pdo->prepare("SELECT doctor_id FROM doctors WHERE user_id = ? AND direction_id = ?");
$stmt->execute([$_SESSION['user_id'], $direction_id]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'У вас нет прав на это направление']);
    exit;
}

// Проверяем на дубликат
$stmt = $pdo->prepare("SELECT id FROM symptoms WHERE keyword = ? AND direction_id = ?");
$stmt->execute([$keyword, $direction_id]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Такой симптом уже существует в этом направлении']);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO symptoms (keyword, priority, direction_id) VALUES (?, ?, ?)");
if ($stmt->execute([$keyword, $priority, $direction_id])) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка при добавлении']);
}