document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('.tabBtn');
    const container = document.querySelector('.cardContent');
    const filterContainer = document.querySelector('.filters');
    const addBtn = document.querySelector('.top .commonBtn');

    let currentSection = 'users';

    tabs.forEach(tab => {
        tab.addEventListener('click', function () {
            const section = this.dataset.section;
            if (!section) return;

            tabs.forEach(t => t.classList.remove('selected'));
            this.classList.add('selected');

            currentSection = section;
            loadSection(section);
        });
    });
    function formatPhone(phone) {
        if (!phone) return '';
        const cleaned = phone.replace(/\D/g, '');
        if (cleaned.length === 11) {
            return cleaned.replace(/(\d{1})(\d{3})(\d{3})(\d{2})(\d{2})/, '+$1 ($2) $3-$4-$5');
        }
        return phone;
    }

    function loadSection(section) {
        updateFilters(section);

        if (section === 'users' || section === 'doctorSchedule') {
            addBtn.style.display = 'none';
        } else {
            addBtn.style.display = 'block';
        }

        fetch(`../crud/get${capitalize(section)}.php`)
            .then(response => response.text())
            .then(data => {
                container.innerHTML = data;
            });
    }

    function updateFilters(section) {
        let filterHtml = '';
        filterContainer.innerHTML = '';

        if (section === 'users') {
            filterHtml = `
                <div class="custom-select-wrapper">
                    <div class="custom-select-trigger">
                        <span>Все роли</span>
                        <img src="../img/svg/selectArrow.svg" alt="">
                    </div>
                    <div class="custom-select-dropdown">
                        <input type="text" class="search-input" placeholder="Поиск">
                        <div class="options-container">
                            <div class="filter-option" data-value="">Все роли</div>
                            <div class="filter-option" data-value="Пользователь">Пользователь</div>
                            <div class="filter-option" data-value="Доктор">Врач</div>
                            <div class="filter-option" data-value="Персонал">Персонал</div>
                            <div class="filter-option" data-value="Администратор">Админ</div>
                            <div class="filter-option" data-value="Заблокирован">Заблокирован</div>
                        </div>
                    </div>
                </div>
            `;
        } else if (section === 'appointments') {
            filterHtml = `
                <div class="custom-select-wrapper">
                    <div class="custom-select-trigger">
                        <span>Все статусы</span>
                        <img src="../img/svg/selectArrow.svg" alt="">
                    </div>
                    <div class="custom-select-dropdown">
                        <input type="text" class="search-input" placeholder="Поиск">
                        <div class="options-container">
                            <div class="filter-option" data-value="">Все статусы</div>
                            <div class="filter-option" data-value="запланирован">Запланирован</div>
                            <div class="filter-option" data-value="завершён">Завершён</div>
                            <div class="filter-option" data-value="отменён">Отменён</div>
                        </div>
                    </div>
                </div>
            `;
        } else if (section === 'diagnose') {
            filterHtml = '';
        } else if (section === 'symptomes') {
            filterHtml = '';
        } else {
            filterHtml = `
                <div class="custom-select-wrapper">
                    <div class="custom-select-trigger">
                        <span>Все отделения</span>
                        <img src="../img/svg/selectArrow.svg" alt="">
                    </div>
                    <div class="custom-select-dropdown">
                        <input type="text" class="search-input" placeholder="Поиск">
                        <div class="options-container">
                            <!-- сюда подгрузятся направления из БД -->
                        </div>
                    </div>
                </div>
            `;
        }

        const form = document.createElement('form');
        form.className = 'filters';
        form.dataset.target = 'cardContent';
        form.dataset.url = `../crud/get${capitalize(section)}.php`;
        form.innerHTML = filterHtml + `<input type="hidden" name="section" value="${section}">` + `<input type="text" name="search" id="search" placeholder="Поиск" value="">`;
        filterContainer.appendChild(form);
        if (typeof window.initFilters === 'function') {
            window.initFilters();
        }
        const optionsContainer = form.querySelector('.options-container');
        if (optionsContainer && section !== 'users' && section !== 'appointments' && section !== 'diagnose') {
            fetch('../crud/getSelectOptions.php?type=directions')
                .then(response => response.text())
                .then(data => {
                    optionsContainer.innerHTML = '<div class="filter-option" data-value="">Все отделения</div>' + data;
                });
        }

        const filterForms = document.querySelectorAll('.filters');
        filterForms.forEach(form => {
            // Здесь можно вызвать логику из filters.js, но проще — диспатчим событие
            const event = new Event('change', { bubbles: true });
            form.dispatchEvent(event);
        });
    }

    function capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    if (window.isAdmin) {
        loadSection('users');
    } else if (window.isDoctor || window.isStuff) {
        loadSection('appointments');
    }

    // Открытие попапа добавления
    addBtn.addEventListener('click', function () {
        // Показываем попап
        showPopup('add-popup');

        // Показываем нужный блок
        const currentSection = document.querySelector('.tabBtn.selected').dataset.section;
        const addBlocks = document.querySelectorAll('#add-popup .popupContent');
        addBlocks.forEach(block => block.style.display = 'none');

        if (currentSection === 'services') {
            document.getElementById('addServices').style.display = 'flex';

            // Очищаем поля
            document.getElementById('serviceNameAdd').value = '';
            document.getElementById('priceAdd').value = '';
            document.querySelector('input[name="is_public"][value="1"]').checked = true;

            // Сбрасываем селект направления
            const directionWrapper = document.querySelector('#addServices .custom-select-wrapper');
            const triggerSpan = directionWrapper.querySelector('.custom-select-trigger span');
            triggerSpan.textContent = 'Выберите направление';
            directionWrapper.dataset.value = '';

            const optionsContainer = document.querySelector('#addServices .options-container');
            fetch('../crud/getSelectOptions.php?type=directions')
                .then(response => response.text())
                .then(data => {
                    optionsContainer.innerHTML = data;
                    // Инициализируем новые селекты
                    if (typeof window.initCustomSelects === 'function') {
                        window.initCustomSelects();
                    }
                });
        } else if (currentSection === 'cabinets') {
            document.getElementById('addCabinets').style.display = 'flex';

            // Очищаем поля
            document.getElementById('floorAdd').value = '';
            document.getElementById('numberAdd').value = '';
        } else if (currentSection === 'appointments') {
            document.getElementById('addApps').style.display = 'flex';

            // Очищаем дату и время
            const dateInput = document.querySelector('#addApps #dateInput');
            if (dateInput) dateInput.value = '';
            const timeGrid = document.querySelector('#addApps .time-grid');
            if (timeGrid) timeGrid.innerHTML = '';

            // Сбрасываем селекты
            const wrappers = document.querySelectorAll('#addApps .custom-select-wrapper');
            wrappers.forEach(wrapper => {
                const triggerSpan = wrapper.querySelector('.custom-select-trigger span');
                triggerSpan.textContent = 'Выберите';
                wrapper.dataset.value = '';
            });

            let doctorUrl = '../crud/getSelectOptions.php?type=doctors';
            if (window.isDoctor) {
                doctorUrl = '../crud/getSelectOptions.php?type=doctorsForDoctor';
            } else if (window.isStuff) {
                doctorUrl = '../crud/getSelectOptions.php?type=currentDoctor';
            }

            // Загружаем пациентов
            const patientContainer = document.querySelector('#addApps .options-container:first-of-type');
            fetch('../crud/getSelectOptions.php?type=patients')
                .then(response => response.text())
                .then(data => {
                    patientContainer.innerHTML = data;
                });

            // Загружаем врачей
            const doctorContainer = document.querySelectorAll('#addApps .options-container')[1];
            fetch(doctorUrl)
                .then(response => response.text())
                .then(data => {
                    doctorContainer.innerHTML = data;
                });

            // Загружаем услуги
            const serviceContainer = document.querySelectorAll('#addApps .options-container')[2];
            fetch('../crud/getSelectOptions.php?type=services')
                .then(response => response.text())
                .then(data => {
                    serviceContainer.innerHTML = data;
                });

            // Статусы
            const statusContainer = document.querySelectorAll('#addApps .options-container')[3];
            statusContainer.innerHTML = `
                <div class="filter-option" data-value="запланирован">Запланирован</div>
                <div class="filter-option" data-value="завершён">Завершён</div>
                <div class="filter-option" data-value="отменён">Отменён</div>
            `;

            // Скрываем календарь и время до выбора врача
            const dateSection = document.querySelector('#addApps #stepDateAdd');
            const timeSection = document.querySelector('#addApps #stepTimeAdd');
            if (dateSection) dateSection.style.display = 'none';
            if (timeSection) timeSection.style.display = 'none';

            if (typeof window.initCustomSelects === 'function') {
                window.initCustomSelects();
            }

            // Элементы для календаря и времени
            const doctorWrapper = document.querySelectorAll('#addApps .custom-select-wrapper')[1];

            let flatpickrInstance = null;

            // Наблюдаем за выбором врача
            if (doctorWrapper && dateSection) {
                const observer = new MutationObserver(() => {
                    const doctorId = doctorWrapper.dataset.value;

                    if (doctorId) {
                        dateSection.style.display = 'block';

                        // Удаляем старый календарь
                        if (flatpickrInstance && flatpickrInstance[0]) {
                            flatpickrInstance[0].destroy();
                            flatpickrInstance = null;
                        }

                        // Загружаем расписание и создаём календарь
                        fetch(`../func/getDoctorSchedule.php?doctor_id=${doctorId}`)
                            .then(response => response.json())
                            .then(schedule => {
                                window.doctorSchedule = schedule;

                                // Создаём новый календарь
                                flatpickrInstance = window.adminDateTime.initFlatpickr(dateInput, {
                                    minDate: 'today',
                                    maxDate: new Date().fp_incr(60),
                                    disable: [
                                        function (date) {
                                            if (!schedule || schedule.length === 0) return true;
                                            return !schedule.includes(date.getDay());
                                        }
                                    ],
                                    onChange: function (selectedDates, dateStr) {
                                        if (doctorId && dateStr) {
                                            const [day, month, year] = dateStr.split('.');
                                            const dateObj = new Date(year, month - 1, day);
                                            const dateDisplay = document.querySelector('#addApps .selected-date-display');
                                            if (dateDisplay) {
                                                dateDisplay.textContent = dateObj.toLocaleDateString('ru-RU', {
                                                    weekday: 'short',
                                                    day: 'numeric',
                                                    month: 'numeric',
                                                    year: 'numeric'
                                                });
                                            }
                                            if (dateSection) dateSection.style.display = 'none';
                                            if (timeSection) timeSection.style.display = 'block';
                                            document.querySelector('#addApps .time-header')?.addEventListener('click', function () {
                                                if (dateSection) dateSection.style.display = 'block';
                                                if (timeSection) timeSection.style.display = 'none';
                                            });
                                            window.adminDateTime.initTimeSlots(timeGrid, doctorId, dateStr, (time) => {
                                                window.selectedTime = time;
                                                if (timeSection) timeSection.style.display = 'block';
                                                document.getElementById('addBtn').disabled = false;
                                            });
                                        }
                                    }
                                });
                            });
                    } else {
                        dateSection.style.display = 'none';
                        if (timeSection) timeSection.style.display = 'none';
                    }
                });
                observer.observe(doctorWrapper, { attributes: true, attributeFilter: ['data-value'] });
            }
        } else if (currentSection === 'doctors') {
            document.getElementById('addDoctors').style.display = 'flex';

            // Очищаем стаж
            document.getElementById('expAdd').value = '';

            // Сбрасываем селекты
            const wrappers = document.querySelectorAll('#addDoctors .custom-select-wrapper');
            wrappers.forEach(wrapper => {
                const triggerSpan = wrapper.querySelector('.custom-select-trigger span');
                triggerSpan.textContent = 'Выберите';
                wrapper.dataset.value = '';
            });

            // Пользователи
            const userContainer = document.querySelector('#addDoctors .options-container:first-of-type');
            fetch('../crud/getSelectOptions.php?type=usersForDoctor')
                .then(response => response.text())
                .then(data => userContainer.innerHTML = data);

            // Роли
            const roleContainer = document.querySelectorAll('#addDoctors .options-container')[1];
            fetch('../crud/getSelectOptions.php?type=rolesForDoctor')
                .then(response => response.text())
                .then(data => roleContainer.innerHTML = data);

            // Специальности (направления)
            const directionContainer = document.querySelectorAll('#addDoctors .options-container')[2];
            fetch('../crud/getSelectOptions.php?type=directions')
                .then(response => response.text())
                .then(data => directionContainer.innerHTML = data);

            // Кабинеты
            const cabinetContainer = document.querySelectorAll('#addDoctors .options-container')[3];
            fetch('../crud/getSelectOptions.php?type=cabinets')
                .then(response => response.text())
                .then(data => cabinetContainer.innerHTML = data);

            if (typeof window.initCustomSelects === 'function') {
                window.initCustomSelects();
            }
        } else if (currentSection === 'doctorSchedule') {
            document.getElementById('addSchedule').style.display = 'flex';
        } else if (currentSection === 'diagnose') {
            document.getElementById('addDiagnose').style.display = 'flex';

            // Очищаем дату и диагноз
            const dateInput = document.querySelector('#addDiagnose #dateInput');
            if (dateInput) dateInput.value = '';
            document.getElementById('diagnoseAdd').value = '';
            const fileInput = document.getElementById('diagnoseFileAdd');
            if (fileInput) fileInput.value = '';


            // Сбрасываем селект пациента
            const patientWrapper = document.querySelector('#addDiagnose .custom-select-wrapper');
            if (patientWrapper) {
                const triggerSpan = patientWrapper.querySelector('.custom-select-trigger span');
                triggerSpan.textContent = 'Выберите пациента';
                patientWrapper.dataset.value = '';
            }

            // Загружаем пациентов
            const patientContainer = document.querySelector('#addDiagnose .options-container:first-of-type');
            fetch('../crud/getSelectOptions.php?type=patients')
                .then(response => response.text())
                .then(data => patientContainer.innerHTML = data);

            // Врач — только текущий (селект не нужен, но для единообразия оставим один)
            // Можно вообще убрать селект врача, так как он один
            const doctorContainer = document.querySelectorAll('#addDiagnose .options-container')[1];
            if (doctorContainer) {
                fetch('../crud/getSelectOptions.php?type=currentDoctor')
                    .then(response => response.text())
                    .then(data => {
                        doctorContainer.innerHTML = data;
                        if (typeof window.initCustomSelects === 'function') {
                            window.initCustomSelects();
                        }
                    });
            }

            // Инициализируем календарь с расписанием врача
            if (dateInput) {
                fetch('../func/getDoctorSchedule.php')
                    .then(response => response.json())
                    .then(schedule => {
                        flatpickr(dateInput, {
                            locale: 'ru',
                            dateFormat: 'd.m.Y',
                            maxDate: 'today',
                            disable: [
                                function (date) {
                                    if (!schedule || schedule.length === 0) return true;
                                    return !schedule.includes(date.getDay());
                                }
                            ]
                        });
                    });
            }
        } else if (currentSection === 'symptomes') {
            document.getElementById('addSymp').style.display = 'flex';

            // Очищаем поля
            document.getElementById('sympAdd').value = '';
            document.getElementById('priorityAdd').value = '';
        }
    });
    //расписание
    document.addEventListener('click', function (e) {
        const addScheduleBtn = e.target.closest('.scheduleCard .addBtn');
        if (!addScheduleBtn) return;

        // Скрываем все блоки в модалке
        document.querySelectorAll('#add-popup .popupContent').forEach(block => {
            block.style.display = 'none';
        });
        // Показываем блок графика
        document.getElementById('addSchedule').style.display = 'flex';
        const card = addScheduleBtn.closest('.scheduleCard');
        const doctorName = card.querySelector('.info p:first-child').textContent;
        const doctorId = addScheduleBtn.dataset.id;

        document.querySelector('#addSchedule #fullName').textContent = doctorName;
        document.querySelector('#addSchedule').dataset.doctorId = doctorId;
        document.querySelectorAll('#addSchedule .cb input').forEach(cb => cb.checked = false);

        showPopup('add-popup');
    });

    // Сохранение
    document.getElementById('addBtn').addEventListener('click', async function () {
        const currentSection = document.querySelector('.tabBtn.selected').dataset.section;
        // Сохранение услуги
        if (currentSection === 'services') {
            const name = document.getElementById('serviceNameAdd').value;
            const directionWrapper = document.querySelector('#addServices .custom-select-wrapper');
            const directionId = directionWrapper.dataset.value;
            const price = document.getElementById('priceAdd').value;
            const is_public = document.querySelector('input[name="is_public"]:checked').value;

            if (!name || !directionId || !price) {
                customAlert('Заполните все поля');
                return;
            }

            try {
                const response = await fetch('../crud/addService.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ name, direction_id: directionId, price, is_public })
                });
                const result = await response.json();

                if (result.success) {
                    customAlert('Услуга добавлена');
                    hidePopup('add-popup');
                    // Перезагружаем текущую секцию
                    loadSection(currentSection);
                } else {
                    customAlert(result.message);
                }
            } catch (error) {
                customAlert('Ошибка при добавлении');
            }
        }//сохранение каба
        else if (currentSection === 'cabinets') {
            const floor = document.getElementById('floorAdd').value;
            const number = document.getElementById('numberAdd').value;

            if (!floor || !number) {
                customAlert('Заполните все поля');
                return;
            }

            const response = await fetch('../crud/addCabinet.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ floor, number })
            });
            const result = await response.json();

            if (result.success) {
                customAlert('Кабинет добавлен');
                hidePopup('add-popup');
                loadSection(currentSection);
            } else {
                customAlert(result.message);
            }
        }//сохранение записи
        else if (currentSection === 'appointments') {
            const patientWrapper = document.querySelectorAll('#addApps .custom-select-wrapper')[0];
            const doctorWrapper = document.querySelectorAll('#addApps .custom-select-wrapper')[1];
            const serviceWrapper = document.querySelectorAll('#addApps .custom-select-wrapper')[2];
            const statusWrapper = document.querySelectorAll('#addApps .custom-select-wrapper')[3];

            const patient_id = patientWrapper.dataset.value;
            const doctor_id = doctorWrapper.dataset.value;
            const service_id = serviceWrapper.dataset.value;
            const date = document.querySelector('#addApps #dateInput').value;
            const time = document.querySelector('#addApps .time-slot.selected')?.dataset.time;
            const status = statusWrapper.dataset.value;

            if (!patient_id || !doctor_id || !service_id || !date || !time) {
                customAlert('Заполните все поля');
                return;
            }

            const response = await fetch('../crud/addAppointment.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ patient_id, doctor_id, service_id, date, time, status })
            });
            const result = await response.json();

            if (result.success) {
                customAlert('Запись добавлена');
                hidePopup('add-popup');
                loadSection(currentSection);
            } else {
                customAlert(result.message);
            }
        }//сохранение докторов
        else if (currentSection === 'doctors') {
            const userWrapper = document.querySelectorAll('#addDoctors .custom-select-wrapper')[0];
            const roleWrapper = document.querySelectorAll('#addDoctors .custom-select-wrapper')[1];
            const directionWrapper = document.querySelectorAll('#addDoctors .custom-select-wrapper')[2];
            const cabinetWrapper = document.querySelectorAll('#addDoctors .custom-select-wrapper')[3];

            const user_id = userWrapper.dataset.value;
            const role = roleWrapper.dataset.value;
            const direction_id = directionWrapper.dataset.value;
            const cabinet_id = cabinetWrapper.dataset.value || null;
            const exp = document.getElementById('expAdd').value;
            if (!user_id || !role || !direction_id || !exp) {
                customAlert('Заполните все поля');
                return;
            }

            const response = await fetch('../crud/addDoctor.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id, role, direction_id, cabinet_id, exp })
            });
            const result = await response.json();

            if (result.success) {
                customAlert('Специалист добавлен');
                hidePopup('add-popup');
                loadSection(currentSection);
            } else {
                customAlert(result.message);
            }
        }//сохранение графика
        else if (currentSection === 'doctorSchedule') {
            const doctorId = document.querySelector('#addSchedule').dataset.doctorId;
            const checkboxes = document.querySelectorAll('#addSchedule .cb input:checked');
            const days = Array.from(checkboxes).map(cb => cb.value);

            if (!doctorId) {
                customAlert('Ошибка: не выбран врач');
                return;
            }

            if (days.length === 0) {
                customAlert('Выберите хотя бы один день');
                return;
            }

            const response = await fetch('../crud/addSchedule.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ doctor_id: doctorId, days })
            });
            const result = await response.json();

            if (result.success) {
                customAlert('График добавлен');
                hidePopup('add-popup');
                loadSection(currentSection);
            } else {
                customAlert(result.message);
            }
        }//сохраняем диагноз
        else if (currentSection === 'diagnose') {
            const patientWrapper = document.querySelectorAll('#addDiagnose .custom-select-wrapper')[0];
            const doctorWrapper = document.querySelectorAll('#addDiagnose .custom-select-wrapper')[1];

            const patient_id = patientWrapper.dataset.value;
            const doctor_id = doctorWrapper.dataset.value;
            const date = document.querySelector('#addDiagnose #dateInput').value;
            const diagnose_text = document.getElementById('diagnoseAdd').value;
            const fileInput = document.getElementById('diagnoseFileAdd');

            if (!patient_id || !doctor_id || !date || !diagnose_text) {
                customAlert('Заполните все поля');
                return;
            }

            const formData = new FormData();
            formData.append('patient_id', patient_id);
            formData.append('doctor_id', doctor_id);
            formData.append('date', date);
            formData.append('diagnose_text', diagnose_text);
            if (fileInput.files.length > 0) {
                formData.append('diagnoseFile', fileInput.files[0]);
            }

            const response = await fetch('../crud/addDiagnose.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                customAlert('Диагноз добавлен');
                hidePopup('add-popup');
                loadSection(currentSection);
            } else {
                customAlert(result.message);
            }
        }//сохранение симптома 
        else if (currentSection === 'symptomes') {
            const keyword = document.getElementById('sympAdd').value;
            const priority = document.getElementById('priorityAdd').value;

            if (!keyword || !priority) {
                customAlert('Заполните все поля');
                return;
            }

            // Получаем direction_id текущего врача
            const dirResponse = await fetch('../func/getDoctorDirection.php');
            const dirData = await dirResponse.json();
            const direction_id = dirData.direction_id;

            if (!direction_id) {
                customAlert('Ошибка: не удалось определить направление врача');
                return;
            }

            const response = await fetch('../crud/addSymptom.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ keyword, priority, direction_id })
            });
            const result = await response.json();

            if (result.success) {
                customAlert('Симптом добавлен');
                hidePopup('add-popup');
                loadSection(currentSection);
            } else {
                customAlert(result.message);
            }
        }
    });
    // Открытие модалки редактирования
    document.addEventListener('click', function (e) {
        // 1. Пользователи
        const userEdit = e.target.closest('.userCard .editBtn');
        if (userEdit) {
            e.preventDefault();

            // Скрываем все блоки и показываем нужный
            document.querySelectorAll('#edit-popup .popupContent').forEach(block => {
                block.style.display = 'none';
            });
            document.getElementById('editUsers').style.display = 'flex';

            const card = userEdit.closest('.userCard');
            const userId = card.dataset.id;

            fetch(`../crud/getItem.php?type=user&id=${userId}`)
                .then(response => response.json())
                .then(user => {
                    if (!user.user_id) {
                        customAlert('Ошибка загрузки данных');
                        return;
                    }

                    // Фото
                    const photo = user.photo ? '../img/avatars/' + user.photo : '../img/avatars/none.svg';
                    document.querySelector('#editUsers img').src = photo;
                    // После установки фото
                    const avatarImg = document.querySelector('#editUsers img');
                    const deleteAvatarBtn = document.getElementById('deleteUserAvatarBtn');
                    if (user.photo && user.photo != 'none.svg') {
                        deleteAvatarBtn.style.display = 'block';
                    } else {
                        deleteAvatarBtn.style.display = 'none';
                    }
                    // Поля
                    document.getElementById('surnameEdit').value = user.surname;
                    document.getElementById('nameEdit').value = user.name;
                    document.getElementById('secnameEdit').value = user.sec_name || '';
                    document.getElementById('phoneEdit').value = formatPhone(user.phone_num);

                    // Заполняем селект ролей (все роли)
                    const allRoles = ['Пользователь', 'Администратор', 'Доктор', 'Персонал', 'Заблокирован'];
                    const roleOptions = document.querySelector('#editUsers .options-container');
                    roleOptions.innerHTML = allRoles.map(role =>
                        `<div class="filter-option" data-value="${role}">${role}</div>`
                    ).join('');

                    // Выбираем текущую роль
                    const roleOption = Array.from(roleOptions.children).find(opt => opt.dataset.value === user.role);
                    const wrapper = document.querySelector('#editUsers .custom-select-wrapper');
                    const triggerSpan = wrapper.querySelector('.custom-select-trigger span');

                    if (roleOption) {
                        triggerSpan.textContent = roleOption.textContent;
                        wrapper.dataset.value = user.role;
                        roleOption.classList.add('selected');
                    }

                    // Инициализируем селект
                    if (typeof window.initCustomSelects === 'function') {
                        window.initCustomSelects();
                    }

                    // Сохраняем ID
                    document.querySelector('#edit-popup').dataset.userId = userId;

                    showPopup('edit-popup');
                });
            return;
        }

        // 2. Услуги
        const serviceEdit = e.target.closest('.serviceCard .editBtn');
        if (serviceEdit) {
            e.preventDefault();

            document.querySelectorAll('#edit-popup .popupContent').forEach(block => {
                block.style.display = 'none';
            });
            document.getElementById('editServices').style.display = 'flex';

            const card = serviceEdit.closest('.serviceCard');
            const serviceId = card.dataset.id;

            fetch(`../crud/getItem.php?type=service&id=${serviceId}`)
                .then(response => response.json())
                .then(service => {
                    if (!service.service_id) {
                        customAlert('Ошибка загрузки данных');
                        return;
                    }

                    document.getElementById('serviceNameEdit').value = service.name;
                    document.getElementById('priceEdit').value = service.price;

                    // Доступность
                    const publicRadio = document.querySelector(`input[name="is_public"][value="${service.is_public}"]`);
                    if (publicRadio) publicRadio.checked = true;

                    // Загружаем направления
                    const directionContainer = document.querySelector('#editServices .options-container');
                    fetch('../crud/getSelectOptions.php?type=directions')
                        .then(response => response.text())
                        .then(data => {
                            directionContainer.innerHTML = data;

                            const directionOption = Array.from(directionContainer.children).find(opt => opt.dataset.value == service.direction_id);
                            const wrapper = document.querySelector('#editServices .custom-select-wrapper');
                            const triggerSpan = wrapper.querySelector('.custom-select-trigger span');

                            if (directionOption) {
                                triggerSpan.textContent = directionOption.textContent;
                                wrapper.dataset.value = service.direction_id;
                                directionOption.classList.add('selected');
                            }

                            if (typeof window.initCustomSelects === 'function') {
                                window.initCustomSelects();
                            }
                        });

                    document.querySelector('#edit-popup').dataset.serviceId = serviceId;
                    showPopup('edit-popup');
                });
            return;
        }

        // 3. Кабинеты
        const cabinetEdit = e.target.closest('.cabinetCard .editBtn');
        if (cabinetEdit) {
            e.preventDefault();

            document.querySelectorAll('#edit-popup .popupContent').forEach(block => {
                block.style.display = 'none';
            });
            document.getElementById('editCabinets').style.display = 'flex';

            const card = cabinetEdit.closest('.cabinetCard');
            const cabinetId = card.dataset.id;

            fetch(`../crud/getItem.php?type=cabinet&id=${cabinetId}`)
                .then(response => response.json())
                .then(cabinet => {
                    if (!cabinet.cabinet_id) {
                        customAlert('Ошибка загрузки данных');
                        return;
                    }

                    document.getElementById('floorEdit').value = cabinet.floor;
                    document.getElementById('numberEdit').value = cabinet.number;

                    document.querySelector('#edit-popup').dataset.cabinetId = cabinetId;
                    showPopup('edit-popup');
                });
            return;
        }

        // 4. Записи
        const appointmentEdit = e.target.closest('.appCard .editBtn');
        if (appointmentEdit) {
            e.preventDefault();

            // Скрываем все блоки в модалке
            document.querySelectorAll('#edit-popup .popupContent').forEach(block => {
                block.style.display = 'none';
            });
            document.getElementById('editApps').style.display = 'flex';

            // Показываем информационный блок, скрываем дату и время
            document.getElementById('stepInfoEdit').style.display = 'flex';
            document.getElementById('stepDateEdit').style.display = 'none';
            document.getElementById('stepTimeEdit').style.display = 'none';

            const card = appointmentEdit.closest('.appCard');
            const appointmentId = card.dataset.id;

            fetch(`../crud/getItem.php?type=appointment&id=${appointmentId}`)
                .then(response => response.json())
                .then(app => {
                    if (!app.appointment_id) {
                        customAlert('Ошибка загрузки данных');
                        return;
                    }

                    // Сохраняем ID записи и оригинального врача
                    document.querySelector('#edit-popup').dataset.appointmentId = appointmentId;
                    document.querySelector('#edit-popup').dataset.originalDoctorId = app.doctor_id;

                    // Заполняем селекты (пациент, врач, услуга, статус)
                    // Пациенты
                    const patientContainer = document.querySelector('#editApps .options-container');
                    fetch('../crud/getSelectOptions.php?type=patients')
                        .then(response => response.text())
                        .then(data => {
                            patientContainer.innerHTML = data;
                            // Выбираем текущего пациента
                            const patientOption = Array.from(patientContainer.children).find(opt => opt.dataset.value == app.user_id);
                            const patientWrapper = document.querySelectorAll('#editApps .custom-select-wrapper')[0];
                            const patientTrigger = patientWrapper.querySelector('.custom-select-trigger span');
                            if (patientOption) {
                                patientTrigger.textContent = patientOption.textContent;
                                patientWrapper.dataset.value = app.user_id;
                                patientOption.classList.add('selected');
                            }
                        });

                    // Врачи — в зависимости от роли
                    let doctorUrl = '../crud/getSelectOptions.php?type=doctors';
                    if (window.isDoctor) {
                        doctorUrl = '../crud/getSelectOptions.php?type=doctorsForDoctor';
                    } else if (window.isStuff) {
                        doctorUrl = '../crud/getSelectOptions.php?type=currentDoctor';
                    }

                    const doctorContainer = document.querySelectorAll('#editApps .options-container')[1];
                    fetch(doctorUrl)
                        .then(response => response.text())
                        .then(data => {
                            doctorContainer.innerHTML = data;
                            const doctorOption = Array.from(doctorContainer.children).find(opt => opt.dataset.value == app.doctor_id);
                            const doctorWrapper = document.querySelectorAll('#editApps .custom-select-wrapper')[1];
                            const doctorTrigger = doctorWrapper.querySelector('.custom-select-trigger span');
                            if (doctorOption) {
                                doctorTrigger.textContent = doctorOption.textContent;
                                doctorWrapper.dataset.value = app.doctor_id;
                                doctorOption.classList.add('selected');
                            }
                        });

                    // Услуги
                    const serviceContainer = document.querySelectorAll('#editApps .options-container')[2];
                    fetch('../crud/getSelectOptions.php?type=services')
                        .then(response => response.text())
                        .then(data => {
                            serviceContainer.innerHTML = data;
                            const serviceOption = Array.from(serviceContainer.children).find(opt => opt.dataset.value == app.service_id);
                            const serviceWrapper = document.querySelectorAll('#editApps .custom-select-wrapper')[2];
                            const serviceTrigger = serviceWrapper.querySelector('.custom-select-trigger span');
                            if (serviceOption) {
                                serviceTrigger.textContent = serviceOption.textContent;
                                serviceWrapper.dataset.value = app.service_id;
                                serviceOption.classList.add('selected');
                            }
                        });

                    // Статусы
                    const statusContainer = document.querySelectorAll('#editApps .options-container')[3];
                    statusContainer.innerHTML = `
                        <div class="filter-option" data-value="запланирован">Запланирован</div>
                        <div class="filter-option" data-value="завершён">Завершён</div>
                        <div class="filter-option" data-value="отменён">Отменён</div>
                    `;
                    const statusOption = Array.from(statusContainer.children).find(opt => opt.dataset.value === app.status);
                    const statusWrapper = document.querySelectorAll('#editApps .custom-select-wrapper')[3];
                    const statusTrigger = statusWrapper.querySelector('.custom-select-trigger span');
                    if (statusOption) {
                        statusTrigger.textContent = statusOption.textContent;
                        statusWrapper.dataset.value = app.status;
                        statusOption.classList.add('selected');
                    }

                    // Инициализируем селекты
                    if (typeof window.initCustomSelects === 'function') {
                        window.initCustomSelects();
                    }

                    showPopup('edit-popup');
                });
            return;
        }

        // 5. Врачи
        const doctorEdit = e.target.closest('.doctorCard .editBtn');
        if (doctorEdit) {
            e.preventDefault();

            document.querySelectorAll('#edit-popup .popupContent').forEach(block => {
                block.style.display = 'none';
            });
            document.getElementById('editDoctors').style.display = 'flex';

            const card = doctorEdit.closest('.doctorCard');
            const doctorId = card.dataset.id;

            fetch(`../crud/getItem.php?type=doctor&id=${doctorId}`)
                .then(response => response.json())
                .then(doctor => {
                    if (!doctor.doctor_id) {
                        customAlert('Ошибка загрузки данных');
                        return;
                    }

                    // Стаж
                    document.getElementById('expEdit').value = doctor.exp;

                    // Загружаем роли (только Доктор и Персонал)
                    const roleContainer = document.querySelector('#editDoctors .options-container:first-of-type');
                    roleContainer.innerHTML = `
                        <div class="filter-option" data-value="Доктор">Врач</div>
                        <div class="filter-option" data-value="Персонал">Персонал</div>
                    `;

                    // Выбираем текущую роль
                    const roleOption = Array.from(roleContainer.children).find(opt => opt.dataset.value === doctor.role);
                    const roleWrapper = document.querySelector('#editDoctors .custom-select-wrapper:first-of-type');
                    const roleTrigger = roleWrapper.querySelector('.custom-select-trigger span');
                    if (roleOption) {
                        roleTrigger.textContent = roleOption.textContent;
                        roleWrapper.dataset.value = doctor.role;
                        roleOption.classList.add('selected');
                    }

                    // Загружаем специальности (направления)
                    const directionContainer = document.querySelectorAll('#editDoctors .options-container')[1];
                    fetch('../crud/getSelectOptions.php?type=directions')
                        .then(response => response.text())
                        .then(data => {
                            directionContainer.innerHTML = data;
                            const directionOption = Array.from(directionContainer.children).find(opt => opt.dataset.value == doctor.direction_id);
                            const directionWrapper = document.querySelectorAll('#editDoctors .custom-select-wrapper')[1];
                            const directionTrigger = directionWrapper.querySelector('.custom-select-trigger span');
                            if (directionOption) {
                                directionTrigger.textContent = directionOption.textContent;
                                directionWrapper.dataset.value = doctor.direction_id;
                                directionOption.classList.add('selected');
                            }
                        });

                    // Загружаем кабинеты
                    const cabinetContainer = document.querySelectorAll('#editDoctors .options-container')[2];
                    fetch('../crud/getSelectOptions.php?type=cabinets')
                        .then(response => response.text())
                        .then(data => {
                            cabinetContainer.innerHTML = data;
                            const cabinetOption = Array.from(cabinetContainer.children).find(opt => opt.dataset.value == doctor.cabinet_id);
                            const cabinetWrapper = document.querySelectorAll('#editDoctors .custom-select-wrapper')[2];
                            const cabinetTrigger = cabinetWrapper.querySelector('.custom-select-trigger span');
                            if (cabinetOption) {
                                cabinetTrigger.textContent = cabinetOption.textContent;
                                cabinetWrapper.dataset.value = doctor.cabinet_id;
                                cabinetOption.classList.add('selected');
                            }
                        });

                    // Сохраняем ID
                    document.querySelector('#edit-popup').dataset.doctorId = doctorId;

                    // Инициализируем селекты
                    if (typeof window.initCustomSelects === 'function') {
                        window.initCustomSelects();
                    }

                    showPopup('edit-popup');
                });
            return;
        }

        // 6. График врача
        const scheduleEdit = e.target.closest('.scheduleCard .editBtn');
        if (scheduleEdit) {
            e.preventDefault();

            document.querySelectorAll('#edit-popup .popupContent').forEach(block => {
                block.style.display = 'none';
            });
            document.getElementById('editSchedule').style.display = 'flex';

            const card = scheduleEdit.closest('.scheduleCard');
            const doctorId = card.dataset.id;
            const doctorName = card.querySelector('.info p:first-child').textContent;

            // Заполняем имя врача
            document.querySelector('#editSchedule #fullName').textContent = doctorName;

            // Загружаем текущий график
            fetch(`../func/getDoctorSchedule.php?doctor_id=${doctorId}`)
                .then(response => response.json())
                .then(schedule => {
                    // Отмечаем чекбоксы
                    const checkboxes = document.querySelectorAll('#editSchedule .cb input');
                    checkboxes.forEach(cb => {
                        cb.checked = schedule.includes(parseInt(cb.value));
                    });

                    // Сохраняем ID врача
                    document.querySelector('#edit-popup').dataset.doctorId = doctorId;

                    showPopup('edit-popup');
                });
            return;
        }

        // 7. Диагнозы
        const diagnoseEdit = e.target.closest('.diagnoseCard .editBtn');
        if (diagnoseEdit) {
            e.preventDefault();

            document.querySelectorAll('#edit-popup .popupContent').forEach(block => {
                block.style.display = 'none';
            });
            document.getElementById('editDiagnose').style.display = 'flex';

            const card = diagnoseEdit.closest('.diagnoseCard');
            const diagnoseId = card.dataset.id;

            fetch(`../crud/getItem.php?type=diagnose&id=${diagnoseId}`)
                .then(response => response.json())
                .then(diagnose => {
                    if (!diagnose.diagnose_id) {
                        customAlert('Ошибка загрузки данных');
                        return;
                    }

                    // Дата в формате дд.мм.гггг
                    const date = new Date(diagnose.date);
                    const day = String(date.getDate()).padStart(2, '0');
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const year = date.getFullYear();
                    const formattedDate = `${day}.${month}.${year}`;
                    document.querySelector('#editDiagnose #dateInput').value = formattedDate;

                    // Диагноз
                    document.getElementById('diagnoseEdit').value = diagnose.diagnose_text;

                    // Загружаем пациентов
                    const patientContainer = document.querySelector('#editDiagnose .options-container');
                    fetch('../crud/getSelectOptions.php?type=patients')
                        .then(response => response.text())
                        .then(data => {
                            patientContainer.innerHTML = data;
                            const patientOption = Array.from(patientContainer.children).find(opt => opt.dataset.value == diagnose.user_id);
                            const patientWrapper = document.querySelector('#editDiagnose .custom-select-wrapper');
                            const patientTrigger = patientWrapper.querySelector('.custom-select-trigger span');
                            if (patientOption) {
                                patientTrigger.textContent = patientOption.textContent;
                                patientWrapper.dataset.value = diagnose.user_id;
                                patientOption.classList.add('selected');
                            }

                            if (typeof window.initCustomSelects === 'function') {
                                window.initCustomSelects();
                            }
                        });

                    // Инициализируем календарь с расписанием текущего врача
                    const dateInput = document.querySelector('#editDiagnose #dateInput');
                    fetch('../func/getDoctorSchedule.php')
                        .then(response => response.json())
                        .then(schedule => {
                            flatpickr(dateInput, {
                                locale: 'ru',
                                dateFormat: 'd.m.Y',
                                maxDate: 'today',
                                disable: [
                                    function (date) {
                                        if (!schedule || schedule.length === 0) return true;
                                        return !schedule.includes(date.getDay());
                                    }
                                ]
                            });
                        });

                    // Сохраняем ID
                    document.querySelector('#edit-popup').dataset.diagnoseId = diagnoseId;

                    // Показываем информацию о файле
                    const fileContainer = document.getElementById('currentFileInfo');
                    if (fileContainer) {
                        if (diagnose.file_name) {
                            fileContainer.innerHTML = `<p>Текущий файл: <a href="../func/download.php?file=${encodeURIComponent(diagnose.file_name)}" target="_blank">${diagnose.file_name}</a></p>`;
                        } else {
                            fileContainer.innerHTML = '<p>Файл не прикреплён</p>';
                        }
                    }

                    showPopup('edit-popup');
                });
            return;
        }

        //8. Симптомы
        const symptomEdit = e.target.closest('.symptomCard .editBtn');
        if (symptomEdit) {
            e.preventDefault();

            document.querySelectorAll('#edit-popup .popupContent').forEach(block => {
                block.style.display = 'none';
            });
            document.getElementById('editSymp').style.display = 'flex';

            const card = symptomEdit.closest('.symptomCard');
            const symptomId = card.dataset.id;

            fetch(`../crud/getItem.php?type=symptom&id=${symptomId}`)
                .then(response => response.json())
                .then(symptom => {
                    if (!symptom.id) {
                        customAlert('Ошибка загрузки данных');
                        return;
                    }

                    document.getElementById('sympEdit').value = symptom.keyword;
                    document.getElementById('priorityEdit').value = symptom.priority;

                    document.querySelector('#edit-popup').dataset.symptomId = symptomId;
                    showPopup('edit-popup');
                });
            return;
        }
    });
    //сохранение редакта
    document.getElementById('editBtn').addEventListener('click', async function () {
        const currentSection = document.querySelector('.tabBtn.selected').dataset.section;

        if (currentSection === 'users') {
            const userId = document.querySelector('#edit-popup').dataset.userId;
            if (!userId) return;

            const surname = document.getElementById('surnameEdit').value;
            const name = document.getElementById('nameEdit').value;
            const secname = document.getElementById('secnameEdit').value;
            const phoneRaw = document.getElementById('phoneEdit').value.replace(/\D/g, '');
            const roleWrapper = document.querySelector('#editUsers .custom-select-wrapper');
            const role = roleWrapper.dataset.value;

            if (!surname || !name || !phoneRaw || !role) {
                customAlert('Заполните все поля');
                return;
            }

            const response = await fetch('../crud/updateUser.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    user_id: userId,
                    surname: surname,
                    name: name,
                    secname: secname,
                    phone: phoneRaw,
                    role: role
                })
            });
            const result = await response.json();

            if (result.success) {
                customAlert('Пользователь обновлён');
                hidePopup('edit-popup');
                loadSection(currentSection);
            } else {
                customAlert(result.message);
            }
        } else if (currentSection === 'services') {
            const serviceId = document.querySelector('#edit-popup').dataset.serviceId;
            if (!serviceId) return;

            const name = document.getElementById('serviceNameEdit').value;
            const directionWrapper = document.querySelector('#editServices .custom-select-wrapper');
            const direction_id = directionWrapper.dataset.value;
            const price = document.getElementById('priceEdit').value;
            const is_public = document.querySelector('input[name="is_public"]:checked').value;

            if (!name || !direction_id || !price) {
                customAlert('Заполните все поля');
                return;
            }

            const response = await fetch('../crud/updateService.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ service_id: serviceId, name, direction_id, price, is_public })
            });
            const result = await response.json();

            if (result.success) {
                customAlert('Услуга обновлена');
                hidePopup('edit-popup');
                loadSection(currentSection);
            } else {
                customAlert(result.message);
            }
        } else if (currentSection === 'cabinets') {
            const cabinetId = document.querySelector('#edit-popup').dataset.cabinetId;
            if (!cabinetId) return;

            const floor = document.getElementById('floorEdit').value;
            const number = document.getElementById('numberEdit').value;

            if (!floor || !number) {
                customAlert('Заполните все поля');
                return;
            }

            const response = await fetch('../crud/updateCabinet.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ cabinet_id: cabinetId, floor, number })
            });
            const result = await response.json();

            if (result.success) {
                customAlert('Кабинет обновлён');
                hidePopup('edit-popup');
                loadSection(currentSection);
            } else {
                customAlert(result.message);
            }
        } else if (currentSection === 'appointments') {
            const appointmentId = document.querySelector('#edit-popup').dataset.appointmentId;
            if (!appointmentId) return;

            // Получаем данные из селектов
            const patientWrapper = document.querySelectorAll('#editApps .custom-select-wrapper')[0];
            const doctorWrapper = document.querySelectorAll('#editApps .custom-select-wrapper')[1];
            const serviceWrapper = document.querySelectorAll('#editApps .custom-select-wrapper')[2];
            const statusWrapper = document.querySelectorAll('#editApps .custom-select-wrapper')[3];

            const patient_id = patientWrapper.dataset.value;
            const doctor_id = doctorWrapper.dataset.value;
            const service_id = serviceWrapper.dataset.value;
            const status = statusWrapper.dataset.value;

            // Получаем дату и время (если выбраны)
            const date = document.querySelector('#editApps #dateInput').value;
            const time = document.querySelector('#editApps .time-slot.selected')?.dataset.time;

            // Проверяем обязательные поля
            if (!patient_id || !doctor_id || !service_id || !status) {
                customAlert('Заполните все поля');
                return;
            }

            // Если изменился врач — обязательно нужно выбрать дату и время
            const originalDoctorId = document.querySelector('#edit-popup').dataset.originalDoctorId;
            if (originalDoctorId != doctor_id && (!date || !time)) {
                customAlert('При смене врача необходимо выбрать новую дату и время');
                return;
            }

            if (date && !time) {
                customAlert('Выберите время');
                return;
            }

            const response = await fetch('../crud/updateAppointment.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    appointment_id: appointmentId,
                    patient_id,
                    doctor_id,
                    service_id,
                    date: date || null,
                    time: time || null,
                    status
                })
            });
            const result = await response.json();

            if (result.success) {
                customAlert('Запись обновлена');
                hidePopup('edit-popup');
                loadSection(currentSection);
            } else {
                customAlert(result.message);
            }
        } else if (currentSection === 'doctors') {
            const doctorId = document.querySelector('#edit-popup').dataset.doctorId;
            if (!doctorId) return;

            const roleWrapper = document.querySelector('#editDoctors .custom-select-wrapper:first-of-type');
            const directionWrapper = document.querySelectorAll('#editDoctors .custom-select-wrapper')[1];
            const cabinetWrapper = document.querySelectorAll('#editDoctors .custom-select-wrapper')[2];

            const role = roleWrapper.dataset.value;
            const direction_id = directionWrapper.dataset.value;
            const cabinet_id = cabinetWrapper.dataset.value || null;
            const exp = document.getElementById('expEdit').value;

            if (!role || !direction_id || !exp) {
                customAlert('Заполните все поля');
                return;
            }

            // Проверка для персонала
            const staffDirections = [11, 12, 13]; // ID процедурного, лаборатории, лучевой
            if (role === 'Персонал' && !staffDirections.includes(parseInt(direction_id))) {
                customAlert('Персонал может работать только в процедурном кабинете, лабораторной диагностике или лучевой диагностике');
                return;
            }

            const response = await fetch('../crud/updateDoctor.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ doctor_id: doctorId, role, direction_id, cabinet_id, exp })
            });
            const result = await response.json();

            if (result.success) {
                customAlert('Врач обновлён');
                hidePopup('edit-popup');
                loadSection(currentSection);
            } else {
                customAlert(result.message);
            }
        } else if (currentSection === 'doctorSchedule') {
            const doctorId = document.querySelector('#edit-popup').dataset.doctorId;
            if (!doctorId) return;

            const checkboxes = document.querySelectorAll('#editSchedule .cb input:checked');
            const newDays = Array.from(checkboxes).map(cb => cb.value);

            if (newDays.length === 0) {
                customAlert('Выберите хотя бы один день');
                return;
            }

            // Получаем старый график
            const oldDays = await fetch(`../func/getDoctorSchedule.php?doctor_id=${doctorId}`)
                .then(res => res.json());

            // Находим удалённые дни
            const removedDays = oldDays.filter(day => !newDays.includes(day.toString()));

            if (removedDays.length > 0) {
                const confirmMsg = `Вы удаляете дни: ${removedDays.join(', ')}. Существующие записи на эти дни останутся, но новые записи на них будут недоступны. Продолжить?`;
                if (!confirm(confirmMsg)) {
                    return;
                }
            }

            const response = await fetch('../crud/updateDoctorSchedule.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ doctor_id: doctorId, days: newDays })
            });
            const result = await response.json();

            if (result.success) {
                customAlert('График обновлён');
                hidePopup('edit-popup');
                loadSection(currentSection);
            } else {
                customAlert(result.message);
            }

        } else if (currentSection === 'diagnose') {
            const diagnoseId = document.querySelector('#edit-popup').dataset.diagnoseId;
            if (!diagnoseId) return;

            const patientWrapper = document.querySelector('#editDiagnose .custom-select-wrapper');
            const patient_id = patientWrapper.dataset.value;
            const date = document.querySelector('#editDiagnose #dateInput').value;
            const diagnose_text = document.getElementById('diagnoseEdit').value;

            if (!patient_id || !date || !diagnose_text) {
                customAlert('Заполните все поля');
                return;
            }

            // Получаем doctor_id текущего врача
            const doctorResponse = await fetch('../func/getCurrentDoctorId.php');
            const doctorData = await doctorResponse.json();
            const doctor_id = doctorData.doctor_id;

            if (!doctor_id) {
                customAlert('Ошибка: не удалось определить врача');
                return;
            }

            const fileInput = document.getElementById('diagnoseFile');
            const hasFile = fileInput.files.length > 0;

            let response;
            if (hasFile) {
                const formData = new FormData();
                formData.append('diagnose_id', diagnoseId);
                formData.append('patient_id', patient_id);
                formData.append('date', date);
                formData.append('diagnose_text', diagnose_text);
                formData.append('diagnoseFile', fileInput.files[0]);

                response = await fetch('../crud/updateDiagnose.php', {
                    method: 'POST',
                    body: formData
                });
            } else {
                response = await fetch('../crud/updateDiagnose.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        diagnose_id: diagnoseId,
                        patient_id: patient_id,
                        date: date,
                        diagnose_text: diagnose_text
                    })
                });
            }
            const result = await response.json();

            if (result.success) {
                customAlert('Диагноз обновлён');
                hidePopup('edit-popup');
                loadSection(currentSection);
            } else {
                customAlert(result.message);
            }
        } else if (currentSection === 'symptomes') {
            const symptomId = document.querySelector('#edit-popup').dataset.symptomId;
            if (!symptomId) return;

            const keyword = document.getElementById('sympEdit').value;
            const priority = document.getElementById('priorityEdit').value;

            if (!keyword || !priority) {
                customAlert('Заполните все поля');
                return;
            }

            const response = await fetch('../crud/updateSymptom.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ symptom_id: symptomId, keyword, priority })
            });
            const result = await response.json();

            if (result.success) {
                customAlert('Симптом обновлён');
                hidePopup('edit-popup');
                loadSection(currentSection);
            } else {
                customAlert(result.message);
            }
        }
    });
    //редакт для фотки
    document.getElementById('photoEdit').addEventListener('change', async function (e) {
        const file = e.target.files[0];
        if (!file) return;

        const userId = document.querySelector('#edit-popup').dataset.userId;
        if (!userId) return;

        const formData = new FormData();
        formData.append('photo', file);
        formData.append('user_id', userId);

        const response = await fetch('../crud/updateUser.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.success) {
            document.querySelector('#editUsers img').src = result.photoPath + '?t=' + Date.now();
            customAlert('Фото обновлено');
        } else {
            customAlert(result.message);
        }
    });
    document.getElementById('editUsers').addEventListener('click', function (e) {
        // Если кликнули по кнопке удаления или по её содержимому — не открываем проводник
        if (e.target.closest('.delete-avatar-btn')) return;
        if (e.target.closest('img')) {
            document.getElementById('photoEdit').click();
        }
    });
    //обработчик удаления аватарки
    document.getElementById('deleteUserAvatarBtn').addEventListener('click', async function () {
        const userId = document.querySelector('#edit-popup').dataset.userId;
        if (!userId) return;

        if (!confirm('Удалить фото?')) return;

        const formData = new FormData();
        formData.append('delete_photo', '1');
        formData.append('user_id', userId);

        const response = await fetch('../crud/updateUser.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.success) {
            const newSrc = '../img/avatars/none.svg?';
            document.querySelector('#editUsers img').src = newSrc;
            document.getElementById('deleteUserAvatarBtn').style.display = 'none';
            customAlert('Фото удалено');
            // Можно обновить карточку в списке, но проще — перезагрузить секцию
            loadSection('users');
        } else {
            customAlert(result.message);
        }
    });

    // Единый обработчик для кнопок удаления
    document.addEventListener('click', function (e) {
        const deleteBtn = e.target.closest('.deleteBtn');
        if (!deleteBtn) return;

        e.preventDefault();

        // Определяем тип карточки и ID
        let type = '';
        let id = null;

        if (deleteBtn.closest('.userCard')) {
            type = 'user';
            id = deleteBtn.closest('.userCard').dataset.id;
        } else if (deleteBtn.closest('.serviceCard')) {
            type = 'service';
            id = deleteBtn.closest('.serviceCard').dataset.id;
        } else if (deleteBtn.closest('.cabinetCard')) {
            type = 'cabinet';
            id = deleteBtn.closest('.cabinetCard').dataset.id;
        } else if (deleteBtn.closest('.appCard')) {
            type = 'appointment';
            id = deleteBtn.closest('.appCard').dataset.id;
        } else if (deleteBtn.closest('.doctorCard')) {
            type = 'doctor';
            id = deleteBtn.closest('.doctorCard').dataset.id;
        } else if (deleteBtn.closest('.scheduleCard')) {
            type = 'schedule';
            id = deleteBtn.closest('.scheduleCard').dataset.id;
        } else if (deleteBtn.closest('.diagnoseCard')) {
            type = 'diagnose';
            id = deleteBtn.closest('.diagnoseCard').dataset.id;
        } else if (deleteBtn.closest('.symptomCard')) {
            type = 'symptom';
            id = deleteBtn.closest('.symptomCard').dataset.id;
        }

        if (!type || !id) return;

        // Сохраняем данные для удаления в попапе
        document.querySelector('#delete-popup').dataset.type = type;
        document.querySelector('#delete-popup').dataset.id = id;

        showPopup('delete-popup');
    });
    //сохранение удаления
    document.getElementById('deleteBtn').addEventListener('click', async function () {
        const type = document.querySelector('#delete-popup').dataset.type;
        const id = document.querySelector('#delete-popup').dataset.id;

        if (!type || !id) return;

        const response = await fetch('../crud/deleteItem.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ type, id })
        });
        const result = await response.json();

        if (result.success) {
            customAlert('Удалено');
            hidePopup('delete-popup');
            // Перезагружаем текущую секцию
            const currentSection = document.querySelector('.tabBtn.selected').dataset.section;
            loadSection(currentSection);
        } else {
            customAlert(result.message);
        }
    });

    // Обработчики для модалки редактирования записи
    const editAppsBlock = document.getElementById('editApps');
    if (editAppsBlock) {
        // Кнопка "Изменить дату" — показываем календарь
        document.getElementById('showDateBtn').addEventListener('click', function () {
            document.getElementById('stepInfoEdit').style.display = 'none';
            document.getElementById('stepDateEdit').style.display = 'flex';

            const doctorWrapper = document.querySelectorAll('#editApps .custom-select-wrapper')[1];
            const doctorId = doctorWrapper.dataset.value;

            if (!doctorId) {
                customAlert('Сначала выберите врача');
                document.getElementById('stepDateEdit').style.display = 'none';
                document.getElementById('stepInfoEdit').style.display = 'flex';
                return;
            }

            const dateInput = document.querySelector('#editApps #dateInput');

            // Очищаем поле, чтобы onChange точно сработал
            dateInput.value = '';

            // Уничтожаем старый календарь
            if (dateInput._flatpickr) {
                dateInput._flatpickr.destroy();
            }

            // Загружаем расписание
            fetch(`../func/getDoctorSchedule.php?doctor_id=${doctorId}`)
                .then(response => response.json())
                .then(schedule => {
                    flatpickr(dateInput, {
                        locale: 'ru',
                        minDate: 'today',
                        maxDate: new Date().fp_incr(60),
                        dateFormat: 'd.m.Y',
                        disable: [
                            function (date) {
                                if (!schedule || schedule.length === 0) return true;
                                return !schedule.includes(date.getDay());
                            }
                        ],
                        onChange: function (selectedDates, dateStr) {
                            // Сохраняем дату
                            window.selectedDate = dateStr;

                            // Показываем блок времени
                            document.getElementById('stepDateEdit').style.display = 'none';
                            document.getElementById('stepTimeEdit').style.display = 'block';

                            // Отображаем дату в шапке
                            const [day, month, year] = dateStr.split('.');
                            const dateObj = new Date(year, month - 1, day);
                            document.querySelector('#editApps .selected-date-display').textContent =
                                dateObj.toLocaleDateString('ru-RU', {
                                    weekday: 'short',
                                    day: 'numeric',
                                    month: 'numeric',
                                    year: 'numeric'
                                });

                            // Загружаем слоты времени
                            window.adminDateTime.initTimeSlots(
                                document.querySelector('#editApps .time-grid'),
                                doctorId,
                                dateStr,
                                (time) => {
                                    window.selectedTime = time;
                                }
                            );
                        }
                    });
                });
        });

        // Кнопка "Вернуться" из даты в информацию
        document.getElementById('backToInfo').addEventListener('click', function () {
            document.getElementById('stepDateEdit').style.display = 'none';
            document.getElementById('stepInfoEdit').style.display = 'flex';
        });

        // Кнопка "Изменить время" — показываем время (если дата уже выбрана)
        document.getElementById('showTimeBtn').addEventListener('click', function () {
            const dateInput = document.querySelector('#editApps #dateInput');
            if (!dateInput.value) {
                customAlert('Сначала выберите дату');
                return;
            }
            document.getElementById('stepDateEdit').style.display = 'none';
            document.getElementById('stepTimeEdit').style.display = 'block';
        });

        // Возврат из времени в дату
        document.querySelector('#editApps .time-header')?.addEventListener('click', function () {
            document.getElementById('stepTimeEdit').style.display = 'none';
            document.getElementById('stepDateEdit').style.display = 'flex';
        });
    }
});