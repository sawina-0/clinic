<?php
session_start();
require_once '../config.php';

$direction = isset($_GET['direction']) ? $_GET['direction'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Базовый запрос
$sql = "SELECT s.*, d.name as direction_name 
        FROM services s 
        LEFT JOIN directions d ON s.direction_id = d.direction_id";

// Собираем условия
$conditions = [];

if (!empty($direction)) {
    $conditions[] = "s.direction_id = " . intval($direction);
}

if (!empty($search)) {
    $search = $pdo->quote('%' . $search . '%');
    $conditions[] = "s.name LIKE " . $search;
}

// Если есть условия, добавляем WHERE
if (!empty($conditions)) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}

$sql .= " ORDER BY d.name, s.price";
$stmt = $pdo->query($sql);
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Группируем
$groupedServices = [];
foreach ($services as $service) {
    $directionName = $service['direction_name'] ?: 'Без направления';
    if (!isset($groupedServices[$directionName])) {
        $groupedServices[$directionName] = [];
    }
    $groupedServices[$directionName][] = $service;
}

// Выводим
foreach ($groupedServices as $directionName => $services): ?>
    <div class="directionCard">
        <h2><?= htmlspecialchars($directionName) ?></h2>
        <div class="services">
            <?php foreach ($services as $row): ?>
                <div class="service">
                    <p><?= htmlspecialchars($row['name']) ?></p>
                    <span><?= htmlspecialchars($row['price']) ?> ₽</span>
                    <?php if ($row['is_public'] == 1): ?>
                        <button type="button" 
                                class="commonBtn"
                                data-service-id="<?= $row['service_id'] ?>"
                                data-service-name="<?= htmlspecialchars($row['name']) ?>"
                                data-service-price="<?= $row['price'] ?>"
                                data-type="service">
                            записаться
                        </button>
                    <?php else: ?>
                        <button type="button" class="nonVisible commonBtn">записаться</button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>

<?php if (empty($services)): ?>
    <p class="nothingFound">Ничего не найдено</p>
<?php endif; ?>