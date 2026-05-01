<?php
session_start();
require_once '../config.php';

if (!isLogged() || !isAdmin()) {
    http_response_code(403);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$doctor_id = (int)($data['doctor_id'] ?? 0);
$role = $data['role'] ?? '';
$direction_id = (int)($data['direction_id'] ?? 0);
$cabinet_id = !empty($data['cabinet_id']) ? (int)$data['cabinet_id'] : null;
$exp = (int)($data['exp'] ?? 0);

if (!$doctor_id || !$role || !$direction_id || $exp < 0) {
    echo json_encode(['success' => false, 'message' => 'Заполните все поля']);
    exit;
}

// Проверка для персонала
$staffDirections = [11, 12, 13];
if ($role === 'Персонал' && !in_array($direction_id, $staffDirections)) {
    echo json_encode(['success' => false, 'message' => 'Персонал может работать только в процедурном кабинете, лаборатории или лучевой диагностике']);
    exit;
}

// Получаем user_id врача
$stmt = $pdo->prepare("SELECT user_id FROM doctors WHERE doctor_id = ?");
$stmt->execute([$doctor_id]);
$user_id = $stmt->fetchColumn();

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Врач не найден']);
    exit;
}

// Обновляем роль пользователя
$stmt = $pdo->prepare("UPDATE users SET role = ? WHERE user_id = ?");
$stmt->execute([$role, $user_id]);

// Обновляем данные врача
$stmt = $pdo->prepare("UPDATE doctors SET direction_id = ?, cabinet_id = ?, exp = ? WHERE doctor_id = ?");
if ($stmt->execute([$direction_id, $cabinet_id, $exp, $doctor_id])) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка при обновлении']);
}