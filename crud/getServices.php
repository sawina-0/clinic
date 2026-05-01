<?php
session_start();
require_once '../config.php';

if (!isLogged() || !isAdmin()) {
    http_response_code(403);
    exit;
}

$direction_filter = isset($_GET['direction']) ? (int)$_GET['direction'] : 0;
$search = isset($_GET['search']) ? $_GET['search'] : '';

$sql = "SELECT s.service_id, s.name, s.price, s.is_public, d.direction_id, d.name as direction_name
        FROM services s
        JOIN directions d ON s.direction_id = d.direction_id
        WHERE 1";

$params = [];

if (!empty($direction_filter)) {
    $sql .= " AND s.direction_id = ?";
    $params[] = $direction_filter;
}

if (!empty($search)) {
    $sql .= " AND s.name LIKE ?";
    $params[] = "%$search%";
}

$sql .= " ORDER BY d.name, s.name";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($services as $service):
    $publicText = $service['is_public'] ? 'доступна' : 'по назначению';
    ?>
    <div class="serviceCard card" data-id="<?= $service['service_id'] ?>">
        <div class="info">
            <p><?= htmlspecialchars($service['name']) ?></p>
            <p>(<?= htmlspecialchars($service['direction_name']) ?>)</p>
            <p><?= number_format($service['price'], 0, '', ' ') ?> ₽</p>
            <p><small><?= $publicText ?></small></p>
        </div>
        <div class="btns">
            <button class="editBtn" data-id="<?= $service['service_id'] ?>"><img src="../img/svg/pencil.svg" alt="редактировать"></button>
            <button class="deleteBtn" data-id="<?= $service['service_id'] ?>"><img src="../img/svg/trash.svg" alt="удалить"></button>
        </div>
    </div>
<?php endforeach; ?>