<?php
session_start();
require_once '../config.php';

if (!isLogged()) {
    http_response_code(403);
    exit;
}

$userId = $_SESSION['user_id'];
$search = isset($_GET['search']) ? $_GET['search'] : '';

$sql = "SELECT 
            a.appointment_id,
            a.app_datetime,
            a.status,
            s.name as service_name,
            c.number as cabinet_number,
            CONCAT(u.surname, ' ', LEFT(u.name, 1), '.', LEFT(u.sec_name, 1), '.') as doctor_short,
            dir.specialist_name
        FROM appointments a
        JOIN services s ON a.service_id = s.service_id
        JOIN cabinets c ON a.cabinet_id = c.cabinet_id
        JOIN doctors d ON a.doctor_id = d.doctor_id
        JOIN users u ON d.user_id = u.user_id
        JOIN directions dir ON d.direction_id = dir.direction_id
        WHERE a.user_id = ? AND a.status = 'запланирован' AND a.app_datetime >= NOW()";

$params = [$userId];

if (!empty($search)) {
    $sql .= " AND (DATE_FORMAT(a.app_datetime, '%d.%m.%Y') LIKE ? 
                  OR s.name LIKE ? 
                  OR u.surname LIKE ? 
                  OR u.name LIKE ?)";
    $searchTerm = "%$search%";
    for ($i = 0; $i < 4; $i++) $params[] = $searchTerm;
}

$sql .= " ORDER BY a.app_datetime ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($appointments as $app):
    $date = new DateTime($app['app_datetime']);
?>
    <div class="appCard card" data-id="<?= $app['appointment_id'] ?>">
        <div class="info">
            <div class="dateTime">
                <span><?= $date->format('d.m.Y') ?></span>
                <span><?= $date->format('H:i') ?></span>
            </div>
            <div class="who">
                <p><?= htmlspecialchars($app['doctor_short']) ?></p>
                <span>(<?= htmlspecialchars($app['specialist_name']) ?>)</span>
            </div>
            <p><?= htmlspecialchars($app['service_name']) ?></p>
            <p>кабинет <?= (int)$app['cabinet_number'] ?></p>
        </div>
        <div class="btns">
            <button class="reschedule" data-id="<?= (int)$app['appointment_id'] ?>">перенести</button>
            <button class="cancel" data-id="<?= $app['appointment_id'] ?>">отменить</button>
        </div>
    </div>
<?php endforeach; ?>