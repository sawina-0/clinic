<?php
session_start();
require_once '../config.php';

if (!isLogged()) {
    http_response_code(403);
    exit;
}

// Дополнительные проверки прав
if (!isAdmin() && !isDoctor() && !isStuff()) {
    http_response_code(403);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$type = $data['type'] ?? '';
$id = (int)($data['id'] ?? 0);

if (!$type || !$id) {
    echo json_encode(['success' => false, 'message' => 'Неверные данные']);
    exit;
}

try {
    if ($type === 'user') {
        if ($id == $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Нельзя удалить себя']);
            exit;
        }
        
        // Получаем имя файла фото
        $stmt = $pdo->prepare("SELECT photo FROM users WHERE user_id = ?");
        $stmt->execute([$id]);
        $photo = $stmt->fetchColumn();
        
        // Удаляем файл, если он есть и не является заглушкой
        if ($photo && $photo != 'none.svg' && file_exists("../img/avatars/" . $photo)) {
            unlink("../img/avatars/" . $photo);
        }
        
        // Удаляем пользователя (каскад удалит всё связанное)
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$id]);
    }
    elseif ($type === 'service') {
        $stmt = $pdo->prepare("DELETE FROM services WHERE service_id = ?");
        $stmt->execute([$id]);
        
    } elseif ($type === 'cabinet') {
        // Проверяем, привязан ли кабинет к врачу
        $stmt = $pdo->prepare("SELECT doctor_id FROM doctors WHERE cabinet_id = ?");
        $stmt->execute([$id]);
        $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($doctor) {
            // Получаем user_id врача
            $stmt = $pdo->prepare("SELECT user_id FROM doctors WHERE doctor_id = ?");
            $stmt->execute([$doctor['doctor_id']]);
            $user_id = $stmt->fetchColumn();
            
            // Меняем роль на "Пользователь"
            if ($user_id) {
                $stmt = $pdo->prepare("UPDATE users SET role = 'Пользователь' WHERE user_id = ?");
                $stmt->execute([$user_id]);
            }
            
            // Удаляем врача (каскад удалит всё связанное)
            $stmt = $pdo->prepare("DELETE FROM doctors WHERE doctor_id = ?");
            $stmt->execute([$doctor['doctor_id']]);
        }
        
        // Удаляем кабинет
        $stmt = $pdo->prepare("DELETE FROM cabinets WHERE cabinet_id = ?");
        $stmt->execute([$id]);
        
    } elseif ($type === 'appointment') {
        $stmt = $pdo->prepare("DELETE FROM appointments WHERE appointment_id = ?");
        $stmt->execute([$id]);
        
    } elseif ($type === 'doctor') {
        // Сначала удаляем график (каскад удалит, но для порядка)
        $stmt = $pdo->prepare("DELETE FROM doctor_schedule WHERE doctor_id = ?");
        $stmt->execute([$id]);
        
        // Получаем user_id
        $stmt = $pdo->prepare("SELECT user_id FROM doctors WHERE doctor_id = ?");
        $stmt->execute([$id]);
        $user_id = $stmt->fetchColumn();
        
        // Удаляем врача
        $stmt = $pdo->prepare("DELETE FROM doctors WHERE doctor_id = ?");
        $stmt->execute([$id]);
        
        // Меняем роль пользователя на "Пользователь"
        if ($user_id) {
            $stmt = $pdo->prepare("UPDATE users SET role = 'Пользователь' WHERE user_id = ?");
            $stmt->execute([$user_id]);
        }
        
    } elseif ($type === 'schedule') {
        // Удаляем график врача
        $stmt = $pdo->prepare("DELETE FROM doctor_schedule WHERE doctor_id = ?");
        $stmt->execute([$id]);
        
    } elseif ($type === 'diagnose') {
        $stmt = $pdo->prepare("DELETE FROM diagnose WHERE diagnose_id = ?");
        $stmt->execute([$id]);
    }
    
    echo json_encode(['success' => true]);
    
} catch (PDOException $e) {
    // Если каскад не сработал (например, есть ограничения), покажем ошибку
    echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
}