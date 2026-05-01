<?php
session_start();
require_once '../config.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT 
        a.doctor_id,
        a.service_id,
        a.app_datetime,
        CONCAT(u.surname, ' ', u.name, ' ', u.sec_name) as doctor_name,
        u.photo as doctor_photo,
        s.name as service_name,
        s.price as service_price
    FROM appointments a
    JOIN doctors d ON a.doctor_id = d.doctor_id
    JOIN users u ON d.user_id = u.user_id
    JOIN services s ON a.service_id = s.service_id
    WHERE a.appointment_id = ?
");
$stmt->execute([$id]);
$app = $stmt->fetch(PDO::FETCH_ASSOC);

// Получаем расписание врача
$stmt = $pdo->prepare("
    SELECT DISTINCT s.day_of_week 
    FROM doctor_schedule ds
    JOIN schedule s ON ds.schedule_id = s.schedule_id
    WHERE ds.doctor_id = ?
");
$stmt->execute([$app['doctor_id']]);
$app['schedule'] = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo json_encode($app);