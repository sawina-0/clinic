<?php
session_start();
require_once '../config.php';

$file = basename($_GET['file'] ?? '');
if (!$file || !isLogged()) {
    die('Доступ запрещён');
}

$userId = $_SESSION['user_id'];
$allowed = false;

// Проверяем, принадлежит ли файл пользователю через таблицу diagnose
$stmt = $pdo->prepare("SELECT user_id FROM diagnose WHERE file_name = ?");
$stmt->execute([$file]);
$diagnose = $stmt->fetch();
if ($diagnose && $diagnose['user_id'] == $userId) {
    $allowed = true;
}

// Проверяем через таблицу analyzes
if (!$allowed) {
    $stmt = $pdo->prepare("SELECT user_id FROM analyzes WHERE file_name = ?");
    $stmt->execute([$file]);
    $analysis = $stmt->fetch();
    if ($analysis && $analysis['user_id'] == $userId) {
        $allowed = true;
    }
}

// Если всё равно не разрешено — проверяем роли (админ, врач, персонал)
if (!$allowed && (isAdmin() || isDoctor() || isStuff())) {
    $allowed = true;
}

if (!$allowed) {
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