<?php
session_start();
require_once '../config.php';

$doctor_id = isset($_GET['doctor_id']) ? (int)$_GET['doctor_id'] : 0;

if (!$doctor_id) {
    echo '<p class="nothingFound">Врач не выбран</p>';
    exit;
}

// Получаем направление врача
$stmt = $pdo->prepare("SELECT direction_id FROM doctors WHERE doctor_id = ?");
$stmt->execute([$doctor_id]);
$direction = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$direction) {
    echo '<p class="nothingFound">Врач не найден</p>';
    exit;
}

// Получаем публичные услуги этого направления
$stmt = $pdo->prepare("SELECT service_id, name, price 
                       FROM services 
                       WHERE direction_id = ? AND is_public = 1 
                       ORDER BY name");
$stmt->execute([$direction['direction_id']]);
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($services)) {
    echo '<p class="nothingFound">У этого врача нет доступных услуг для записи</p>';
    exit;
}

// Выводим услуги
foreach ($services as $service): ?>
    <div class="serviceOption" 
         data-service-id="<?= $service['service_id'] ?>"
         data-service-name="<?= htmlspecialchars($service['name']) ?>"
         data-service-price="<?= $service['price'] ?>">
        <p><?= htmlspecialchars($service['name']) ?></p>
        <span><?= $service['price'] ?> ₽</span>
    </div>
<?php endforeach; ?>