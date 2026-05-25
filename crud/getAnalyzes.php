<?php
session_start();
require_once '../config.php';

if (!isLogged() || !isStuff()) {
    http_response_code(403);
    exit;
}

$search = isset($_GET['search']) ? $_GET['search'] : '';

$sql = "SELECT a.analysis_id, a.file_name, a.date,
               CONCAT(u.surname, ' ', u.name, ' ', u.sec_name) as patient_name,
               s.name as service_name,
               CONCAT(du.surname, ' ', du.name, ' ', du.sec_name) as doctor_name
        FROM analyzes a
        JOIN users u ON a.user_id = u.user_id
        JOIN services s ON a.service_id = s.service_id
        JOIN doctors d ON a.doctor_id = d.doctor_id
        JOIN users du ON d.user_id = du.user_id
        WHERE 1";

$params = [];

if (!empty($search)) {
    $sql .= " AND (u.surname LIKE ? OR u.name LIKE ? OR s.name LIKE ? OR a.file_name LIKE ?)";
    $searchTerm = "%$search%";
    for ($i = 0; $i < 4; $i++) $params[] = $searchTerm;
}

$sql .= " ORDER BY a.date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$analyzes = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($analyzes as $item):
    $date = new DateTime($item['date']);
?>
    <div class="analysisCard card" data-id="<?= $item['analysis_id'] ?>">
        <div class="info">
            <p>Пациент: <?= htmlspecialchars($item['patient_name']) ?></p>
            <p>Услуга: <?= htmlspecialchars($item['service_name']) ?></p>
            <p>Врач: <?= htmlspecialchars($item['doctor_name']) ?></p>
            <p>Дата: <?= $date->format('d.m.Y') ?></p>
            <p><a href="../func/download.php?file=<?= urlencode($item['file_name']) ?>" target="_blank"><?= htmlspecialchars($item['file_name']) ?></a></p>
        </div>
        <div class="btns">
            <button class="editBtn" data-id="<?= $item['analysis_id'] ?>"><img src="../img/svg/pencil.svg" alt="редактировать"></button>
            <button class="deleteBtn" data-id="<?= $item['analysis_id'] ?>"><img src="../img/svg/trash.svg" alt="удалить"></button>
        </div>
    </div>
<?php endforeach; ?>