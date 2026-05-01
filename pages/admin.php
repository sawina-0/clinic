<?php
    session_start();
    require_once '../config.php';
    if(isLogged() && (isAdmin() || isDoctor()|| isStuff())){
        
    }
    else{
        header('Location: ../index.php');
        exit;
    }
?>
<script>
    window.isAdmin = <?php echo isAdmin() ? 'true' : 'false'; ?>;
    window.isDoctor = <?php echo isDoctor() ? 'true' : 'false'; ?>;
    window.isStuff = <?php echo isStuff() ? 'true' : 'false'; ?>;
</script>
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
    <script src="../js/toggle.js" defer></script>
    <script src="../js/hat.js" defer></script>
    <script src="../js/adminTabs.js" defer></script>
    <script src="../js/filters.js" defer></script>
    <script src="../js/customSelect.js" defer></script>
    <script src="../js/adminDateTime.js" defer></script>
    <script src="../js/maskAndError.js" defer></script>
    <title>Клиника Кедр - Админ-панель</title>
</head>
<body>
    <header>
        <div class="container hat hatAdmin">
            <a class="logo" href="../index.php#hero">
                <img src="../img/svg/logoGreen.svg" alt="логтип кедр">
            </a>
            <?if(isAdmin()): ?>
            <nav class="desctopNav">
                <ul>
                    <li><button class="tabBtn selected" data-section="users">пользователи</button></li>
                    <li><button class="tabBtn" data-section="services">услуги</button></li>
                    <li><button class="tabBtn" data-section="cabinets">кабинеты</button></li>
                    <li><button class="tabBtn" data-section="appointments">записи</button></li>
                    <li><button class="tabBtn" data-section="doctors">врачи</button></li>
                    <li><button class="tabBtn" data-section="doctorSchedule">график</button></li>
                </ul>
            </nav>
            <?elseif(isDoctor()): ?>
            <nav class="desktopNavDisplay">
                <ul>
                    <li><button class="tabBtn selected" data-section="appointments">записи</button></li>
                    <li><button class="tabBtn" data-section="diagnose">диагнозы</button></li>
                </ul>
            </nav>
            <?elseif(isStuff()): ?>
            <nav class="desktopNav">
                <ul>
                    <li><button class="tabBtn selected" data-section="appointments">записи</button></li>
                </ul>
            </nav>
            <?endif?>
                
        </div>
    </header>
    <?if(isAdmin()): ?>
    <div class="mobileMenu">
        <nav>
            <ul>
                <li><button onclick="toggleMenu()" class="tabBtn selected" data-section="users">пользователи</button></li>
                <li><button onclick="toggleMenu()" class="tabBtn" data-section="services">услуги</button></li>
                <li><button onclick="toggleMenu()" class="tabBtn" data-section="cabinets">кабинеты</button></li>
                <li><button onclick="toggleMenu()" class="tabBtn" data-section="appointments">записи</button></li>
                <li><button onclick="toggleMenu()" class="tabBtn" data-section="doctors">врачи</button></li>
                <li><button onclick="toggleMenu()" class="tabBtn" data-section="doctorSchedule">график</button></li>
            </ul>
        </nav>
    </div>
    <div class="burger" onclick="toggleMenu()">
        <span></span>
        <span></span>
        <span></span>
    </div>
    <?endif?>
    <main id="adminPanel">
        <div class="container">
            <div class="top">
                <div class="filters">
                    <!-- <div class="custom-select-wrapper">
                        <div class="custom-select-trigger">
                            <span>Выберите</span>
                            <img src="../img/svg/selectArrow.svg" alt="">
                        </div>
                        <div class="custom-select-dropdown">
                            <input type="text" class="search-input" placeholder="Поиск">
                            <div class="options-container">
                                
                            </div>
                        </div>
                    </div>
                    <input type="text" name="search" id="search" placeholder="Поиск услуг" value=""> -->
                </div>
                <button class="commonBtn">добавить</button>
            </div>
            <div class="cardContent" id="cardContent">
                <!-- <div class="userCard card">
                    <img src="../img/avatars/none.svg" alt="">
                    <div class="info">
                        <p>Иванов Иван Иванович</p>
                        <p>8(800)555-35-35</p>
                        <p>Пользователь</p>
                    </div>
                    <div class="btns">
                        <button class="editBtn"><img src="../img/svg/pencil.svg" alt="редактировать"></button>
                        <button class="deleteBtn"><img src="../img/svg/trash.svg" alt="удалить"></button>
                    </div>
                </div>
                <div class="serviceCard card">
                    <div class="info">
                        <p>ЭКГ с расшифровкой</p>
                        <p>(кардиология)</p>
                        <p>2200 ₽</p>
                    </div>
                    <div class="btns">
                        <button class="editBtn"><img src="../img/svg/pencil.svg" alt="редактировать"></button>
                        <button class="deleteBtn"><img src="../img/svg/trash.svg" alt="удалить"></button>
                    </div>
                </div>
                <div class="cabinetCard card">
                    <div class="info">
                        <p>Этаж: 3</p>
                        <p>Кабинет № 304</p>
                        <p>Фамилия И.О.</p>
                    </div>
                    <div class="btns">
                        <button class="editBtn"><img src="../img/svg/pencil.svg" alt="редактировать"></button>
                        <button class="deleteBtn"><img src="../img/svg/trash.svg" alt="удалить"></button>
                    </div>
                </div>
                <div class="appCard card">
                    <div class="info">
                        <div class="dateTime">
                            <p>30.03.2026</p>
                            <p>16:00</p>
                        </div>
                        <p>Пациент: Иванов И. И.</p>
                        <p>Врач: Фамилия И. О.</p>
                        <p>Услуга: ЭКГ с расшифровкой</p>
                        <p>Кабинет № 304</p>
                        <p>Статус: запланирована</p>
                    </div>
                    <div class="btns">
                        <button class="editBtn"><img src="../img/svg/pencil.svg" alt="редактировать"></button>
                        <button class="deleteBtn"><img src="../img/svg/trash.svg" alt="удалить"></button>
                    </div>
                </div>
                <div class="doctorCard card">
                    <img src="../img/avatars/none.svg" alt="">
                    <div class="info">
                        <p>Иванов Иван Иванович</p>
                        <p>Кардиолог</p>
                        <p>Стаж: 20 лет</p>
                        <p>8(800)555-35-35</p>
                    </div>
                    <div class="btns">
                        <button class="editBtn"><img src="../img/svg/pencil.svg" alt="редактировать"></button>
                        <button class="deleteBtn"><img src="../img/svg/trash.svg" alt="удалить"></button>
                    </div>
                </div>
                <div class="scheduleCard card">
                    <img src="../img/avatars/none.svg" alt="">
                    <div class="info">
                        <p>Иванов Иван Иванович</p>
                        <p>Кардиолог</p>
                        <p>График: пн, ср, пт</p>
                    </div>
                    <div class="btns">
                        <button class="addBtn">+</button>
                        <button class="editBtn"><img src="../img/svg/pencil.svg" alt="редактировать"></button>
                        <button class="deleteBtn"><img src="../img/svg/trash.svg" alt="удалить"></button>
                    </div>
                </div>
                <div class="diagnoseCard card">
                    <div class="info">
                        <p>30.03.2026</p>
                        <p>Пациент: Иванов И. И. </p>
                        <p>Врач: Фамилия И. О.</p>
                        <p>Диагноз: Тахикардия</p>
                    </div>
                    <div class="btns">
                        <button class="editBtn"><img src="../img/svg/pencil.svg" alt="редактировать"></button>
                        <button class="deleteBtn"><img src="../img/svg/trash.svg" alt="удалить"></button>
                    </div>
                </div>
                </div> -->
        </div>
    </main>
    <div id="delete-popup-overlay" class="popupOverlay" onclick="hidePopup('delete-popup')">
        <div id="delete-popup" class="popupContainer">
            <img src="../img/svg/cross.svg" alt="крестик - закрыть окно" onclick="hidePopup('delete-popup')">
            <h2>Удалить?</h2>
            <p>Все связанные данные также удалятся</p>
            <button class="delete" id="deleteBtn">удалить</button>
        </div>
    </div>
    <div id="edit-popup-overlay" class="popupOverlay" onclick="hidePopup('edit-popup')">
        <div id="edit-popup" class="popupContainer">
            <h2>Редактировать?</h2>
            <img src="../img/svg/cross.svg" alt="крестик - закрыть окно" onclick="hidePopup('edit-popup')">
            <div id="editUsers" class="popupContent">
                <div class="avatar-wrapper">
                    <img src="../img/avatars/none.svg" alt="" id="editUserAvatar">
                    <button type="button" id="deleteUserAvatarBtn" class="delete-avatar-btn" style="display: none;"><img src="../img/svg/trashRed.svg" alt="удалить аватарку"></button>
                </div>
                <div class="field">
                    <label for="surnameEdit">Фамилия:</label>
                    <input type="text" name="surnameEdit" id="surnameEdit" required>
                </div>
                <div class="field">
                    <label for="nameEdit">Имя:</label>
                    <input type="text" name="nameEdit" id="nameEdit" required>
                </div>
                <div class="field">
                    <label for="secnameEdit">Отчество:</label>
                    <input type="text" name="secnameEdit" id="secnameEdit">
                </div>
                <div class="field">
                    <span>Роль:</span>
                    <div class="custom-select-wrapper">
                        <div class="custom-select-trigger">
                            <span>Выберите роль</span>
                            <img src="../img/svg/selectArrow.svg" alt="">
                        </div>
                        <div class="custom-select-dropdown">
                            <input type="text" class="search-input" placeholder="Поиск">
                            <div class="options-container">
                                <!-- роль пользователя. изначальная стоит выбраной сразу -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="field">
                    <label for="phoneEdit">Телефон:</label>
                    <input type="tel" name="phoneEdit" id="phoneEdit" required>
                </div>
                <input type="file" id="photoEdit" accept="image/*" style="display: none;">
            </div>
            <div id="editServices" class="popupContent">
                <div class="field">
                    <label for="serviceNameEdit">Название:</label>
                    <input type="text" name="serviceNameEdit" id="serviceNameEdit" required>
                </div>
                <div class="field">
                    <span>Направление:</span>
                    <div class="custom-select-wrapper">
                        <div class="custom-select-trigger">
                            <span>Выберите направление</span>
                            <img src="../img/svg/selectArrow.svg" alt="">
                        </div>
                        <div class="custom-select-dropdown">
                            <input type="text" class="search-input" placeholder="Поиск">
                            <div class="options-container">
                                <!-- направление. изначальное стоит выбрано сразу -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="field">
                    <label for="priceEdit">Цена:</label>
                    <input type="number" name="priceEdit" id="priceEdit" required>
                </div>
                <div class="field">
                    <span>Доступность:</span>
                    <div class="radios">
                        <label><input type="radio" name="is_public" value="1" checked> доступна для записи</label>
                        <label><input type="radio" name="is_public" value="0"> только по назначению</label>
                    </div>
                </div>
            </div>
            <div id="editCabinets" class="popupContent">
                <div class="field">
                    <label for="floorEdit">Этаж:</label>
                    <input type="number" name="floorEdit" id="floorEdit" required>
                </div>
                <div class="field">
                    <label for="numberEdit">Номер кабинета:</label>
                    <input type="number" name="numberEdit" id="numberEdit" required>
                </div>
            </div>
            <div id="editApps" class="popupContent">
                <div id="stepDateEdit">
                    <button type="button" id="showTimeBtn">Изменить время</button>
                    <button type="button" id="backToInfo">Вернуться</button>
                    <input type="text" id="dateInput" class="calendar" placeholder="Выберите дату">
                </div>
                <div id="stepTimeEdit">
                    <div class="time">
                        <div class="time-header">
                            <button class="back-to-date"><</button>
                            <span class="selected-date-display"></span>
                        </div>
                        <div class="time-grid"></div>
                    </div>
                </div>
                <div id="stepInfoEdit">
                    <button type="button" id="showDateBtn">Изменить дату</button>
                    <small>при смене специалиста не забудьте так же сменить дату и время</small>
                    <div class="field">
                        <span>Пациент:</span>
                        <div class="custom-select-wrapper">
                            <div class="custom-select-trigger">
                                <span>Выберите пациента</span>
                                <img src="../img/svg/selectArrow.svg" alt="">
                            </div>
                            <div class="custom-select-dropdown">
                                <input type="text" class="search-input" placeholder="Поиск">
                                <div class="options-container">
                                    <!-- пользователь. изначальный стоит выбраным сразу -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="field">
                        <span>Специалист:</span>
                        <div class="custom-select-wrapper">
                            <div class="custom-select-trigger">
                                <span>Выберите специалиста</span>
                                <img src="../img/svg/selectArrow.svg" alt="">
                            </div>
                            <div class="custom-select-dropdown">
                                <input type="text" class="search-input" placeholder="Поиск">
                                <div class="options-container">
                                    <!-- врач. изначальный стоит выбраным сразу -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="field">
                        <span>Услуга:</span>
                        <div class="custom-select-wrapper">
                            <div class="custom-select-trigger">
                                <span>Выберите услугу</span>
                                <img src="../img/svg/selectArrow.svg" alt="">
                            </div>
                            <div class="custom-select-dropdown">
                                <input type="text" class="search-input" placeholder="Поиск">
                                <div class="options-container">
                                    <!-- услуга. изначальная стоит выбраной сразу -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="field">
                        <span>Статус:</span>
                        <div class="custom-select-wrapper">
                            <div class="custom-select-trigger">
                                <span>Выберите статус</span>
                                <img src="../img/svg/selectArrow.svg" alt="">
                            </div>
                            <div class="custom-select-dropdown">
                                <input type="text" class="search-input" placeholder="Поиск">
                                <div class="options-container">
                                    <!-- статус. изначальный стоит выбраным сразу -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="editDoctors" class="popupContent">
                <div class="field">
                    <span>Роль:</span>
                    <div class="custom-select-wrapper">
                        <div class="custom-select-trigger">
                            <span>Выберите роль</span>
                            <img src="../img/svg/selectArrow.svg" alt="">
                        </div>
                        <div class="custom-select-dropdown">
                            <input type="text" class="search-input" placeholder="Поиск">
                            <div class="options-container">
                                
                            </div>
                        </div>
                    </div>
                </div>
                <div class="field">
                    <span>Специальность:</span>
                    <div class="custom-select-wrapper">
                        <div class="custom-select-trigger">
                            <span>Выберите специальность</span>
                            <img src="../img/svg/selectArrow.svg" alt="">
                        </div>
                        <div class="custom-select-dropdown">
                            <input type="text" class="search-input" placeholder="Поиск">
                            <div class="options-container">
                                
                            </div>
                        </div>
                    </div>
                </div>
                <div class="field">
                    <span>Кабинет:</span>
                    <div class="custom-select-wrapper">
                        <div class="custom-select-trigger">
                            <span>Выберите кабинет</span>
                            <img src="../img/svg/selectArrow.svg" alt="">
                        </div>
                        <div class="custom-select-dropdown">
                            <input type="text" class="search-input" placeholder="Поиск">
                            <div class="options-container">
                                
                            </div>
                        </div>
                    </div>
                </div>
                <div class="field">
                    <label for="expEdit">Стаж:</label>
                    <input type="number" name="expEdit" id="expEdit" required>
                </div>
            </div>
            <div id="editSchedule" class="popupContent">
                <p id="fullName"></p>
                <fieldset>
                    <legend>График:</legend>
                    <div class="cb">
                        <label>
                            Пн:
                            <input type="checkbox" name="mon" value="1">
                        </label>
                        <label>
                            Вт:
                            <input type="checkbox" name="tue" value="2">
                        </label>
                        <label>
                            Ср:
                            <input type="checkbox" name="wed" value="3">
                        </label>
                        <label>
                            Чт:
                            <input type="checkbox" name="thu" value="4">
                        </label>
                        <label>
                            Пт:
                            <input type="checkbox" name="fri" value="5">
                        </label>
                        <label>
                            Сб:
                            <input type="checkbox" name="sat" value="6">
                        </label>
                    </div>
                </fieldset>
            </div>
            <div id="editDiagnose" class="popupContent">
                <input type="text" id="dateInput" class="calendar" placeholder="Выберите дату">
                <div class="field">
                    <span>Пациент:</span>
                    <div class="custom-select-wrapper">
                        <div class="custom-select-trigger">
                            <span>Выберите пациента</span>
                            <img src="../img/svg/selectArrow.svg" alt="">
                        </div>
                        <div class="custom-select-dropdown">
                            <input type="text" class="search-input" placeholder="Поиск">
                            <div class="options-container">
                                <!-- пользователь. изначальный стоит выбраным сразу -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="field">
                    <label for="diagnoseEdit">Диагноз:</label>
                    <input type="text" name="diagnoseEdit" id="diagnoseEdit" required>
                </div>
            </div>
            <button id="editBtn" class="edit">сохранить</button>
        </div>
    </div>
    <div id="add-popup-overlay" class="popupOverlay" onclick="hidePopup('add-popup')">
        <div id="add-popup" class="popupContainer">
            <h2>Добавить?</h2>
            <img src="../img/svg/cross.svg" alt="крестик - закрыть окно" onclick="hidePopup('add-popup')">
            <div id="addServices" class="popupContent">
                <div class="field">
                    <label for="serviceNameAdd">Название:</label>
                    <input type="text" name="serviceNameAdd" id="serviceNameAdd" required>
                </div>
                <div class="field">
                    <span>Направление:</span>
                    <div class="custom-select-wrapper">
                        <div class="custom-select-trigger">
                            <span>Выберите направление</span>
                            <img src="../img/svg/selectArrow.svg" alt="">
                        </div>
                        <div class="custom-select-dropdown">
                            <input type="text" class="search-input" placeholder="Поиск">
                            <div class="options-container">
                                
                            </div>
                        </div>
                    </div>
                </div>
                <div class="field">
                    <label for="priceAdd">Цена:</label>
                    <input type="number" name="priceAdd" id="priceAdd" required>
                </div>
                <div class="field">
                    <span>Доступность:</span>
                    <div class="radios">
                        <label><input type="radio" name="is_public" value="1" checked> доступна для записи</label>
                        <label><input type="radio" name="is_public" value="0"> только по назначению</label>
                    </div>
                </div>
            </div>
            <div id="addCabinets" class="popupContent">
                <div class="field">
                    <label for="floorAdd">Этаж:</label>
                    <input type="number" name="floorAdd" id="floorAdd" min="1" required>
                </div>
                <div class="field">
                    <label for="numberAdd">Номер кабинета:</label>
                    <input type="number" name="numberAdd" id="numberAdd" required>
                </div>
            </div>
            <div id="addApps" class="popupContent">
                <div id="stepDateAdd">
                    <input type="text" id="dateInput" class="calendar" placeholder="Выберите дату">
                </div>
                <div id="stepTimeAdd">
                    <div class="time">
                        <div class="time-header">
                            <button class="back-to-date"><</button>
                            <span class="selected-date-display"></span>
                        </div>
                        <div class="time-grid"></div>
                    </div>
                </div>
                <div class="field">
                    <span>Пациент:</span>
                    <div class="custom-select-wrapper">
                        <div class="custom-select-trigger">
                            <span>Выберите пациента</span>
                            <img src="../img/svg/selectArrow.svg" alt="">
                        </div>
                        <div class="custom-select-dropdown">
                            <input type="text" class="search-input" placeholder="Поиск">
                            <div class="options-container">
                                
                            </div>
                        </div>
                    </div>
                </div>
                <div class="field">
                    <span>Специалист:</span>
                    <div class="custom-select-wrapper">
                        <div class="custom-select-trigger">
                            <span>Выберите специалиста</span>
                            <img src="../img/svg/selectArrow.svg" alt="">
                        </div>
                        <div class="custom-select-dropdown">
                            <input type="text" class="search-input" placeholder="Поиск">
                            <div class="options-container">
                                
                            </div>
                        </div>
                    </div>
                </div>
                <div class="field">
                    <span>Услуга:</span>
                    <div class="custom-select-wrapper">
                        <div class="custom-select-trigger">
                            <span>Выберите услугу</span>
                            <img src="../img/svg/selectArrow.svg" alt="">
                        </div>
                        <div class="custom-select-dropdown">
                            <input type="text" class="search-input" placeholder="Поиск">
                            <div class="options-container">
                                
                            </div>
                        </div>
                    </div>
                </div>
                <div class="field">
                    <span>Статус:</span>
                    <div class="custom-select-wrapper">
                        <div class="custom-select-trigger">
                            <span>Выберите статус</span>
                            <img src="../img/svg/selectArrow.svg" alt="">
                        </div>
                        <div class="custom-select-dropdown">
                            <input type="text" class="search-input" placeholder="Поиск">
                            <div class="options-container">
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="addDoctors" class="popupContent">
                <div class="field">
                    <span>Пользователь системы:</span>
                    <div class="custom-select-wrapper">
                        <div class="custom-select-trigger">
                            <span>Выберите пользователя</span>
                            <img src="../img/svg/selectArrow.svg" alt="">
                        </div>
                        <div class="custom-select-dropdown">
                            <input type="text" class="search-input" placeholder="Поиск">
                            <div class="options-container">
                                
                            </div>
                        </div>
                    </div>
                </div>
                <div class="field">
                    <span>Роль:</span>
                    <div class="custom-select-wrapper">
                        <div class="custom-select-trigger">
                            <span>Выберите роль</span>
                            <img src="../img/svg/selectArrow.svg" alt="">
                        </div>
                        <div class="custom-select-dropdown">
                            <input type="text" class="search-input" placeholder="Поиск">
                            <div class="options-container">
                                
                            </div>
                        </div>
                    </div>
                </div>
                <div class="field">
                    <span>Специальность:</span>
                    <div class="custom-select-wrapper">
                        <div class="custom-select-trigger">
                            <span>Выберите специальность</span>
                            <img src="../img/svg/selectArrow.svg" alt="">
                        </div>
                        <div class="custom-select-dropdown">
                            <input type="text" class="search-input" placeholder="Поиск">
                            <div class="options-container">
                                
                            </div>
                        </div>
                    </div>
                </div>
                <div class="field">
                    <span>Кабинет:</span>
                    <div class="custom-select-wrapper">
                        <div class="custom-select-trigger">
                            <span>Выберите кабинет</span>
                            <img src="../img/svg/selectArrow.svg" alt="">
                        </div>
                        <div class="custom-select-dropdown">
                            <input type="text" class="search-input" placeholder="Поиск">
                            <div class="options-container">
                                
                            </div>
                        </div>
                    </div>
                </div>
                <div class="field">
                    <label for="expAdd">Стаж:</label>
                    <input type="number" name="expAdd" id="expAdd" required>
                </div>
            </div>
            <div id="addSchedule" class="popupContent">
                <p id="fullName"></p>
                <fieldset>
                    <legend>График:</legend>
                    <div class="cb">
                        <label>
                            Пн:
                            <input type="checkbox" name="mon" value="1">
                        </label>
                        <label>
                            Вт:
                            <input type="checkbox" name="tue" value="2">
                        </label>
                        <label>
                            Ср:
                            <input type="checkbox" name="wed" value="3">
                        </label>
                        <label>
                            Чт:
                            <input type="checkbox" name="thu" value="4">
                        </label>
                        <label>
                            Пт:
                            <input type="checkbox" name="fri" value="5">
                        </label>
                        <label>
                            Сб:
                            <input type="checkbox" name="sat" value="6">
                        </label>
                    </div>
                </fieldset>
            </div>
            <div id="addDiagnose" class="popupContent">
                <input type="text" id="dateInput" class="calendar" placeholder="Выберите дату">
                <div class="field">
                    <span>Пациент:</span>
                    <div class="custom-select-wrapper">
                        <div class="custom-select-trigger">
                            <span>Выберите пациента</span>
                            <img src="../img/svg/selectArrow.svg" alt="">
                        </div>
                        <div class="custom-select-dropdown">
                            <input type="text" class="search-input" placeholder="Поиск">
                            <div class="options-container">
                                <!-- пользователь. изначальный стоит выбраным сразу -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="field">
                    <span>Специалист:</span>
                    <div class="custom-select-wrapper">
                        <div class="custom-select-trigger">
                            <span>Выберите специалиста</span>
                            <img src="../img/svg/selectArrow.svg" alt="">
                        </div>
                        <div class="custom-select-dropdown">
                            <input type="text" class="search-input" placeholder="Поиск">
                            <div class="options-container">
                                <!-- врач. изначальный стоит выбраным сразу -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="field">
                    <label for="diagnoseAdd">Диагноз:</label>
                    <input type="text" name="diagnoseAdd" id="diagnoseAdd" required>
                </div>
            </div>
            <button id="addBtn" class="add">добавить</button>
        </div>
    </div>
</body>
</html>