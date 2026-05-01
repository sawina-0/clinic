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
            d.exp,
            u.user_id,
            u.surname,
            u.name,
            u.sec_name,
            u.phone_num,
            u.photo,
            u.role,
            dir.name as direction_name,
            dir.direction_id,
            dir.specialist_name,
            c.number as cabinet_number,
            c.floor
        FROM doctors d
        JOIN users u ON d.user_id = u.user_id
        JOIN directions dir ON d.direction_id = dir.direction_id
        LEFT JOIN cabinets c ON d.cabinet_id = c.cabinet_id
        WHERE u.role IN ('Доктор', 'Персонал')";

$params = [];

if (!empty($direction_filter)) {
    $sql .= " AND dir.direction_id = ?";
    $params[] = $direction_filter;
}

if (!empty($search)) {
    $sql .= " AND (u.surname LIKE ? OR u.name LIKE ? OR u.sec_name LIKE ? OR u.phone_num LIKE ? OR dir.specialist_name LIKE ?)";
    $searchTerm = "%$search%";
    for ($i = 0; $i < 5; $i++) $params[] = $searchTerm;
}

$sql .= " ORDER BY u.surname, u.name";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($doctors as $doc):
    $photoPath = $doc['photo'] ? '../img/avatars/' . $doc['photo'] : '../img/avatars/none.svg';
    
    // Форматируем телефон
    $phone = $doc['phone_num'];
    $formattedPhone = '8(' . substr($phone, 1, 3) . ')' . substr($phone, 4, 3) . '-' . substr($phone, 7, 2) . '-' . substr($phone, 9, 2);
    
    $fullName = $doc['surname'] . ' ' . $doc['name'] . ' ' . $doc['sec_name'];
    $cabinetText = $doc['cabinet_number'] ? 'каб. ' . $doc['cabinet_number'] : 'кабинет не назначен';
?>
    <div class="doctorCard card" data-id="<?= $doc['doctor_id'] ?>">
        <img src="<?= htmlspecialchars($photoPath) ?>" alt="">
        <div class="info">
            <p><?= htmlspecialchars($fullName) ?></p>
            <p><?= htmlspecialchars($doc['specialist_name']) ?></p>
            <p>Стаж: <?= (int)$doc['exp'] ?> лет</p>
            <p><?= $formattedPhone ?></p>
            <p><small><?= $cabinetText ?></small></p>
        </div>
        <div class="btns">
            <button class="editBtn" data-id="<?= $doc['doctor_id'] ?>"><img src="../img/svg/pencil.svg" alt="редактировать"></button>
            <button class="deleteBtn" data-id="<?= $doc['doctor_id'] ?>"><img src="../img/svg/trash.svg" alt="удалить"></button>
        </div>
    </div>
<?php endforeach; ?>