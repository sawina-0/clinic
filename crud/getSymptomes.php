<?php
session_start();
require_once '../config.php';

if (!isLogged() || !isDoctor()) {
    http_response_code(403);
    exit;
}

// Получаем направление текущего врача
$stmt = $pdo->prepare("
    SELECT direction_id 
    FROM doctors 
    WHERE user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$direction_id = $stmt->fetchColumn();

if (!$direction_id) {
    echo '<p class="nothingFound">Направление врача не найдено</p>';
    exit;
}

// Поиск (опционально)
$search = isset($_GET['search']) ? $_GET['search'] : '';

$sql = "SELECT id, keyword, priority FROM symptoms WHERE direction_id = ?";
$params = [$direction_id];

if (!empty($search)) {
    $sql .= " AND keyword LIKE ?";
    $params[] = "%$search%";
}

$sql .= " ORDER BY priority DESC, keyword";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$symptoms = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if (empty($symptoms)): ?>
    <p class="nothingFound">Симптомы не найдены</p>
<?php else: ?>
    <?php foreach ($symptoms as $symp): ?>
        <div class="symptomCard card" data-id="<?= $symp['id'] ?>">
            <div class="info">
                <p><strong><?= htmlspecialchars($symp['keyword']) ?></strong></p>
                <p>Приоритет (вес): <?= (int)$symp['priority'] ?></p>
            </div>
            <div class="btns">
                <button class="editBtn" data-id="<?= $symp['id'] ?>"><img src="../img/svg/pencil.svg" alt="редактировать"></button>
                <button class="deleteBtn" data-id="<?= $symp['id'] ?>"><img src="../img/svg/trash.svg" alt="удалить"></button>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>