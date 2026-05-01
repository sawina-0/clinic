<?php
session_start();
require_once '../config.php';

if (!isLogged()) {
    echo json_encode(['success' => false, 'message' => 'Не авторизован']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$appointment_id = (int)($data['appointment_id'] ?? 0);

if (!$appointment_id) {
    echo json_encode(['success' => false, 'message' => 'Нет ID записи']);
    exit;
}

// Проверяем, что запись принадлежит текущему пользователю
$stmt = $pdo->prepare("SELECT user_id FROM appointments WHERE appointment_id = ?");
$stmt->execute([$appointment_id]);
$owner_id = $stmt->fetchColumn();

if ($owner_id != $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'Это не ваша запись']);
    exit;
}

// Меняем статус
$stmt = $pdo->prepare("UPDATE appointments SET status = 'отменён' WHERE appointment_id = ?");
if ($stmt->execute([$appointment_id])) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка при отмене']);
}