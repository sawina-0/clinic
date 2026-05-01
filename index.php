<?php
session_start();
require_once 'config.php';
$sql = "SELECT 
            d.doctor_id AS doctor_id,
            d.exp,
            u.photo,
            CONCAT(
                u.surname, ' ', 
                LEFT(u.name, 1), '.', 
                LEFT(u.sec_name, 1), '.'
            ) AS full_name,
            dir.specialist_name
        FROM doctors d
        JOIN users u ON d.user_id = u.user_id
        JOIN directions dir ON d.direction_id = dir.direction_id
        WHERE u.role IN ('Доктор', 'Персонал')  
        ORDER BY d.exp DESC
        LIMIT 6";
$stmt = $pdo->query($sql);
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="./img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="./css/style.css">
    <script src="./js/toggle.js" defer></script>
    <script src="./js/hat.js" defer></script>
    <script src="./js/scroll.js" defer></script>
    <script src="./js/accordion.js" defer></script>
    <title>Клиника Кедр - Главная</title>
</head>
<body>
    <header>
        <div class="container hat">
            <a class="logo" href="#hero">
                <img src="./img/svg/logoGreen.svg" alt="логтип кедр">
                Клиника “Кедр”
            </a>
            <nav class="desctopNav">
                <ul>
                    <li><a href="#aboutUs">о нас</a></li>
                    <li><a href="#specialists">специалисты</a></li>
                    <li><a href="#services">услуги</a></li>
                    <li><a href="#contacts">контакты</a></li>
                </ul>
            </nav>
            <?if(isLogged()): ?>
                <div class="clientFunc">
                    <?if(isAdmin() || isDoctor()|| isStuff()): ?>
                        <a href="./pages/admin.php">админ-панель</a>
                    <?endif?>
                    <a href="./pages/pa.php"><img src="./img/avatars/none.svg" alt=""></a>
                </div>
            <?else:?>
                <a href="./pages/auth.php">войти</a>
            <?endif?>
        </div>
    </header>
    <div class="mobileMenu">
        <nav>
            <ul>
                <li><a href="#aboutUs" onclick="toggleMenu()">о нас</a></li>
                <li><a href="#specialists" onclick="toggleMenu()">специалисты</a></li>
                <li><a href="#services" onclick="toggleMenu()">услуги</a></li>
                <li><a href="#contacts" onclick="toggleMenu()">контакты</a></li>
            </ul>
        </nav>
    </div>
    <div class="burger" onclick="toggleMenu()">
        <span></span>
        <span></span>
        <span></span>
    </div>
    <main id="hero">
        <div class="container">
            <div class="txt">
                <h1>Кедр - частная клиника комплексного здоровья</h1>
                <h2>Ваше здоровье - наш приоритет</h2>
            </div>
            <div class="join">
                <span>Выбирайте легкий путь к здоровью</span>
                <div class="btns">
                    <a href="" class="btn white">умная запись по симптомам</a>
                    <a href="./pages/specialists.php" class="btn green">прямая запись к врачу</a>
                </div>
            </div>
        </div>
    </main>
    <section id="aboutUs">
        <div class="container">
            <div class="point">
                <h2>Онлайн запись 24/7</h2>
                <p>запись без звонков и очередей</p>
            </div>
            <div class="point">
                <h2>Электронная карта</h2>
                <p>анализы и история в личном кабинете</p>
            </div>
            <div class="point">
                <h2>Прозрачные цены</h2>
                <p>фиксированная стоимость без скрытых платежей</p>
            </div>
        </div>
    </section>
    <section id="smartApp">
        <div class="container">
            <h2>Как работает умная запись</h2>
            <div class="blocks">
                <div class="top">
                    <div class="block">
                        <p>Вы вводите свои симптомы</p>
                        <span>Головная боль, тошн...</span>
                    </div>
                    <img src="./img/svg/arrow.svg" alt="">
                    <div class="block">
                        <p>Наша система анализирует</p>
                        <img src="./img/svg/loading.svg" alt="">
                    </div>
                    <img src="./img/svg/arrow.svg" alt="">
                    <img src="./img/svg/arrowAngle.svg" alt="" class="adaptive">
                    <div class="block">
                        <p>Мы предлагаем вам врачей для записи</p>
                        <div class="group">
                            <img src="./img/svg/chel.svg" alt="">
                            <img src="./img/svg/chel.svg" alt="">
                            <img src="./img/svg/chel.svg" alt="">
                        </div>
                    </div>
                </div>
                <div class="bottom">
                    <div class="block">
                        <p>Подтверждение вашей записи</p>
                        <img src="./img/svg/check.svg" alt="">
                    </div>
                    <img src="./img/svg/arrow.svg" alt="">
                    <div class="block">
                        <p>Вы выбираете удобный вам день и время</p>
                        <img src="./img/svg/calendar.svg" alt="">
                    </div>
                </div>
                <img src="./img/svg/arrowAngle.svg" alt="" class="abs">
            </div>
            <p>*Система анализирует симптомы для рекомендации специалиста. Постановка диагноза и назначение лечения производится только врачом на приёме.</p>
        </div>
    </section>
    <section id="specialists">
        <div class="container">
            <h2>Наши специалисты</h2>
            <button class="swapButton" type="button" id="swapLeft">
                <img src="./img/svg/swapGreenArrow.svg" alt="стрелка - листать влево">
            </button>
            <div class="doctorScroll" id="box">
                
                <?php foreach ($doctors as $row): 
                    $photoPath = $row['photo'] 
                        ? './img/avatars/' . $row['photo'] 
                        : './img/avatars/none.svg'; ?>

                    <div class="doctorCard">
                        <img src="<?=htmlspecialchars($photoPath)?>" alt="<?=htmlspecialchars($row['full_name'])?>">
                        <h3><?=htmlspecialchars($row['full_name'])?></h3>
                        <p><?=htmlspecialchars($row['specialist_name'])?></p>
                        <p>Стаж: <?=(int)$row['exp']?> лет</p>
                    </div>

                <?php endforeach; ?>

            </div>
            <button class="swapButton" type="button" id="swapRight">
                <img src="./img/svg/swapGreenArrow.svg" alt="стрелка - листать вправо" id="swapRight">
            </button>
            <div class="button">
                <a class="commonBtn" href="./pages/specialists.php">смотреть всех</a>
            </div>
        </div>
    </section>
    <section id="services">
        <div class="container">
            <h2>Услуги</h2>
            <div class="content" id="accServices">
                <button type="button" class="accordion">Терапия</button>
                <div class="panel">
                    <div class="point">
                        <p>Консультация терапевта</p>
                        <p>2500 ₽ </p>
                    </div>
                    <div class="point">
                        <p>Вакцинация (без стоимости препарата)</p>
                        <p>1500 ₽ </p>
                    </div>
                </div>
                <button type="button" class="accordion">Кардиология</button>
                <div class="panel">
                    <div class="point">
                        <p>Консультация кардиолога</p>
                        <p>3500 ₽</p>
                    </div>
                    <div class="point">
                        <p>ЭКГ с расшифровкой</p>
                        <p>2200 ₽</p>
                    </div>
                    <div class="point">
                        <p>Эхокардиология (УЗИ сердца)</p>
                        <p>4000 ₽</p>
                    </div>
                </div>
                <button type="button" class="accordion">Неврология</button>
                <div class="panel">
                    <div class="point">
                        <p>Консультация невролога</p>
                        <p>3200 ₽ </p>
                    </div>
                    <div class="point">
                        <p>УЗИ сосудов головы и шеи</p>
                        <p>4500 ₽ </p>
                    </div>
                </div>
                <button type="button" class="accordion">Гастроэнтерология</button>
                <div class="panel">
                    <div class="point">
                        <p>Консультация гастроэнтеролога</p>
                        <p>3300 ₽ </p>
                    </div>
                    <div class="point">
                        <p>Гастроскопия (ФГДС)</p>
                        <p>6500 ₽</p>
                    </div>
                </div>
                <button type="button" class="accordion">Лабораторная диагностика</button>
                <div class="panel">
                    <div class="point">
                        <p>Общий анализ крови</p>
                        <p>1200 ₽ </p>
                    </div>
                    <div class="point">
                        <p>Биохимический анализ крови (базовый)</p>
                        <p>2800 ₽ </p>
                    </div>
                </div>
            </div>
            <div class="button">
                <a class="commonBtn" href="./pages/services.php">смотреть все</a>
            </div>
            <img src="./img/svg/snake.svg" alt="">
        </div>
    </section>
    <section id="contacts">
        <div class="container">
            <h2>Контакты</h2>
            <div class="content">
                <div class="contactInfo">
                    <div class="point">
                        <img src="./img/svg/phoneCall.svg" alt="телефон"></img>
                        <p>8(904)322-12-12</p>
                    </div>
                    <div class="point">
                        <img src="./img/svg/mail.svg" alt="почта"></img>
                        <p>contact@kedrspb.ru</p>
                    </div>
                    <div class="point">
                        <img src="./img/svg/geo.svg" alt="адрес"></img>
                        <p>м. Лесная, ул. Ленина, д. 36 </p>
                    </div>
                    <div class="point">
                        <img src="./img/svg/clock.svg" alt="часы работы">
                        <div class="vert">
                            <p>пн-пт: 8:00-19:00</p>
                            <p>сб: 10:00-17:00</p>
                            <p>вс: выходной</p>
                        </div>
                    </div>
                </div>
                <div class="requisits">
                    <p>ООО “Кедр”</p>
                    <p>Юр. адрес: 123456, г.Санкт-Петербург, ул.Ленина, д.36</p>
                    <p>Лицензия № ЛО-77-01-012345 от 15.04.2020</p>
                    <div class="nums">
                        <div class="vert">
                            <p>ИНН: 7701234567</p>
                            <p>КПП: 770101001</p>
                            <p>ОГРН: 1187746123456</p>
                        </div>
                        <div class="vert">
                            <p>ОКПО: 12345678</p>
                            <p>ОКАТО: 45293598000</p>
                            <p>БИК: 044525222</p>
                        </div>
                    </div>
                </div>
            </div>
            <img src="./img/svg/decoLine1.svg" alt="">
            <img src="./img/svg/decoLine2.svg" alt="">
        </div>
    </section>
    <footer>
        <div class="container">
            <nav>
                <ul>
                    <li><a href="#aboutUs">о нас</a></li>
                    <li><a href="#specialists">специалисты</a></li>
                    <li><a href="#services">услуги</a></li>
                    <li><a href="#contacts">контакты</a></li>
                    <li><a href="#smartApp">как работает умная запись</a></li>
                </ul>
            </nav>
            <span></span>
            <div class="info">
                <div class="logoC">
                    <a class="logo" href="#hero">
                        <img src="./img/svg/logoFooter.svg" alt="логотип - кедр">
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
</body>
</html>