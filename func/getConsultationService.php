<?php
session_start();
require_once '../config.php';

$doctor_id = isset($_GET['doctor_id']) ? (int)$_GET['doctor_id'] : 0;

if (!$doctor_id) {
    echo json_encode(['error' => 'Не указан врач']);
    exit;
}

// Получаем направление врача
$stmt = $pdo->prepare("SELECT direction_id FROM doctors WHERE doctor_id = ?");
$stmt->execute([$doctor_id]);
$direction_id = $stmt->fetchColumn();

if (!$direction_id) {
    echo json_encode(['error' => 'Направление врача не найдено']);
    exit;
}

// Ищем услугу-консультацию для этого направления
$stmt = $pdo->prepare("
    SELECT service_id, name, price 
    FROM services 
    WHERE direction_id = ? AND name LIKE '%Консультация%' AND is_public = 1
    LIMIT 1
");
$stmt->execute([$direction_id]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$service) {
    // Если нет консультации — берём любую публичную услугу этого направления
    $stmt = $pdo->prepare("
        SELECT service_id, name, price 
        FROM services 
        WHERE direction_id = ? AND is_public = 1
        LIMIT 1
    ");
    $stmt->execute([$direction_id]);
    $service = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$service) {
    echo json_encode(['error' => 'Для этого врача нет доступных услуг']);
    exit;
}

echo json_encode([
    'service_id' => $service['service_id'],
    'service_name' => $service['name'],
    'price' => $service['price']
]);
?>