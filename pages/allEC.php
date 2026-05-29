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
    <script src="../js/hat.js" defer></script>
    <script src="../js/alert.js" defer></script>
    <script src="../js/toggle.js" defer></script>
    <script src="../js/filters.js" defer></script>
    <script src="../js/customSelect.js" defer></script>
    <title>Клиника кедр - Электронная карта</title>
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
    <main id="ec">
        <div class="container">
            <h1>Электронная карта</h1>
            <form class="filters" data-target="ecList" data-url="../func/getEcard.php">
                <div class="custom-select-wrapper">
                    <div class="custom-select-trigger">
                        <span>Всё</span>
                        <img src="../img/svg/selectArrow.svg" alt="">
                    </div>
                    <div class="custom-select-dropdown">
                        <div class="options-container">
                            <div class="filter-option" data-value="all">Всё</div>
                            <div class="filter-option" data-value="diagnose">Только диагнозы</div>
                            <div class="filter-option" data-value="analysis">Только анализы</div>
                        </div>
                    </div>
                </div>
                <input type="text" name="search" id="searchInput" placeholder="Поиск...">
            </form>
            <div id="ecList" class="ecList">
                <!-- сюда загружается лента -->
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
    <?php include '../component/alert.php'; ?>
</body>
</html>