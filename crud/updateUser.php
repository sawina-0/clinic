<?php
session_start();
require_once '../config.php';

if (!isLogged() || !isAdmin()) {
    http_response_code(403);
    exit;
}

// Удаление фото пользователя (админ)
if (isset($_POST['delete_photo']) && $_POST['delete_photo'] == '1') {
    $user_id = (int)($_POST['user_id'] ?? 0);
    if (!$user_id) {
        echo json_encode(['success' => false, 'message' => 'Нет ID пользователя']);
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT photo FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $photo = $stmt->fetchColumn();
    
    if ($photo && $photo != 'none.svg' && file_exists("../img/avatars/" . $photo)) {
        unlink("../img/avatars/" . $photo);
    }
    
    $stmt = $pdo->prepare("UPDATE users SET photo = NULL WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    echo json_encode(['success' => true]);
    exit;
}

// ==================== ОБНОВЛЕНИЕ ТЕКСТОВЫХ ДАННЫХ ====================
if ($_SERVER['CONTENT_TYPE'] === 'application/json') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $user_id = (int)($data['user_id'] ?? 0);
    $surname = trim($data['surname'] ?? '');
    $name = trim($data['name'] ?? '');
    $secname = trim($data['secname'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $role = $data['role'] ?? '';

    if (!$user_id || !$surname || !$name || !$phone || !$role) {
        echo json_encode(['success' => false, 'message' => 'Заполните все поля']);
        exit;
    }

    // Нельзя менять себя
    if ($user_id == $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'Нельзя редактировать себя']);
        exit;
    }

    // Получаем текущую роль
    $stmt = $pdo->prepare("SELECT role FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $oldUser = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$oldUser) {
        echo json_encode(['success' => false, 'message' => 'Пользователь не найден']);
        exit;
    }
    $oldRole = $oldUser['role'];

    // Проверяем смену роли
    if ($oldRole !== $role) {
        if (in_array($role, ['Доктор', 'Персонал'])) {
            echo json_encode(['success' => false, 'message' => 'Чтобы сделать врача/персонал, используйте раздел "Врачи"']);
            exit;
        }
        if (in_array($oldRole, ['Доктор', 'Персонал']) && $role === 'Пользователь') {
            $stmt = $pdo->prepare("DELETE FROM doctors WHERE user_id = ?");
            $stmt->execute([$user_id]);
        }
        if (in_array($oldRole, ['Доктор', 'Персонал']) && in_array($role, ['Пользователь', 'Администратор', 'Заблокирован'])) {
            $stmt = $pdo->prepare("DELETE FROM doctors WHERE user_id = ?");
            $stmt->execute([$user_id]);
        }
    }

    // Очищаем телефон
    $phone = preg_replace('/\D/', '', $phone);
    if (strlen($phone) != 11) {
        echo json_encode(['success' => false, 'message' => 'Телефон должен содержать 11 цифр']);
        exit;
    }

    // Проверяем дубликат телефона
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE phone_num = ? AND user_id != ?");
    $stmt->execute([$phone, $user_id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Этот телефон уже зарегистрирован']);
        exit;
    }

    // Обновляем
    $stmt = $pdo->prepare("UPDATE users SET surname = ?, name = ?, sec_name = ?, phone_num = ?, role = ? WHERE user_id = ?");
    if ($stmt->execute([$surname, $name, $secname, $phone, $role, $user_id])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Ошибка при обновлении']);
    }
    exit;
}

// ==================== ОБНОВЛЕНИЕ ФОТО ====================
if (isset($_FILES['photo'])) {
    $user_id = (int)($_POST['user_id'] ?? 0);
    if (!$user_id) {
        echo json_encode(['success' => false, 'message' => 'Нет ID пользователя']);
        exit;
    }

    $file = $_FILES['photo'];
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Можно загружать только JPG, PNG, GIF или WEBP']);
        exit;
    }
    if ($file['size'] > 2 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'Файл слишком большой (макс 2MB)']);
        exit;
    }

    // Удаляем старое фото
    $stmt = $pdo->prepare("SELECT photo FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $oldPhoto = $stmt->fetchColumn();
    if ($oldPhoto && $oldPhoto != 'none.svg' && file_exists("../img/avatars/" . $oldPhoto)) {
        unlink("../img/avatars/" . $oldPhoto);
    }

    // Сохраняем новое
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newFileName = 'user_' . $user_id . '_' . time() . '.' . $ext;
    $uploadPath = '../img/avatars/' . $newFileName;

    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        $stmt = $pdo->prepare("UPDATE users SET photo = ? WHERE user_id = ?");
        $stmt->execute([$newFileName, $user_id]);
        echo json_encode(['success' => true, 'photoPath' => '../img/avatars/' . $newFileName]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Ошибка сохранения файла']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Неверный запрос']);