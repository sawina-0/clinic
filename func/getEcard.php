<?php
session_start();
require_once '../config.php';

if (!isLogged()) {
    http_response_code(403);
    exit;
}

$userId = $_SESSION['user_id'];
$type = isset($_GET['direction']) ? $_GET['direction'] : 'all';
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
    $params = [$userId];
    
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
    $params = [$userId];
    
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

// Сортируем по дате (новые первые)
usort($history, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

foreach ($history as $item):
    $typeText = $item['type'] === 'diagnose' ? 'Диагноз' : 'Анализ';
?>
    <div class="ecCard card" data-type="<?= $item['type'] ?>" data-id="<?= $item['id'] ?>">
        <div class="head">
            <p class="ec-date"><?= date('d.m.Y', strtotime($item['date'])) ?></p>
            <p class="ec-type"><?= $typeText ?></p>
        </div>
        <p class="ec-text"><?= htmlspecialchars($item['text']) ?></p>
        <p class="ec-doctor">Врач: <?= htmlspecialchars($item['doctor_name']) ?></p>
        <?php if ($item['file_name']): ?>
            <div class="file-link">
                <a href="../func/download.php?file=<?= urlencode($item['file_name']) ?>" target="_blank">Смотреть файл</a>
            </div>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

<?php if (empty($history)): ?>
    <p class="nothingFound">Ничего не найдено</p>
<?php endif; ?>