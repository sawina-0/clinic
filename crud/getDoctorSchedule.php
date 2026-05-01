<?php
session_start();
require_once '../config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isLogged() || !isAdmin()) {
    http_response_code(403);
    exit;
}

$direction_filter = isset($_GET['direction']) ? (int)$_GET['direction'] : 0;
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Сначала получаем всех врачей
$sql = "SELECT 
            d.doctor_id,
            d.exp,
            u.user_id,
            u.surname,
            u.name,
            u.sec_name,
            u.photo,
            dir.name as direction_name,
            dir.direction_id,
            dir.specialist_name
        FROM doctors d
        JOIN users u ON d.user_id = u.user_id
        JOIN directions dir ON d.direction_id = dir.direction_id
        WHERE u.role IN ('Доктор', 'Персонал')";

$params = [];

if (!empty($direction_filter)) {
    $sql .= " AND dir.direction_id = ?";
    $params[] = $direction_filter;
}

if (!empty($search)) {
    $sql .= " AND (u.surname LIKE ? OR u.name LIKE ? OR u.sec_name LIKE ?)";
    $searchTerm = "%$search%";
    for ($i = 0; $i < 3; $i++) $params[] = $searchTerm;
}

$sql .= " ORDER BY u.surname, u.name";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Массив для перевода дней
$daysMap = [
    1 => 'пн',
    2 => 'вт',
    3 => 'ср',
    4 => 'чт',
    5 => 'пт',
    6 => 'сб'
];

foreach ($doctors as $doc):
    $photoPath = $doc['photo'] ? '../img/avatars/' . $doc['photo'] : '../img/avatars/none.svg';
    $fullName = $doc['surname'] . ' ' . $doc['name'] . ' ' . $doc['sec_name'];
    
    // Получаем график врача
    $scheduleStmt = $pdo->prepare("
        SELECT s.day_of_week 
        FROM doctor_schedule ds
        JOIN schedule s ON ds.schedule_id = s.schedule_id
        WHERE ds.doctor_id = ?
    ");
    $scheduleStmt->execute([$doc['doctor_id']]);
    $scheduleDays = $scheduleStmt->fetchAll(PDO::FETCH_COLUMN);
    // Формируем строку с графиком
    $scheduleText = 'График: ';
    if (empty($scheduleDays)) {
        $scheduleText .= 'не выставлен';
        $hasSchedule = false;
    } else {
        $dayNames = [];
        foreach ($scheduleDays as $day) {
            $dayNames[] = $daysMap[$day] ?? '';
        }
        $scheduleText .= implode(', ', $dayNames);
        $hasSchedule = true;
    }
?>
    <div class="scheduleCard card" data-id="<?= $doc['doctor_id'] ?>">
        <img src="<?= htmlspecialchars($photoPath) ?>" alt="">
        <div class="info">
            <p><?= htmlspecialchars($fullName) ?></p>
            <p><?= htmlspecialchars($doc['specialist_name']) ?></p>
            <p><?= $scheduleText ?></p>
        </div>
        <div class="btns">
            <?php if (!$hasSchedule): ?>
                <button class="addBtn" data-id="<?= $doc['doctor_id'] ?>">+</button>
            <?php else: ?>
                <button class="editBtn" data-id="<?= $doc['doctor_id'] ?>"><img src="../img/svg/pencil.svg" alt="редактировать"></button>
                <button class="deleteBtn" data-id="<?= $doc['doctor_id'] ?>"><img src="../img/svg/trash.svg" alt="удалить"></button>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>