<?php
session_start();
require_once '../config.php';

$file = basename($_GET['file'] ?? '');
if (!$file || !isLogged()) {
    die('Доступ запрещён');
}

$userId = $_SESSION['user_id'];

// Проверяем, принадлежит ли файл этому пользователю
$stmt = $pdo->prepare("SELECT user_id FROM diagnose WHERE file_name = ?");
$stmt->execute([$file]);
$diagnose = $stmt->fetch();

// Доступ разрешён, если:
// 1. Файл принадлежит текущему пользователю
// 2. ИЛИ пользователь — админ/врач/персонал
if ($diagnose && $diagnose['user_id'] == $userId) {
    // свой файл — можно
} elseif (isAdmin() || isDoctor() || isStuff()) {
    // админ/врач/персонал — можно любой
} else {
    die('Доступ запрещён');
}

$filePath = __DIR__ . '/../files/' . $file;
if (!file_exists($filePath)) {
    die('Файл не найден');
}

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $file . '"');
readfile($filePath);
exit;