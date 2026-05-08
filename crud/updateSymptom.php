<?php
session_start();
require_once '../config.php';

if (!isLogged() || !isDoctor()) {
    http_response_code(403);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$symptom_id = (int)($data['symptom_id'] ?? 0);
$keyword = trim($data['keyword'] ?? '');
$priority = (int)($data['priority'] ?? 0);

if (!$symptom_id) {
    echo json_encode(['success' => false, 'message' => 'Не указан ID симптома']);
    exit;
}

if (empty($keyword)) {
    echo json_encode(['success' => false, 'message' => 'Симптом не может быть пустым']);
    exit;
}

if ($priority < 1 || $priority > 10) {
    echo json_encode(['success' => false, 'message' => 'Приоритет должен быть от 1 до 10']);
    exit;
}

// Проверяем, принадлежит ли симптом этому врачу, и получаем direction_id
$stmt = $pdo->prepare("
    SELECT s.direction_id, d.user_id 
    FROM symptoms s
    JOIN doctors d ON s.direction_id = d.direction_id
    WHERE s.id = ? AND d.user_id = ?
");
$stmt->execute([$symptom_id, $_SESSION['user_id']]);
$symptomData = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$symptomData) {
    echo json_encode(['success' => false, 'message' => 'У вас нет прав на редактирование этого симптома']);
    exit;
}

$direction_id = $symptomData['direction_id'];

// Проверка на дубликат (исключая текущий симптом)
$stmt = $pdo->prepare("
    SELECT id FROM symptoms 
    WHERE keyword = ? AND direction_id = ? AND id != ?
");
$stmt->execute([$keyword, $direction_id, $symptom_id]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Такой симптом уже существует в этом направлении']);
    exit;
}

$stmt = $pdo->prepare("UPDATE symptoms SET keyword = ?, priority = ? WHERE id = ?");
if ($stmt->execute([$keyword, $priority, $symptom_id])) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка при обновлении']);
}