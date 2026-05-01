<?php
session_start();
require_once '../config.php';

if (!isLogged() || !isAdmin()) {
    http_response_code(403);
    exit;
}

$role_filter = isset($_GET['role']) ? $_GET['role'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

$sql = "SELECT user_id, surname, name, sec_name, phone_num, photo, role FROM users WHERE 1";

$params = [];

if (!empty($role_filter)) {
    $sql .= " AND role = ?";
    $params[] = $role_filter;
}

if (!empty($search)) {
    $sql .= " AND (surname LIKE ? OR name LIKE ? OR sec_name LIKE ? OR phone_num LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$sql .= " ORDER BY surname, name";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as $user):
    $photoPath = $user['photo'] ? '../img/avatars/' . $user['photo'] : '../img/avatars/none.svg';
    
    // Форматируем телефон
    $phone = $user['phone_num'];
    $formattedPhone = '8(' . substr($phone, 1, 3) . ')' . substr($phone, 4, 3) . '-' . substr($phone, 7, 2) . '-' . substr($phone, 9, 2);
    
    
    ?>
    <div class="userCard card" data-id="<?= $user['user_id'] ?>">
        <img src="<?= htmlspecialchars($photoPath) ?>" alt="">
        <div class="info">
            <p><?= htmlspecialchars($user['surname'] . ' ' . $user['name'] . ' ' . $user['sec_name']) ?></p>
            <p><?= $formattedPhone ?></p>
            <p><?= htmlspecialchars($user['role']) ?></p>
        </div>
        <div class="btns">
            <button class="editBtn" data-id="<?= $user['user_id'] ?>"><img src="../img/svg/pencil.svg" alt="редактировать"></button>
            <button class="deleteBtn" data-id="<?= $user['user_id'] ?>"><img src="../img/svg/trash.svg" alt="удалить"></button>
        </div>
    </div>
<?php endforeach; ?>