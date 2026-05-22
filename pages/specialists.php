<?php
    session_start();
    require_once '../config.php';
?>
<script>
    window.isPatient = <?php echo isLogged() && $_SESSION['role'] === 'Пользователь' ? 'true' : 'false'; ?>;
    window.isAdmin = <?php echo isAdmin() ? 'true' : 'false'; ?>;
    window.isDoctor = <?php echo isDoctor() ? 'true' : 'false'; ?>;
    window.isStuff = <?php echo isStuff() ? 'true' : 'false'; ?>;
    window.isLogged = <?php echo isLogged() ? 'true' : 'false'; ?>;
    window.isBlocked = <?php echo isBlocked() ? 'true' : 'false'; ?>;
</script>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="format-detection" content="telephone=no">
    <script src="../js/toggle.js" defer></script>
    <script src="../js/hat.js" defer></script>
    <script src="../js/filters.js" defer></script>
    <script src="../js/doctorsModalData.js" defer></script>
    <script src="../js/customSelect.js" defer></script>
    <script src="../js/alert.js" defer></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ru.js"></script>
    <link rel="shortcut icon" href="../img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../css/style.css">
    <title>Клиника Кедр - Специалисты</title>
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
    <main id="allSpecialists">
        <div class="container">
            <form class="filters" data-target="doctorsList" data-url="../func/getFiltredDoctors.php">
                <div class="custom-select-wrapper">
                    <div class="custom-select-trigger">
                        <span>Все отделения</span>
                        <img src="../img/svg/selectArrow.svg" alt="">
                    </div>
                    <div class="custom-select-dropdown">
                        <input type="text" class="search-input" placeholder="Поиск">
                        <div class="options-container">
                            <div class="filter-option" data-value="">Все отделения</div>
                            <?php
                            $sql = "SELECT direction_id, name FROM directions ORDER BY name";
                            $stmt = $pdo->query($sql);
                            $directions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($directions as $row) {
                                $selected = (isset($_GET['direction']) && $_GET['direction'] == $row['direction_id']) ? 'selected' : '';
                                echo '<div class="filter-option" data-value="' . $row['direction_id'] . '">' . htmlspecialchars($row['name']) . '</div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <input type="text" name="search" id="search" placeholder="Поиск" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            </form>
            <div id="doctorsList" class="doctorsList">
                
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
    <div id="app-popup-overlay" class="popupOverlay" onclick="hidePopup('app-popup')">
        <div id="app-popup" class="popupContainer">
            <div id="stepService" class="popupContent">
                <h2>Запись</h2>
                <img src="../img/svg/cross.svg" alt="крестик - закрыть окно" onclick="hidePopup('app-popup')"> <!-- крести в абсолюте к попапу -->
                <div class="data"><!--flex-direction: row; justify-content: flex-start;-->
                    <!--фото врача-->
                    <!--фамилия + инициалы-->
                </div>
                <div class="custom-select-wrapper">
                    <div class="custom-select-trigger">
                        <span>Выберите услугу</span>
                        <img src="../img/svg/selectArrow.svg" alt="">
                    </div>
                    <div class="custom-select-dropdown">
                        <input type="text" class="search-input" placeholder="Поиск">
                        <div class="options-container">
                            <!-- сюда будут грузиться услуги -->
                        </div>
                    </div>
                </div>
                <button id="madeAppService" class="madeApp" disabled>выбрать дату</button>
            </div>
            <div id="stepDate" class="popupContent">
                <h2>Запись</h2>
                <img src="../img/svg/cross.svg" alt="крестик - закрыть окно" onclick="hidePopup('app-popup')"> <!-- крести в абсолюте к попапу -->
                <div class="data"><!--flex-direction: row; justify-content: flex-start; при нажатии на блок идем на шаг с услугами-->
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
                <div class="data"><!--flex-direction: row; justify-content: flex-start; при нажатии на блок идем на шаг с врачами-->
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
    <?php include '../component/alert.php'; ?>
</body>
</html>