<?php
session_start();
require_once '../config.php';

$doctor_id = isset($_GET['doctor_id']) ? (int)$_GET['doctor_id'] : 0;


if (!$doctor_id && isDoctor()) {
    $stmt = $pdo->prepare("SELECT doctor_id FROM doctors WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $doctor_id = $stmt->fetchColumn();
}

if (!$doctor_id) {
    echo json_encode([]);
    exit;
}

// Получаем дни недели, когда работает врач
$stmt = $pdo->prepare("
    SELECT DISTINCT s.day_of_week 
    FROM doctor_schedule ds
    JOIN schedule s ON ds.schedule_id = s.schedule_id
    WHERE ds.doctor_id = ?
");
$stmt->execute([$doctor_id]);
$days = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Преобразуем в формат для flatpickr (0 = вс, 1 = пн, ..., 6 = сб)
$days = array_map(function($day) {
    return (int)$day;
}, $days);

echo json_encode($days);