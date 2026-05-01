<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isLogged()) {
    echo json_encode(['success' => false, 'message' => 'Не авторизован']);
    exit;
}
// Удаление фото
if (isset($_POST['delete_photo']) && $_POST['delete_photo'] == '1') {
    $user_id = $_SESSION['user_id'];
    
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
// ============================================
// ЗАГРУЗКА ФОТО
// ============================================
if (isset($_FILES['photo'])) {
    $file = $_FILES['photo'];
    $user_id = $_SESSION['user_id'];
    
    // Проверки
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Можно загружать только JPG, PNG, GIF или WEBP']);
        exit;
    }
    
    if ($file['size'] > 2 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'Файл слишком большой (макс 2MB)']);
        exit;
    }
    
    // Получаем старое фото
    $stmt = $pdo->prepare("SELECT photo FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $oldPhoto = $stmt->fetchColumn();
    
    // Удаляем старое фото, если оно есть и это не заглушка
    if (!empty($oldPhoto) && $oldPhoto != 'none.svg' && file_exists("../img/avatars/" . $oldPhoto)) {
        unlink("../img/avatars/" . $oldPhoto);
    }
    
    // Генерируем новое имя: user_123_время.расширение
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newFileName = 'user_' . $user_id . '_' . time() . '.' . $ext;
    $uploadPath = '../img/avatars/' . $newFileName;
    
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        // Обновляем БД
        $stmt = $pdo->prepare("UPDATE users SET photo = ? WHERE user_id = ?");
        $stmt->execute([$newFileName, $user_id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Фото обновлено',
            'photoPath' => '../img/avatars/' . $newFileName
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Ошибка загрузки файла']);
    }
    exit;
}

// ============================================
// ОБНОВЛЕНИЕ ДАННЫХ
// ============================================
if (isset($_POST['action']) && $_POST['action'] == 'update_data') {
    $surname = trim($_POST['surname'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $secName = trim($_POST['secName'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $currentPass = trim($_POST['currentPass'] ?? '');
    $newPass = trim($_POST['newPass'] ?? '');
    $newPassAgain = trim($_POST['newPassAgain'] ?? '');

    // Валидация
    $errors = [];

    if (empty($surname)) $errors[] = 'Фамилия обязательна';
    if (empty($name)) $errors[] = 'Имя обязательно';
    if (empty($phone)) $errors[] = 'Телефон обязателен';

    // Проверка телефона (11 цифр)
    $phone = preg_replace('/\D/', '', $phone);
    if (strlen($phone) != 11) {
        $errors[] = 'Телефон должен содержать 11 цифр';
    }

    // Проверка на дубликат телефона (кроме текущего пользователя)
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE phone_num = ? AND user_id != ?");
    $stmt->execute([$phone, $_SESSION['user_id']]);
    if ($stmt->fetch()) {
        $errors[] = 'Этот телефон уже зарегистрирован';
    }

    // Если есть новые пароли — проверяем
    if (!empty($newPass) || !empty($newPassAgain) || !empty($currentPass)) {
        if (empty($currentPass)) {
            $errors[] = 'Введите текущий пароль';
        }
        if (empty($newPass)) {
            $errors[] = 'Введите новый пароль';
        }
        if ($newPass !== $newPassAgain) {
            $errors[] = 'Новые пароли не совпадают';
        }
        if (strlen($newPass) < 6 || !preg_match('/[А-ЯA-Z]/', $newPass) || !preg_match('/[0-9]/', $newPass)) {
            $errors[] = 'Пароль должен содержать заглавную букву, цифру и минимум 6 символов';
        }
    }

    if (!empty($errors)) {
        echo json_encode(['success' => false, 'message' => implode("\n", $errors)]);
        exit;
    }

    // Проверяем текущий пароль, если меняем
    if (!empty($currentPass)) {
        $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if (!password_verify($currentPass, $user['password'])) {
            echo json_encode(['success' => false, 'message' => 'Неверный текущий пароль']);
            exit;
        }
        
        // Хешируем новый пароль
        $newPassHash = password_hash($newPass, PASSWORD_DEFAULT);
    }

    // Обновляем данные
    if (!empty($newPassHash)) {
        $sql = "UPDATE users SET surname = ?, name = ?, sec_name = ?, phone_num = ?, password = ? WHERE user_id = ?";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([$surname, $name, $secName, $phone, $newPassHash, $_SESSION['user_id']]);
    } else {
        $sql = "UPDATE users SET surname = ?, name = ?, sec_name = ?, phone_num = ? WHERE user_id = ?";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([$surname, $name, $secName, $phone, $_SESSION['user_id']]);
    }

    if ($result) {
        // Обновляем сессию
        $_SESSION['name'] = $name;
        
        echo json_encode(['success' => true, 'message' => 'Данные обновлены']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Ошибка при обновлении']);
    }
    exit;
}

// Если ни одно действие не подошло
echo json_encode(['success' => false, 'message' => 'Неверный запрос']);