<?php
session_start();
require_once '../config.php';

if (!isLogged()) {
    http_response_code(403);
    exit;
}

$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

$sql = "SELECT 
            a.appointment_id,
            a.app_datetime,
            a.status,
            s.name as service_name,
            c.number as cabinet_number,
            CONCAT(du.surname, ' ', LEFT(du.name, 1), '.', LEFT(du.sec_name, 1), '.') as doctor_short,
            CONCAT(pu.surname, ' ', LEFT(pu.name, 1), '.', LEFT(pu.sec_name, 1), '.') as patient_short,
            dir.direction_id
        FROM appointments a
        JOIN services s ON a.service_id = s.service_id
        JOIN cabinets c ON a.cabinet_id = c.cabinet_id
        JOIN doctors d ON a.doctor_id = d.doctor_id
        JOIN users du ON d.user_id = du.user_id
        JOIN users pu ON a.user_id = pu.user_id
        JOIN directions dir ON d.direction_id = dir.direction_id
        WHERE 1";

$params = [];

// Ограничение по роли
if (isAdmin()) {
    // админ видит всё
} elseif (isDoctor() || isStuff()) {
    // врач и персонал видят только свои записи
    $sql .= " AND a.doctor_id = (SELECT doctor_id FROM doctors WHERE user_id = ?)";
    $params[] = $_SESSION['user_id'];
}

if (!empty($status_filter)) {
    $sql .= " AND a.status = ?";
    $params[] = $status_filter;
}

if (!empty($search)) {
    $sql .= " AND (a.app_datetime LIKE ? OR a.status LIKE ? OR s.name LIKE ? OR c.number LIKE ? OR du.surname LIKE ? OR pu.surname LIKE ?)";
    $searchTerm = "%$search%";
    for ($i = 0; $i < 6; $i++) $params[] = $searchTerm;
}

$sql .= " ORDER BY a.app_datetime DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$apps = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($apps as $app):
    $date = new DateTime($app['app_datetime']);
?>
    <div class="appCard card" data-id="<?= $app['appointment_id'] ?>">
        <div class="info">
            <div class="dateTime">
                <p><?= $date->format('d.m.Y') ?></p>
                <p><?= $date->format('H:i') ?></p>
            </div>
            <p>Пациент: <?= htmlspecialchars($app['patient_short']) ?></p>
            <p>Врач: <?= htmlspecialchars($app['doctor_short']) ?></p>
            <p>Услуга: <?= htmlspecialchars($app['service_name']) ?></p>
            <p>Кабинет № <?= (int)$app['cabinet_number'] ?></p>
            <p>Статус: <?= htmlspecialchars($app['status']) ?></p>
        </div>
        <div class="btns">
            <button class="editBtn" data-id="<?= $app['appointment_id'] ?>"><img src="../img/svg/pencil.svg" alt="редактировать"></button>
            <button class="deleteBtn" data-id="<?= $app['appointment_id'] ?>"><img src="../img/svg/trash.svg" alt="удалить"></button>
        </div>
    </div>
<?php endforeach; ?>