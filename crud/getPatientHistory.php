<?php
session_start();
require_once '../config.php';

if (!isLogged() || !isDoctor()) {
    http_response_code(403);
    exit;
}

$patient_id = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : 0;
$stmt = $pdo->prepare("SELECT doctor_id FROM doctors WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$doctor_id = $stmt->fetchColumn();

if (!$doctor_id) {
    echo '<p class="nothingFound">Врач не найден</p>';
    exit;
}

if (!$patient_id) {
    echo '<p class="nothingFound">Неверный запрос</p>';
    exit;
}

// Проверяем, есть ли у врача запись с этим пациентом
$stmt = $pdo->prepare("SELECT appointment_id FROM appointments WHERE doctor_id = ? AND user_id = ?");
$stmt->execute([$doctor_id, $patient_id]);
if (!$stmt->fetch()) {
    echo '<p class="nothingFound">У вас нет доступа к истории этого пациента</p>';
    exit;
}

$type = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$search = isset($_GET['search']) ? $_GET['search'] : '';

$history = [];

// Диагнозы
if ($type === 'all' || $type === 'diagnose') {
    $sql = "SELECT 'diagnose' as type, d.date, d.diagnose_text as text,
                   CONCAT(u.surname, ' ', u.name, ' ', u.sec_name) as doctor_name,
                   d.file_name, d.diagnose_id as id
            FROM diagnose d
            JOIN doctors doc ON d.doctor_id = doc.doctor_id
            JOIN users u ON doc.user_id = u.user_id
            WHERE d.user_id = ?";
    $params = [$patient_id];
    if (!empty($search)) {
        $sql .= " AND (d.diagnose_text LIKE ? OR u.surname LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $diagnoses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $history = array_merge($history, $diagnoses);
}

// Анализы
if ($type === 'all' || $type === 'analysis') {
    $sql = "SELECT 'analysis' as type, a.date, s.name as text,
                   CONCAT(u.surname, ' ', u.name, ' ', u.sec_name) as doctor_name,
                   a.file_name, a.analysis_id as id
            FROM analyzes a
            JOIN services s ON a.service_id = s.service_id
            JOIN doctors doc ON a.doctor_id = doc.doctor_id
            JOIN users u ON doc.user_id = u.user_id
            WHERE a.user_id = ?";
    $params = [$patient_id];
    if (!empty($search)) {
        $sql .= " AND (s.name LIKE ? OR u.surname LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $analyzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $history = array_merge($history, $analyzes);
}

usort($history, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

if (empty($history)) {
    echo '<p class="nothingFound">У пациента нет диагнозов или анализов</p>';
    exit;
}

foreach ($history as $item):
    $typeText = $item['type'] === 'diagnose' ? 'Диагноз' : 'Анализ';
?>
    <div class="ecCard card" data-type="<?= $item['type'] ?>">
        <div class="head">
            <p class="ec-date"><?= date('d.m.Y', strtotime($item['date'])) ?></p>
            <p class="ec-type"><?= $typeText ?></p>
        </div>
        <p class="ec-text"><?= htmlspecialchars($item['text']) ?></p>
        <p class="ec-doctor">Специалист: <?= htmlspecialchars($item['doctor_name']) ?></p>
        <?php if ($item['file_name']): ?>
            <div class="ec-file">
                <a href="../func/download.php?file=<?= urlencode($item['file_name']) ?>" target="_blank">Смотреть файл</a>
            </div>
        <?php endif; ?>
    </div>
<?php endforeach; ?>