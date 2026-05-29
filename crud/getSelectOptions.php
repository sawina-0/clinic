<?php
session_start();
require_once '../config.php';

if (!isLogged()) {
    http_response_code(403);
    exit;
}

$type = $_GET['type'] ?? '';

if ($type === 'directions') {
    $stmt = $pdo->query("SELECT direction_id as id, name FROM directions ORDER BY name");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($data as $item) {
        echo '<div class="filter-option" data-value="' . $item['id'] . '">' . htmlspecialchars($item['name']) . '</div>';
    }
} elseif ($type === 'doctors') {
    $stmt = $pdo->query("
        SELECT d.doctor_id as id, CONCAT(u.surname, ' ', u.name, ' ', u.sec_name) as name
        FROM doctors d
        JOIN users u ON d.user_id = u.user_id
        WHERE u.role IN ('Доктор', 'Персонал')
        ORDER BY u.surname
    ");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($data as $item) {
        echo '<div class="filter-option" data-value="' . $item['id'] . '">' . htmlspecialchars($item['name']) . '</div>';
    }
} elseif ($type === 'patients') {
    $stmt = $pdo->query("
        SELECT user_id as id, CONCAT(surname, ' ', name, ' ', sec_name) as name
        FROM users
        WHERE role = 'Пользователь'
        ORDER BY surname
    ");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($data as $item) {
        echo '<div class="filter-option" data-value="' . $item['id'] . '">' . htmlspecialchars($item['name']) . '</div>';
    }
} elseif ($type === 'services') {
    if (isAdmin()) {
        $stmt = $pdo->query("SELECT service_id as id, name FROM services ORDER BY name");
    } elseif (isDoctor() || isStuff()) {
        // Получаем direction_id текущего пользователя
        $userId = $_SESSION['user_id'];
        $stmtDir = $pdo->prepare("SELECT direction_id FROM doctors WHERE user_id = ?");
        $stmtDir->execute([$userId]);
        $userDir = $stmtDir->fetchColumn();
        
        if (!$userDir) {
            // Если направление не найдено — пустой результат
            $stmt = $pdo->query("SELECT service_id as id, name FROM services WHERE 1=0");
        } else {
            if (isDoctor()) {
                // Врач: своё направление + направления персонала (11,12,13)
                $staffDirections = [11, 12, 13];
                $allowed = array_merge([$userDir], $staffDirections);
                $placeholders = implode(',', array_fill(0, count($allowed), '?'));
                $stmt = $pdo->prepare("SELECT service_id as id, name FROM services WHERE direction_id IN ($placeholders) ORDER BY name");
                $stmt->execute($allowed);
            } else { // isStuff()
                // Персонал: только своё направление
                $stmt = $pdo->prepare("SELECT service_id as id, name FROM services WHERE direction_id = ? ORDER BY name");
                $stmt->execute([$userDir]);
            }
        }
    } else {
        $stmt = $pdo->query("SELECT service_id as id, name FROM services WHERE 1=0");
    }
    
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($services as $service) {
        echo '<div class="filter-option" data-value="' . $service['id'] . '">' . htmlspecialchars($service['name']) . '</div>';
    }
}elseif ($type === 'currentDoctor') {
    //селект врачей для персонала - только себя видит чел
    $userId = $_SESSION['user_id'];
    $stmt = $pdo->prepare("
        SELECT d.doctor_id as id, CONCAT(u.surname, ' ', u.name, ' ', u.sec_name) as name
        FROM doctors d
        JOIN users u ON d.user_id = u.user_id
        WHERE u.user_id = ?
    ");
    $stmt->execute([$userId]);
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($doctor) {
        echo '<div class="filter-option" data-value="' . $doctor['id'] . '" selected>' . htmlspecialchars($doctor['name']) . '</div>';
    }
} elseif ($type === 'doctorsForDoctor') {
    $userId = $_SESSION['user_id'];
    // Врач видит себя + весь персонал
    $stmt = $pdo->prepare("
        SELECT d.doctor_id as id, 
               CONCAT(u.surname, ' ', u.name, ' ', u.sec_name) as name, 
               dir.specialist_name
        FROM doctors d
        JOIN users u ON d.user_id = u.user_id
        JOIN directions dir ON d.direction_id = dir.direction_id
        WHERE u.user_id = ? OR u.role IN ('Персонал')
        ORDER BY u.role DESC, u.surname
    ");
    $stmt->execute([$userId]);
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($doctors as $doc) {
        $suffix = $doc['specialist_name'] ? " ({$doc['specialist_name']})" : '';
        echo '<div class="filter-option" data-value="' . $doc['id'] . '">' . htmlspecialchars($doc['name'] . $suffix) . '</div>';
    }
}elseif ($type === 'usersForDoctor') {
    $stmt = $pdo->prepare("
        SELECT user_id as id, CONCAT(surname, ' ', name, ' ', sec_name) as name
        FROM users
        WHERE role NOT IN ('Доктор', 'Персонал', 'Администратор')
        ORDER BY surname
    ");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($users as $user) {
        echo '<div class="filter-option" data-value="' . $user['id'] . '">' . htmlspecialchars($user['name']) . '</div>';
    }
} elseif ($type === 'rolesForDoctor') {
    echo '<div class="filter-option" data-value="Доктор">Врач</div>';
    echo '<div class="filter-option" data-value="Персонал">Персонал</div>';
} elseif ($type === 'cabinets') {
    $stmt = $pdo->query("SELECT cabinet_id as id, CONCAT('каб. ', number, ' (', floor, ' этаж)') as name FROM cabinets ORDER BY floor, number");
    $cabinets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cabinets as $cab) {
        echo '<div class="filter-option" data-value="' . $cab['id'] . '">' . htmlspecialchars($cab['name']) . '</div>';
    }
} elseif ($type === 'labServicesByDoctor') {
    $userId = $_SESSION['user_id'];
    // Получаем направление врача
    $stmt = $pdo->prepare("SELECT direction_id FROM doctors WHERE user_id = ?");
    $stmt->execute([$userId]);
    $direction_id = $stmt->fetchColumn();

    if (!$direction_id) {
        echo '';
        exit;
    }

    // Услуги только этого направления
    $stmt = $pdo->prepare("SELECT service_id as id, name FROM services WHERE direction_id = ? ORDER BY name");
    $stmt->execute([$direction_id]);
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($services as $service) {
        echo '<div class="filter-option" data-value="' . $service['id'] . '">' . htmlspecialchars($service['name']) . '</div>';
    }
}

?>