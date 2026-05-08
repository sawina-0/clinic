<?php
session_start();
require_once '../config.php';

if (!isLogged()) {
    http_response_code(403);
    exit;
}

$type = $_GET['type'] ?? '';
$id = (int)($_GET['id'] ?? 0);

if (!$type || !$id) {
    http_response_code(400);
    exit;
}

$data = [];

if ($type === 'user') {
    $stmt = $pdo->prepare("SELECT user_id, surname, name, sec_name, phone_num, role, photo FROM users WHERE user_id = ?");
    $stmt->execute([$id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
} elseif ($type === 'service') {
    $stmt = $pdo->prepare("SELECT service_id, name, price, direction_id, is_public FROM services WHERE service_id = ?");
    $stmt->execute([$id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
} elseif ($type === 'cabinet') {
    $stmt = $pdo->prepare("SELECT cabinet_id, floor, number FROM cabinets WHERE cabinet_id = ?");
    $stmt->execute([$id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
} elseif ($type === 'appointment') {
    $stmt = $pdo->prepare("SELECT appointment_id, user_id, doctor_id, service_id, app_datetime, status FROM appointments WHERE appointment_id = ?");
    $stmt->execute([$id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
} elseif ($type === 'doctor') {
    $stmt = $pdo->prepare("SELECT d.doctor_id, d.exp, d.direction_id, d.cabinet_id, u.user_id, u.surname, u.name, u.sec_name, u.phone_num, u.photo, u.role 
                           FROM doctors d 
                           JOIN users u ON d.user_id = u.user_id 
                           WHERE d.doctor_id = ?");
    $stmt->execute([$id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
} elseif ($type === 'diagnose') {
    $stmt = $pdo->prepare("SELECT diagnose_id, user_id, doctor_id, date, diagnose_text FROM diagnose WHERE diagnose_id = ?");
    $stmt->execute([$id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
} elseif ($type === 'symptom') {
    $stmt = $pdo->prepare("
        SELECT id, keyword, priority 
        FROM symptoms 
        WHERE id = ?
    ");
    $stmt->execute([$id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
}

echo json_encode($data);