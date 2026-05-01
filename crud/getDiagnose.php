<?php
session_start();
require_once '../config.php';

if (!isLogged()) {
    http_response_code(403);
    exit;
}

$search = isset($_GET['search']) ? $_GET['search'] : '';

$sql = "SELECT 
            diag.diagnose_id,
            diag.date,
            diag.diagnose_text,
            CONCAT(du.surname, ' ', LEFT(du.name, 1), '.', LEFT(du.sec_name, 1), '.') as doctor_short,
            CONCAT(pu.surname, ' ', LEFT(pu.name, 1), '.', LEFT(pu.sec_name, 1), '.') as patient_short
        FROM diagnose diag
        JOIN doctors d ON diag.doctor_id = d.doctor_id
        JOIN users du ON d.user_id = du.user_id
        JOIN users pu ON diag.user_id = pu.user_id
        WHERE 1";

$params = [];

// Ограничение по роли
if (isAdmin()) {
    // админ видит всё
} elseif (isDoctor()) {
    // врач видит только свои диагнозы
    $sql .= " AND d.doctor_id = (SELECT doctor_id FROM doctors WHERE user_id = ?)";
    $params[] = $_SESSION['user_id'];
} elseif (isStuff()) {
    // персонал не ставит диагнозы, поэтому ничего не видит
    $sql .= " AND 1=0"; // пустой результат
}

if (!empty($search)) {
    $sql .= " AND (diag.date LIKE ? OR diag.diagnose_text LIKE ? OR du.surname LIKE ? OR pu.surname LIKE ?)";
    $searchTerm = "%$search%";
    for ($i = 0; $i < 4; $i++) $params[] = $searchTerm;
}

$sql .= " ORDER BY diag.date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$diagnoses = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($diagnoses as $diag):
    $date = new DateTime($diag['date']);
?>
    <div class="diagnoseCard card" data-id="<?= $diag['diagnose_id'] ?>">
        <div class="info">
            <p><?= $date->format('d.m.Y') ?></p>
            <p>Пациент: <?= htmlspecialchars($diag['patient_short']) ?></p>
            <p>Врач: <?= htmlspecialchars($diag['doctor_short']) ?></p>
            <p>Диагноз: <?= htmlspecialchars($diag['diagnose_text']) ?></p>
        </div>
        <div class="btns">
            <button class="editBtn" data-id="<?= $diag['diagnose_id'] ?>"><img src="../img/svg/pencil.svg" alt="редактировать"></button>
            <button class="deleteBtn" data-id="<?= $diag['diagnose_id'] ?>"><img src="../img/svg/trash.svg" alt="удалить"></button>
        </div>
    </div>
<?php endforeach; ?>