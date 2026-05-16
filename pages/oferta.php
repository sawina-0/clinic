<?php
    session_start();
    require_once '../config.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="format-detection" content="telephone=no">
    <script src="../js/toggle.js" defer></script>
    <script src="../js/hat.js" defer></script>
    <link rel="shortcut icon" href="../img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../css/style.css">
    <title>Клиника Кедр - Публичная оферта</title>
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
    <main id="law">
        <div class="container">
            <h1>Публичная оферта</h1>
            <p>
Важно:
Настоящий сайт создан в рамках дипломной работы и не является реальной медицинской платформой. Все данные, расписание, услуги и записи — тестовые. Оказание медицинских услуг через сайт не осуществляется, оплата не производится.
Сайт демонстрирует функциональные возможности информационной системы частной клиники.
<br>1. Общие положения
<br>1.1. Данный документ является официальным предложением (офертой) ООО «Кедр» (Исполнитель) любому дееспособному лицу (Заказчик) заключить договор на оказание услуг по предварительной записи на приём к специалистам на условиях, изложенных ниже.

<br>1.2. Все действия на сайте являются демонстрационными и не создают юридических обязательств.

<br>2. Предмет оферты
<br>2.1. Исполнитель предоставляет Заказчику техническую возможность:

<br>&ensp;просматривать список врачей и услуг;

<br>&ensp;выбирать специалиста и свободное время;

<br>&ensp;создавать тестовую запись на приём.

<br>2.2. Все записи носят учебно-демонстрационный характер и не влекут реального оказания медицинских услуг.

<br>3. Порядок записи
<br>3.1. Заказчик заполняет форму записи на Сайте: выбирает специалиста, услугу, дату и время.
<br>3.2. После подтверждения запись отображается в личном кабинете в тестовом режиме.
<br>3.3. Отмена и перенос записи возможны через личный кабинет или кнопки в карточке записи.

<br>4. Права и обязанности сторон
<br>&ensp;Исполнитель:

обеспечивает работоспособность демонстрационного функционала на момент проверки;

не гарантирует сохранность данных после завершения работы дипломного проекта.

<br>&ensp;Заказчик:

обязуется использовать сайт в учебных целях;

не предъявлять реальных требований по оказанию медицинских услуг.

<br>5. Оплата
<br>5.1. Сайт не принимает и не обрабатывает платежи.
<br>5.2. Все данные о ценах указаны в демонстрационных целях.

<br>6. Ответственность
<br>6.1. Исполнитель не несёт ответственности за любые прямые или косвенные убытки, связанные с использованием учебного сайта.
<br>6.2. Все медицинские формулировки, симптомы, рекомендации, диагнозы являются учебными и тестовыми.

<br>7. Прочие условия
<br>7.1. Акцептом оферты является нажатие кнопки «Записаться» в демонстрационных целях.
<br>7.2. Оферта действует только в рамках дипломной работы и не порождает реальных прав и обязанностей.

<br>8. Реквизиты (учебные)
<br>&ensp;ООО «Кедр» (учебный проект)
<br>&ensp;ИНН 7701234567
<br>&ensp;ОГРН 1187746123456
<br>&ensp;Адрес: г. Санкт-Петербург, ул. Лесная, д. 36 (учебный адрес)
<br>&ensp;Email: contact@kedrspb.ru</p>
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