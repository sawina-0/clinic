<?php
    session_start();
    require_once '../config.php';
    if(!isLogged()){
        header('Location: ./auth.php');
        exit;
    }
    if(isBlocked()){
        session_destroy();
        header('Location: ../index.php');
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Определяем путь к фото
    $photoPath = $user['photo'] 
        ? '../img/avatars/' . $user['photo'] 
        : '../img/avatars/none.svg';

    function formatPhone($phone) {
        $phone = preg_replace('/\D/', '', $phone);
        if (strlen($phone) == 11) {
            return '8(' . substr($phone, 1, 3) . ')' . substr($phone, 4, 3) . '-' . substr($phone, 7, 2) . '-' . substr($phone, 9, 2);
        }
        return $phone;
    }
    $stmt = $pdo->prepare("
        SELECT 
            a.appointment_id,
            a.app_datetime,
            a.status,
            s.name as service_name,
            c.number as cabinet_number,
            c.floor,
            CONCAT(u.surname, ' ', LEFT(u.name, 1), '.', LEFT(u.sec_name, 1), '.') as doctor_short,
            dir.specialist_name
        FROM appointments a
        JOIN services s ON a.service_id = s.service_id
        JOIN cabinets c ON a.cabinet_id = c.cabinet_id
        JOIN doctors d ON a.doctor_id = d.doctor_id
        JOIN users u ON d.user_id = u.user_id
        JOIN directions dir ON d.direction_id = dir.direction_id
        WHERE a.user_id = ? AND a.status = 'запланирован'
        ORDER BY a.app_datetime ASC
        LIMIT 5
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Получаем диагнозы пользователя
    $stmt = $pdo->prepare("
        SELECT 
            d.date,
            d.diagnose_text,
            d.file_name,
            CONCAT(u.surname, ' ', LEFT(u.name, 1), '.', LEFT(u.sec_name, 1), '.') as doctor_short,
            dir.specialist_name
        FROM diagnose d
        JOIN doctors doc ON d.doctor_id = doc.doctor_id
        JOIN users u ON doc.user_id = u.user_id
        JOIN directions dir ON doc.direction_id = dir.direction_id
        WHERE d.user_id = ?
        ORDER BY d.date ASC
        LIMIT 5
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $diagnoses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="format-detection" content="telephone=no">
    <link rel="shortcut icon" href="../img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ru.js"></script>
    <script src="../js/maskAndError.js" defer></script>
    <script src="../js/profile.js" defer></script>
    <script src="../js/hat.js" defer></script>
    <script src="../js/alert.js" defer></script>
    <title>Клиника кедр - Профиль</title>
</head>
<body>
    <main class="pa">
        <div class="top">
            <a class="logo" href="../index.php#hero">
                <img src="../img/svg/logoGreen.svg" alt="логотип кедр">
                Клиника “Кедр”
            </a>
            <a href="../logout.php"><img src="../img/svg/exit.svg" alt="выйти из аккаунта"></a>
        </div>
        <div class="container">
            <h1>Личный кабинет</h1>
            <div class="personalData" id="viewBlock">
                <div class="avatar-wrapper">
                    <img src="<?= htmlspecialchars($photoPath) ?>" alt="фото профиля" id="profilePhoto">
                    <?php if ($user['photo'] && $user['photo'] != 'none.svg'): ?>
                        <button type="button" id="deleteAvatarBtn" class="delete-avatar-btn"><img src="../img/svg/trashRed.svg" alt="удалить аватарку"></button>
                    <?php endif; ?>
                </div>
                <div class="pInfo">
                    <p id="viewSurname"><?= htmlspecialchars($user['surname']) ?></p>
                    <p id="viewName"><?= htmlspecialchars($user['name']) ?></p>
                    <p id="viewSecName"><?= htmlspecialchars($user['sec_name']) ?></p>
                    <p id="viewPhone"><?= formatPhone($user['phone_num']) ?></p>
                </div>
                <div class="aife">
                    <button class="pEdit" id="changeBtn">изменить</button>
                </div>
            </div>
            <div class="personalDataEdit" id="editBlock">
                <img src="<?= htmlspecialchars($photoPath) ?>" alt="фото профиля" id="editPhoto">
                <div class="inputs">
                    <div class="pInfo">
                        <div class="field">
                            <label for="surname">фамилия:</label>
                            <input type="text" name="surname" id="surname" value="<?= htmlspecialchars($user['surname']) ?>">
                        </div>
                        <div class="field">
                            <label for="name">имя:</label>
                            <input type="text" name="name" id="name" value="<?= htmlspecialchars($user['name']) ?>">
                        </div>
                        <div class="field">
                            <label for="secName">отчество:</label>
                            <input type="text" name="secName" id="secName" value="<?= htmlspecialchars($user['sec_name']) ?>">
                        </div>
                        <div class="field">
                            <label for="phone">телефон:</label>
                            <input type="tel" name="phone" id="phone" value="<?= formatPhone($user['phone_num']) ?>">
                        </div>
                    </div>
                    <div class="pPassword">
                        <div class="field">
                            <label for="currentPass">текущий пароль:</label>
                            <input type="password" name="currentPass" id="currentPass">
                        </div>
                        <div class="field">
                            <label for="newPass">новый пароль:</label>
                            <input type="password" name="newPass" id="newPass">
                        </div>
                        <div class="field">
                            <label for="newPassAgain">повторить новый пароль:</label>
                            <input type="password" name="newPassAgain" id="newPassAgain">
                        </div>
                        <p>пароль должен включать цифру, заглавную букву и минимум 6 символов. оставьте пустыми поля паролей, если не меняете</p>
                    </div>
                </div>
                <div class="aife">
                    <button class="pEdit" id="saveBtn">сохранить</button>
                    <button class="pEdit" id="cancelBtn">отмена</button>
                </div>
            </div>
            <input type="file" id="photoUpload" accept="image/*" style="display: none;">
            <?if(!isAdmin() && !isStuff() && !isDoctor()): ?>
            <div class="scrolls">
                <div class="appBlock scroll">
                    <h2>Ближайшие записи</h2>
                    <div class="closestApps">
                        <?php if (empty($appointments)): ?>
                            <div class="emptyState">
                                <p>У вас нет предстоящих записей</p>
                                <a href="./services.php" class="commonBtn">Записаться на приём</a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($appointments as $app): 
                                $date = new DateTime($app['app_datetime']);
                            ?>
                                <div class="app">
                                    <div class="dateTime">
                                        <span><?= $date->format('d.m.Y') ?></span>
                                        <span><?= $date->format('H:i') ?></span>
                                    </div>
                                    <div class="who">
                                        <p><?= htmlspecialchars($app['doctor_short']) ?></p>
                                        <span>(<?= htmlspecialchars($app['specialist_name']) ?>)</span>
                                    </div>
                                    <p><?= htmlspecialchars($app['service_name']) ?></p>
                                    <p>кабинет <?= (int)$app['cabinet_number'] ?></p>
                                    <div class="btns">
                                        <button class="reschedule" data-id="<?= (int)$app['appointment_id'] ?>">перенести</button>
                                        <button class="cancel" data-id="<?= $app['appointment_id'] ?>">отменить</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="eCardBlock scroll">
                    <h2>Электронная карта</h2>
                    <div class="eCard">
                        <?php if (empty($diagnoses)): ?>
                            <div class="emptyState">
                                <p>История диагнозов пуста</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($diagnoses as $diag): ?>
                                <div class="diagnose">
                                    <span class="date"><?= date('d.m.Y', strtotime($diag['date'])) ?></span>
                                    <div class="who">
                                        <p><?= htmlspecialchars($diag['doctor_short']) ?></p>
                                        <span>(<?= htmlspecialchars($diag['specialist_name']) ?>)</span>
                                    </div>
                                    <div class="dTxt">
                                        <span>Диагноз:</span>
                                        <p><?= htmlspecialchars($diag['diagnose_text']) ?></p>
                                    </div>

                                    <?php if (!empty($diag['file_name'])): ?>
                                        <div class="file-link">
                                            <a href="../func/download.php?file=<?= urlencode($diag['file_name']) ?>" target="_blank">
                                            Смотерть файл</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?endif?>
        </div>
    </main>
    <div id="cancel-popup-overlay" class="popupOverlay" onclick="hidePopup('cancel-popup')">
        <div id="cancel-popup" class="popupContainer">
            <img src="../img/svg/cross.svg" alt="крестик - закрыть окно" onclick="hidePopup('cancel-popup')">
            <h2>Отмена записи</h2>
            <p>Убедительная просьба, отменять запись к врачу не менее чем за сутки.</p>
            <button class="cancelBtn" id="confirmCancelBtn">отменить запись</button>
        </div>
    </div>
    <div id="reschedule-popup-overlay" class="popupOverlay" onclick="hidePopup('reschedule-popup')">
        <div id="reschedule-popup" class="popupContainer">
            <div id="stepDate" class="popupContent">
                <h2>Перенос записи</h2>
                <img src="../img/svg/cross.svg" alt="крестик - закрыть окно" onclick="hidePopup('reschedule-popup')">
                <div class="data">
                    <!-- фото врача -->
                    <div class="vert"> <!--flex-direction: column; align-items: flex-start-->
                        <!--фамилия и инициалы выбранного врача-->
                        <!--название услуги-->
                        <!--цена-->
                    </div>
                </div>
                <input type="text" id="dateInput" class="calendar" placeholder="Выберите дату">
                <p>Убедительная просьба, переносить запись к врачу не менее чем за сутки.</p>
                <button id="madeAppDate" class="madeReschedule" disabled>выбрать время</button>
            </div>
            <div id="stepTime" class="popupContent">
                <h2>Перенос записи</h2>
                <img src="../img/svg/cross.svg" alt="крестик - закрыть окно" onclick="hidePopup('reschedule-popup')">
                    <!-- фото врача -->
                    <div class="vert">
                        <!--фамилия и инициалы выбранного врача-->
                        <!--название услуги-->
                        <!--цена-->
                    </div>
                <div class="time">
                    <div class="time-header">
                        <button class="back-to-date"><</button>
                        <span class="selected-date-display"></span>
                    </div>
                    <div class="time-grid"></div>
                </div>
                <button id="madeAppReschedule" class="madeReschedule" disabled>перенести</button>
            </div>
        </div>
    </div>
    <?php include '../component/alert.php'; ?>
</body>
</html>