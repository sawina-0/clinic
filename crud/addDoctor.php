<?php
session_start();
require_once '../config.php';

if (!isLogged() || !isAdmin()) {
    http_response_code(403);
    exit;
}


$data = json_decode(file_get_contents('php://input'), true);

$user_id = (int)($data['user_id'] ?? 0);
$role = $data['role'] ?? '';
$direction_id = (int)($data['direction_id'] ?? 0);
$cabinet_id = !empty($data['cabinet_id']) ? (int)$data['cabinet_id'] : null;
$exp = (int)($data['exp'] ?? 0);

if (!$user_id || !$role || !$direction_id || $exp <= 0) {
    echo json_encode(['success' => false, 'message' => 'Заполните все поля']);
    exit;
}

// Проверяем, что пользователь существует и не является уже врачом/персоналом
$stmt = $pdo->prepare("SELECT role FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Пользователь не найден']);
    exit;
}
if (in_array($user['role'], ['Доктор', 'Персонал', 'Администратор'])) {
    echo json_encode(['success' => false, 'message' => 'Пользователь уже имеет роль ' . $user['role']]);
    exit;
}

// Проверяем направление
$stmt = $pdo->prepare("SELECT direction_id FROM directions WHERE direction_id = ?");
$stmt->execute([$direction_id]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Направление не найдено']);
    exit;
}

$exp = (int)($data['exp'] ?? 0);
if ($exp < 0) {
    echo json_encode(['success' => false, 'message' => 'Стаж не может быть отрицательным']);
    exit;
}

// Проверка соответствия роли и направления
$allowedStaffDirections = [11, 12, 13]; // замени на реальные ID процедурного кабинета, лаборатории, лучевой диагностики

if ($role === 'Персонал') {
    if (!in_array($direction_id, $allowedStaffDirections)) {
        echo json_encode(['success' => false, 'message' => 'Персонал может работать только в процедурном кабинете, лаборатории или лучевой диагностике']);
        exit;
    }
} elseif ($role === 'Доктор') {
    if (in_array($direction_id, $allowedStaffDirections)) {
        echo json_encode(['success' => false, 'message' => 'Врач не может работать в процедурном кабинете, лаборатории или лучевой диагностике']);
        exit;
    }
}

// Проверяем кабинет (если выбран)
if ($cabinet_id) {
    $stmt = $pdo->prepare("SELECT cabinet_id FROM cabinets WHERE cabinet_id = ?");
    $stmt->execute([$cabinet_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Кабинет не найден']);
        exit;
    }
}

// Обновляем роль пользователя
$stmt = $pdo->prepare("UPDATE users SET role = ? WHERE user_id = ?");
$stmt->execute([$role, $user_id]);

// Добавляем запись в doctors
$stmt = $pdo->prepare("INSERT INTO doctors (user_id, exp, direction_id, cabinet_id) VALUES (?, ?, ?, ?)");
if ($stmt->execute([$user_id, $exp, $direction_id, $cabinet_id])) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка при добавлении врача']);
}