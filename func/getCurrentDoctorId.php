<?php
session_start();
require_once '../config.php';

if (!isLogged() || !(isDoctor() || isStuff())) {
    echo json_encode(['doctor_id' => 0]);
    exit;
}

$stmt = $pdo->prepare("SELECT doctor_id FROM doctors WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$doctor_id = $stmt->fetchColumn();

echo json_encode(['doctor_id' => $doctor_id ?: 0]);