<?php
session_start();
require_once '../config.php';

$doctor_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$doctor_id) {
    header('Location: specialists.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT 
        u.surname,
        u.name,
        u.sec_name,
        u.photo,
        d.exp,
        dir.name as direction_name,
        dir.specialist_name,
        dd.education,
        dd.qualification,
        dd.awards
    FROM doctors d
    JOIN users u ON d.user_id = u.user_id
    JOIN directions dir ON d.direction_id = dir.direction_id
    LEFT JOIN doctor_details dd ON d.doctor_id = dd.doctor_id
    WHERE d.doctor_id = ?
");
$stmt->execute([$doctor_id]);
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$doctor) {
    header('Location: specialists.php');
    exit;
}

$fullName = $doctor['surname'] . ' ' . $doctor['name'] . ' ' . $doctor['sec_name'];
$photoPath = $doctor['photo'] ? '../img/avatars/' . $doctor['photo'] : '../img/avatars/none.svg';
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($fullName) ?> - Клиника Кедр</title>
    <link rel="shortcut icon" href="../img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../css/style.css">
    <script src="../js/toggle.js" defer></script>
    <script src="../js/hat.js" defer></script>
</head>

<body>
    <header>
        <div class="container hat">
            <a class="logo" href="../index.php#hero">
                <img src="../img/svg/logoGreen.svg" alt="логтип кедр">
                Клиника “Кедр”
            </a>
            <nav class="desctopNav">
                <ul>
                    <li><a href="../index.php#aboutUs">о нас</a></li>
                    <li><a href="../index.php#specialists">специалисты</a></li>
                    <li><a href="../index.php#services">услуги</a></li>
                    <li><a href="../index.php#contacts">контакты</a></li>
                </ul>
            </nav>
            <? if (isLogged()): ?>
                <div class="clientFunc">
                    <? if (isAdmin() || isDoctor() || isStuff()): ?>
                        <a href="./admin.php">админ-панель</a>
                    <? endif ?>
                    <a href="./pa.php"><img src="../img/avatars/none.svg" alt=""></a>
                </div>
            <? else: ?>
                <a href="./auth.php">войти</a>
            <? endif ?>
        </div>
    </header>
    <div class="mobileMenu">
        <nav>
            <ul>
                <li><a href="../index.php#aboutUs" onclick="toggleMenu()">о нас</a></li>
                <li><a href="../index.php#specialists" onclick="toggleMenu()">специалисты</a></li>
                <li><a href="../index.php#services" onclick="toggleMenu()">услуги</a></li>
                <li><a href="../index.php#contacts" onclick="toggleMenu()">контакты</a></li>
            </ul>
        </nav>
    </div>
    <div class="burger" onclick="toggleMenu()">
        <span></span>
        <span></span>
        <span></span>
    </div>
    <main id="doctorPage">
        <div class="container">
            <div class="top">
                <img src="<?= htmlspecialchars($photoPath) ?>" alt="<?= htmlspecialchars($fullName) ?>">
                <div class="txt">
                    <h1><?= htmlspecialchars($fullName) ?></h1>
                    <p class="specialist"><?= htmlspecialchars($doctor['specialist_name']) ?></p>
                    <p class="exp">Стаж: <?= (int)$doctor['exp'] ?> лет</p>
                </div>
            </div>
            <div class="doctorInfo">
                <?php if (!empty($doctor['education'])): ?>
                    <div class="detailBlock">
                        <h3>Образование:</h3>
                        <p><?= nl2br(htmlspecialchars($doctor['education'])) ?></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($doctor['qualification'])): ?>
                    <div class="detailBlock">
                        <h3>Квалификации:</h3>
                        <p><?= nl2br(htmlspecialchars($doctor['qualification'])) ?></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($doctor['awards'])): ?>
                    <div class="detailBlock">
                        <h3>Награды и достижения:</h3>
                        <p><?= nl2br(htmlspecialchars($doctor['awards'])) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    <footer>
        <div class="container">
            <nav>
                <ul>
                    <li><a href="../index.php#aboutUs">о нас</a></li>
                    <li><a href="../index.php#specialists">специалисты</a></li>
                    <li><a href="../index.php#services">услуги</a></li>
                    <li><a href="../index.php#contacts">контакты</a></li>
                    <li><a href="../index.php#smartApp">как работает умная запись</a></li>
                </ul>
            </nav>
            <span></span>
            <div class="info">
                <div class="logoC">
                    <a class="logo" href="#hero">
                        <img src="../img/svg/logoFooter.svg" alt="логотип - кедр">
                        Кедр
                    </a>
                    <p>© 2026 Клиника «Кедр». Все права защищены.</p>
                </div>
                <div class="law">
                    <a href="./privacy.php">политика конфиденциальности</a>
                    <a href="./oferta.php">публичная оферта</a>
                </div>
            </div>
        </div>
    </footer>
</body>

</html>