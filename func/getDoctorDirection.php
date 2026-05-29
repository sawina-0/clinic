<?php
session_start();
require_once '../config.php';

if (!isLogged() || !isDoctor() || !isStuff()) {
    echo json_encode(['direction_id' => 0, 'direction_name' => '']);
    exit;
}

$stmt = $pdo->prepare("
    SELECT d.direction_id, dir.name as direction_name
    FROM doctors d
    JOIN directions dir ON d.direction_id = dir.direction_id
    WHERE d.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    'direction_id' => $result['direction_id'] ?? 0,
    'direction_name' => $result['direction_name'] ?? ''
]);