<?php
session_start();
require_once '../config.php';

if (!isLogged() || !isAdmin()) {
    http_response_code(403);
    exit;
}

$direction_filter = isset($_GET['direction']) ? (int)$_GET['direction'] : 0;
$search = isset($_GET['search']) ? $_GET['search'] : '';

$sql = "SELECT 
            c.cabinet_id,
            c.floor,
            c.number,
            d.doctor_id,
            CONCAT(u.surname, ' ', LEFT(u.name, 1), '.', LEFT(u.sec_name, 1), '.') as doctor_short,
            dir.name as direction_name,
            dir.direction_id
        FROM cabinets c
        LEFT JOIN doctors d ON c.cabinet_id = d.cabinet_id
        LEFT JOIN users u ON d.user_id = u.user_id
        LEFT JOIN directions dir ON d.direction_id = dir.direction_id
        WHERE 1";

$params = [];

if (!empty($direction_filter)) {
    $sql .= " AND dir.direction_id = ?";
    $params[] = $direction_filter;
}

if (!empty($search)) {
    $sql .= " AND (c.floor LIKE ? OR c.number LIKE ? OR u.surname LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$sql .= " ORDER BY c.floor, c.number";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cabinets = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($cabinets as $cab):
    $doctorText = $cab['doctor_short'] ? htmlspecialchars($cab['doctor_short']) : '—';
    $directionText = $cab['direction_name'] ? htmlspecialchars($cab['direction_name']) : '—';
?>
    <div class="cabinetCard card" data-id="<?= $cab['cabinet_id'] ?>">
        <div class="info">
            <p>Этаж: <?= (int)$cab['floor'] ?></p>
            <p>Кабинет № <?= htmlspecialchars($cab['number']) ?></p>
            <p><?= $doctorText ?> <small>( <?= $directionText ?> )</small></p>
        </div>
        <div class="btns">
            <button class="editBtn" data-id="<?= $cab['cabinet_id'] ?>"><img src="../img/svg/pencil.svg" alt="редактировать"></button>
            <button class="deleteBtn" data-id="<?= $cab['cabinet_id'] ?>"><img src="../img/svg/trash.svg" alt="удалить"></button>
        </div>
    </div>
<?php endforeach; ?>