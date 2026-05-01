<?php
session_start();
require_once '../config.php';

$service_id = isset($_GET['service_id']) ? (int)$_GET['service_id'] : 0;

if (!$service_id) {
    echo '<p class="nothingFound">Услуга не выбрана</p>';
    exit;
}

// Получаем направление услуги
$stmt = $pdo->prepare("SELECT direction_id FROM services WHERE service_id = ?");
$stmt->execute([$service_id]);
$direction_id = $stmt->fetchColumn();

if (!$direction_id) {
    echo '<p class="nothingFound">Направление не найдено</p>';
    exit;
}

// Получаем врачей этого направления
$stmt = $pdo->prepare("
    SELECT 
        d.doctor_id,
        d.exp,
        u.photo,
        CONCAT(u.surname, ' ', u.name, ' ', u.sec_name) AS full_name
    FROM doctors d
    JOIN users u ON d.user_id = u.user_id
    WHERE d.direction_id = ?
");
$stmt->execute([$direction_id]);
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($doctors)) {
    echo '<p class="nothingFound">Нет врачей по этому направлению</p>';
    exit;
}

foreach ($doctors as $doctor): ?>
    <div class="serviceOption" 
         data-doctor-id="<?= $doctor['doctor_id'] ?>"
         data-doctor-name="<?= htmlspecialchars($doctor['full_name']) ?>"
         data-doctor-photo="<?= htmlspecialchars($doctor['photo']) ?>"
         data-doctor-exp="<?= $doctor['exp'] ?>">
        <p><?= htmlspecialchars($doctor['full_name']) ?></p>
        <span>Стаж: <?= $doctor['exp'] ?> лет</span>
    </div>
<?php endforeach; ?>