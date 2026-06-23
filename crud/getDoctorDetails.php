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
            d.doctor_id,
            u.surname,
            u.name,
            u.sec_name,
            u.photo,
            dir.name as direction_name,
            dd.education,
            dd.qualification,
            dd.awards
        FROM doctors d
        JOIN users u ON d.user_id = u.user_id
        JOIN directions dir ON d.direction_id = dir.direction_id
        LEFT JOIN doctor_details dd ON d.doctor_id = dd.doctor_id
        WHERE u.role IN ('Доктор', 'Персонал')";

$params = [];

if (!empty($direction_filter)) {
    $sql .= " AND d.direction_id = ?";
    $params[] = $direction_filter;
}
if (!empty($search)) {
    $sql .= " AND (u.surname LIKE ? OR u.name LIKE ? OR u.sec_name LIKE ? OR dir.name LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$sql .= " ORDER BY u.surname, u.name";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($doctors as $doc):
    $fullName = $doc['surname'] . ' ' . $doc['name'] . ' ' . $doc['sec_name'];
    $photoPath = $doc['photo'] ? '../img/avatars/' . $doc['photo'] : '../img/avatars/none.svg';
    $hasDetails = !empty($doc['education']) || !empty($doc['qualification']) || !empty($doc['awards']);
    
    // Обрезаем текст для карточки
    $education = $doc['education'] ? mb_substr($doc['education'], 0, 60) . (mb_strlen($doc['education']) > 60 ? '...' : '') : 'Не указано';
    $qualification = $doc['qualification'] ? mb_substr($doc['qualification'], 0, 60) . (mb_strlen($doc['qualification']) > 60 ? '...' : '') : 'Не указано';
    $awards = $doc['awards'] ? mb_substr($doc['awards'], 0, 60) . (mb_strlen($doc['awards']) > 60 ? '...' : '') : 'Не указано';
?>
    <div class="doctorDetailCard card" data-id="<?= $doc['doctor_id'] ?>">
        <img src="<?= htmlspecialchars($photoPath) ?>" alt="">
        <div class="info">
            <p><?= htmlspecialchars($fullName) ?></p>
            <p><?= htmlspecialchars($doc['direction_name']) ?></p>
            <div class="details-preview">
                <p>Образование:<?= ' ' . htmlspecialchars($education) ?></p>
                <p>Квалификация:<?= ' ' . htmlspecialchars($qualification) ?></p>
                <p>Награды:<?= ' ' . htmlspecialchars($awards) ?></p>
            </div>
        </div>
        <div class="btns">
            <?php if (!$hasDetails): ?>
                <button class="addDetailBtn" data-id="<?= $doc['doctor_id'] ?>">+</button>
            <?php else: ?>
                <div class="btns">
                    <button class="editBtn" data-id="<?= $doc['doctor_id'] ?>"><img src="../img/svg/pencil.svg" alt="редактировать"></button>
                    <button class="deleteBtn" data-id="<?= $doc['doctor_id'] ?>"><img src="../img/svg/trash.svg" alt="удалить"></button>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>