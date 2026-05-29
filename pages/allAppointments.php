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
    if(isAdmin()||isDoctor()||isStuff()){
        header('Location: ../index.php');
        exit;
    }
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ru.js"></script>
    <script src="../js/profile.js" defer></script>
    <script src="../js/hat.js" defer></script>
    <script src="../js/alert.js" defer></script>
    <script src="../js/toggle.js" defer></script>
    <title>Клиника кедр - Записи</title>
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
            <?if(isLogged()): ?>
                <div class="clientFunc">
                    <?if(isAdmin() || isDoctor()|| isStuff()): ?>
                        <a href="./admin.php">админ-панель</a>
                    <?endif?>
                    <a href="./pa.php"><img src="../img/avatars/none.svg" alt=""></a>
                </div>
            <?else:?>
                <a href="./auth.php">войти</a>
            <?endif?>
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
    <main id="allApp">
        <div class="container">
            <h1>Все записи</h1>
            <input type="text" name="search" id="searchApp" placeholder="Поиск услуг" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <div class="appCards" id="appointmentsList">

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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('appointmentsList');
            const searchInput = document.getElementById('searchApp');

            function loadAppointments() {
                const search = searchInput.value;
                fetch(`../func/getUserAppointments.php?search=${encodeURIComponent(search)}`)
                    .then(response => response.text())
                    .then(data => {
                        container.innerHTML = data;
                    });
            }

            searchInput.addEventListener('input', loadAppointments);
            loadAppointments();
        });
    </script>
</body>
</html>