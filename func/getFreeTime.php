<?php
session_start();
require_once '../config.php';

$doctor_id = isset($_GET['doctor_id']) ? (int)$_GET['doctor_id'] : 0;
$date = isset($_GET['date']) ? $_GET['date'] : '';

$date = DateTime::createFromFormat('d.m.Y', $date);
if (!$date) {
    echo '<p>Ошибка формата даты</p>';
    exit;
}
$mysqlDate = $date->format('Y-m-d');

if (!$doctor_id || !$mysqlDate) {
    echo '<p>Ошибка: нет данных</p>';
    exit;
}

// Получаем день недели для выбранной даты
$dayOfWeek = $date->format('N'); // 1 = пн, 7 = вс

// Получаем график врача на этот день
$stmt = $pdo->prepare("
    SELECT s.start_time, s.end_time 
    FROM doctor_schedule ds
    JOIN schedule s ON ds.schedule_id = s.schedule_id
    WHERE ds.doctor_id = ? AND s.day_of_week = ?
");
$stmt->execute([$doctor_id, $dayOfWeek]);
$schedule = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$schedule) {
    echo '<p>Врач не работает в этот день</p>';
    exit;
}

// Генерируем слоты по 30 минут
$start = new DateTime($schedule['start_time']);
$end = new DateTime($schedule['end_time']);
$interval = new DateInterval('PT30M');
$slots = [];

// Получаем занятые слоты
$stmt = $pdo->prepare("
    SELECT app_datetime 
    FROM appointments 
    WHERE doctor_id = ? AND DATE(app_datetime) = ? AND status != 'отменён'
");
$stmt->execute([$doctor_id, $mysqlDate]);
$busySlots = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Получаем записи текущего пользователя на выбранную дату
$stmt = $pdo->prepare("
    SELECT app_datetime 
    FROM appointments 
    WHERE user_id = ? AND DATE(app_datetime) = ? AND status != 'отменён'
");
$stmt->execute([$_SESSION['user_id'], $mysqlDate]);
$userSlots = $stmt->fetchAll(PDO::FETCH_COLUMN);

$userTimes = [];
foreach ($userSlots as $slot) {
    $userTimes[] = date('H:i', strtotime($slot));
}

// Форматируем занятые слоты для сравнения
$busyTimes = [];
foreach ($busySlots as $slot) {
    $busyTimes[] = date('H:i', strtotime($slot));
}

// Генерируем кнопки
while ($start < $end) {
    $time = $start->format('H:i');
    
    // Если слот свободен И у пользователя нет записи на это время
    if (!in_array($time, $busyTimes) && !in_array($time, $userTimes)) {
        echo "<button class='time-slot' data-time='$time'>$time</button>";
    }
    
    $start->add($interval);
}
?>