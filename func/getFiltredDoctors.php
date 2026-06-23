<?php
session_start();
require_once '../config.php';

$direction = isset($_GET['direction']) ? $_GET['direction'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Базовый запрос
$sql = "SELECT
            d.doctor_id AS doctor_id,
            d.direction_id,
            d.exp,
            u.photo,
            CONCAT(
                u.surname, ' ',
                u.name, ' ',
                u.sec_name
            ) AS full_name,
            dir.specialist_name
        FROM doctors d
        JOIN users u ON d.user_id = u.user_id
        JOIN directions dir ON d.direction_id = dir.direction_id
        WHERE u.role IN ('Доктор', 'Персонал')";

// Собираем дополнительные условия
$conditions = [];

if (!empty($direction)) {
    $conditions[] = "d.direction_id = " . intval($direction);
}

if (!empty($search)) {
    $searchTerm = $pdo->quote('%' . $search . '%');
    $conditions[] = "(u.surname LIKE $searchTerm 
                      OR u.name LIKE $searchTerm 
                      OR u.sec_name LIKE $searchTerm
                      OR dir.specialist_name LIKE $searchTerm
                      OR d.exp LIKE $searchTerm)";
}

// Добавляем условия, если есть
if (!empty($conditions)) {
    $sql .= " AND " . implode(' AND ', $conditions);
}

$sql .= " ORDER BY u.surname, u.name";
$stmt = $pdo->query($sql);
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Выводим карточки
if (!empty($doctors)) {
    foreach ($doctors as $row):
        $photoPath = $row['photo']
            ? '../img/avatars/' . $row['photo']
            : '../img/avatars/none.svg';
?>
        <div class="doctorCard">
            <a href="./doctor.php?id=<?= $row['doctor_id'] ?>" class="doctor-link">
                <img src="<?= htmlspecialchars($photoPath) ?>" alt="<?= htmlspecialchars($row['full_name']) ?>">
                <div class="txt">
                    <h3><?= htmlspecialchars($row['full_name']) ?></h3>
                    <p><?= htmlspecialchars($row['specialist_name']) ?></p>
                    <p>Стаж: <?= (int)$row['exp'] ?> лет</p>
                </div>
            </a>
            <button type="button"
                class="commonBtn"
                data-doctor-id="<?= $row['doctor_id'] ?>"
                data-doctor-name="<?= htmlspecialchars($row['full_name']) ?>"
                data-doctor-photo="<?= htmlspecialchars($row['photo']) ?>"
                data-type="doctor"
                data-direction-id="<?= $row['direction_id'] ?? '' ?>">
                записаться
            </button>
        </div>
    <?php endforeach;
} else { ?>
    <p class="nothingFound">Ничего не найдено</p>
<?php } ?>