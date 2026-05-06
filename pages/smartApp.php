<?php
    session_start();
    require_once '../config.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="../js/toggle.js" defer></script>
    <script src="../js/hat.js" defer></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ru.js"></script>
    <link rel="shortcut icon" href="../img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../css/style.css">
    <title>Клиника Кедр - Умная запись</title>
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
    <main id="smartAppPage">
        <div class="container">
            <p>*Сервис предназначен для предварительного подбора специалиста. Не является системой медицинской диагностики. Окончательный диагноз устанавливает врач на очном приеме. При острых состояниях немедленно вызывайте скорую.</p>
            <textarea name="sympSearch" id="sympSearch" placeholder="Опишите свои симптомы"></textarea>
            <button class="commonBtn">Определить специалиста</button>
            <div class="specialistsCard">
                <div class="doctorCard">
                    <img src="../img/avatars/man1.jpeg" alt="">
                    <p>Иванов Иван Иванович</p>
                    <p>Терапевт</p>
                    <p>Стаж: 20 лет</p>
                    <button class="commonBtn">Записаться</button>
                </div>
                <div class="doctorCard">
                    <img src="../img/avatars/none.svg" alt="">
                    <p>Иванов Иван Иванович</p>
                    <p>Терапевт</p>
                    <p>Стаж: 20 лет</p>
                    <button class="commonBtn">Записаться</button>
                </div>
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
                    <p>© 2024 Клиника «Кедр». Все права защищены.</p>
                </div>
                <div class="law">
                    <a href="">политика конфиденциальности</a>
                    <a href="">публичная оферта</a>
                </div>
            </div>
        </div>
    </footer>
    <div id="app-popup-overlay" class="popupOverlay" onclick="hidePopup('app-popup')">
        <div id="app-popup" class="popupContainer">
            <div id="stepDate" class="popupContent">
                <h2>Запись</h2>
                <img src="../img/svg/cross.svg" alt="крестик - закрыть окно" onclick="hidePopup('app-popup')"> <!-- крести в абсолюте к попапу -->
                <div class="data"><!--flex-direction: row; justify-content: flex-start; -->
                    <!-- фото врача -->
                    <div class="vert"> <!--flex-direction: column; align-items: flex-start-->
                        <!--фамилия и инициалы выбранного врача-->
                        <!--название услуги-->
                        <!--цена-->
                    </div>
                </div>
                <input type="text" id="dateInput" class="calendar" placeholder="Выберите дату">
                    <!-- 7*8 padding:15px; gap:10; ширина колонок 1fr, ширина строк hug (ну в фигме стоит hug), всю первую строку занимает блок с justify-content: space-between; в котором кнопки для листания и текущий месяц и год, по типу: "< Март 2026 >". кнопки для листания треугольные скобки. записаться можно только на текующий месяц и следующий. 2 строка это ди недели пн-вс, остальные строки это дни недели, недоступные дни имеют фон var(--gray) и цвет текста #432E2599-->
                <p>*Запись к врачу переносится и отменяется не менее чем за сутки.</p><!--text-align: center-->
                <button id="madeAppDate" class="madeApp" disabled>выбрать время</button>
            </div>
            <div id="stepTime" class="popupContent">
                <h2>Запись</h2>
                <img src="../img/svg/cross.svg" alt="крестик - закрыть окно" onclick="hidePopup('app-popup')"> <!-- крести в абсолюте к попапу -->
                <div class="data"><!--flex-direction: row; justify-content: flex-start; -->
                    <!-- фото врача -->
                    <div class="vert"> <!--flex-direction: column; align-items: flex-start-->
                        <!--фамилия и инициалы выбранного врача-->
                        <!--название услуги-->
                        <!--цена-->
                    </div>
                </div>
                <div class="time">
                    <div class="time-header">
                        <button class="back-to-date"><</button>
                        <span class="selected-date-display"></span>
                    </div>
                    <div class="time-grid"></div>
                    <!-- grid 4*3, в первой строке выбранная дата, день недели и стрелка назад (стрелка в виде треугольной скобки) типо: "< вт 03.03.2026 ". дата по центру, стрелка с левого края. ниже по четыре в строке кнопочки со временем у них фон var(--lightGreen); color: var(--aboutWhite); при наведении фон становится var(--green). время вычисляется по существующим записям и графику врача на этот день недели. работает во вторник с 9 до 17 допустим, приемы по пол часа, уже есть запись на этот день в 12:00 - это время не выводим, ну мы обсуждали -->
                </div>
                <button id="madeAppTime" class="madeApp" disabled>записаться</button>
            </div>
        </div>
    </div>
</body>
</html>