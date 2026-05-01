document.addEventListener('DOMContentLoaded', function () {
    // Переключение между блоками
    const changeBtn = document.getElementById('changeBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const viewBlock = document.getElementById('viewBlock');
    const editBlock = document.getElementById('editBlock');

    if (changeBtn) {
        changeBtn.addEventListener('click', function () {
            viewBlock.style.display = 'none';
            editBlock.style.display = 'flex';
        });
    }

    if (cancelBtn) {
        cancelBtn.addEventListener('click', function () {
            editBlock.style.display = 'none';
            viewBlock.style.display = 'flex';
            // Сброс полей паролей
            if (document.getElementById('editBlock').style.display === 'flex') {
                document.getElementById('currentPass').value = '';
                document.getElementById('newPass').value = '';
                document.getElementById('newPassAgain').value = '';
            }
        });
    }

    // Загрузка фото
    const profilePhoto = document.getElementById('profilePhoto');
    const editPhoto = document.getElementById('editPhoto');
    const photoUpload = document.getElementById('photoUpload');

    if (editPhoto) {
        editPhoto.addEventListener('click', () => photoUpload.click());
    }

    if (photoUpload) {
        photoUpload.addEventListener('change', async function (e) {
            const file = e.target.files[0];
            if (!file) return;

            // Проверка размера (макс 2MB)
            if (file.size > 2 * 1024 * 1024) {
                alert('Файл слишком большой. Максимум 2MB');
                return;
            }

            // Проверка типа
            if (!file.type.startsWith('image/')) {
                alert('Можно загружать только изображения');
                return;
            }

            const formData = new FormData();
            formData.append('photo', file);

            try {
                const response = await fetch('../func/updateProfile.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    // Обновляем фото на странице + добавляем параметр времени чтобы сбросить кеш
                    const newPhotoSrc = result.photoPath + '?t=' + Date.now();
                    if (profilePhoto) profilePhoto.src = newPhotoSrc;
                    if (editPhoto) editPhoto.src = newPhotoSrc;
                } else {
                    alert(result.message);
                }
            } catch (error) {
                alert('Ошибка загрузки');
            }
        });
    }

    // Сохранение данных
    const saveBtn = document.getElementById('saveBtn');
    if (saveBtn) {
        saveBtn.addEventListener('click', async function () {
            // Защита от выполнения, если блок редактирования скрыт
            if (!editBlock || editBlock.style.display !== 'flex') {
                return;
            }

            // Блокируем кнопку от повторных нажатий
            saveBtn.disabled = true;

            try {
                const formData = new FormData();

                // Получаем элементы формы с проверкой существования
                const surnameInput = document.getElementById('surname');
                const nameInput = document.getElementById('name');
                const secNameInput = document.getElementById('secName');
                const phoneInput = document.getElementById('phone');
                const currentPassInput = document.getElementById('currentPass');
                const newPassInput = document.getElementById('newPass');
                const newPassAgainInput = document.getElementById('newPassAgain');

                // Проверяем, что все обязательные поля существуют
                if (!surnameInput || !nameInput || !phoneInput) {
                    throw new Error('Критические поля формы не найдены');
                }

                // Добавляем данные в FormData
                formData.append('surname', surnameInput.value);
                formData.append('name', nameInput.value);
                formData.append('secName', secNameInput ? secNameInput.value : '');

                // Очищаем телефон от форматирования
                const rawPhone = phoneInput.value.replace(/\D/g, '');
                formData.append('phone', rawPhone);

                // Пароли (только если поля существуют)
                formData.append('currentPass', currentPassInput ? currentPassInput.value : '');
                formData.append('newPass', newPassInput ? newPassInput.value : '');
                formData.append('newPassAgain', newPassAgainInput ? newPassAgainInput.value : '');

                // Указываем, что это обновление данных (не фото)
                formData.append('action', 'update_data');

                // Отправляем запрос
                const response = await fetch('../func/updateProfile.php', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.message);

                    // Обновляем отображаемые данные
                    const viewSurname = document.getElementById('viewSurname');
                    const viewName = document.getElementById('viewName');
                    const viewSecName = document.getElementById('viewSecName');
                    const viewPhone = document.getElementById('viewPhone');

                    if (viewSurname) viewSurname.textContent = surnameInput.value;
                    if (viewName) viewName.textContent = nameInput.value;
                    if (viewSecName) viewSecName.textContent = secNameInput ? secNameInput.value : '';
                    if (viewPhone) viewPhone.textContent = phoneInput.value;

                    // Возвращаемся к просмотру
                    if (editBlock) editBlock.style.display = 'none';
                    if (viewBlock) viewBlock.style.display = 'flex';

                    // Очищаем поля паролей
                    if (currentPassInput) currentPassInput.value = '';
                    if (newPassInput) newPassInput.value = '';
                    if (newPassAgainInput) newPassAgainInput.value = '';
                } else {
                    alert(result.message);
                }
            } catch (error) {
                console.error('Ошибка:', error);
                alert('Произошла ошибка при сохранении');
            } finally {
                // В любом случае разблокируем кнопку
                saveBtn.disabled = false;
            }
        });
    }
    let currentCancelId = null;

    // Открытие попапа с сохранением ID
    document.querySelectorAll('.cancel').forEach(btn => {
        btn.addEventListener('click', function () {
            currentCancelId = this.dataset.id;
            showPopup('cancel-popup');
        });
    });

    // Обработчик подтверждения (на кнопку с ID)
    const confirmBtn = document.getElementById('confirmCancelBtn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', async function () {
            if (!currentCancelId) return;

            try {
                const response = await fetch('../func/cancelAppointment.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ appointment_id: currentCancelId })
                });

                const result = await response.json();

                if (result.success) {
                    alert('Запись отменена');

                    // Закрываем попап
                    const popup = document.getElementById('cancel-popup');
                    const overlay = document.getElementById('cancel-popup-overlay');
                    if (popup && overlay) {
                        popup.classList.remove('show');
                        overlay.classList.remove('show');
                        document.body.style.overflow = 'auto';
                    }

                    // Удаляем карточку из DOM
                    const btnList = document.querySelectorAll('.cancel');
                    let foundBtn = null;
                    btnList.forEach(btn => {
                        if (btn.dataset.id === currentCancelId) {
                            foundBtn = btn;
                        }
                    });

                    if (foundBtn) {
                        const card = foundBtn.closest('.app');
                        if (card) {
                            card.remove();

                            // Если записей больше нет — показываем заглушку
                            const container = document.querySelector('.closestApps');
                            if (container.children.length === 0) {
                                container.innerHTML = `
                                <div class="emptyState">
                                    <p>У вас нет предстоящих записей</p>
                                    <a href="./services.php" class="commonBtn">Записаться на приём</a>
                                </div>
                            `;
                            }
                        }
                    }

                    currentCancelId = null;
                } else {
                    alert(result.message);
                }
            } catch (error) {
                alert('Ошибка при отмене');
            }
        });
    }
    let rescheduleId = null;

    document.querySelectorAll('.reschedule').forEach(btn => {
        btn.addEventListener('click', async function () {
            console.log('Клик по переносу, ID =', this.dataset.id);
            rescheduleId = this.dataset.id;

            // Получаем данные текущей записи
            const response = await fetch(`../func/getAppointment.php?id=${rescheduleId}`);
            const data = await response.json();

            // Сохраняем данные в локальные переменные
            const doctor = {
                id: data.doctor_id,
                name: data.doctor_name,
                photo: data.doctor_photo
            };
            const service = {
                id: data.service_id,
                name: data.service_name,
                price: data.service_price
            };
            const schedule = data.schedule;

            // Показываем шаг с датой и скрываем время
            document.querySelector('#reschedule-popup #stepDate').style.display = 'flex';
            document.querySelector('#reschedule-popup #stepTime').style.display = 'none';
            document.querySelector('#reschedule-popup #stepDate').style.visibility = 'visible';
            document.querySelector('#reschedule-popup #stepTime').style.visibility = 'hidden';

            // Заполняем шапку
            const stepDate = document.querySelector('#reschedule-popup #stepDate');
            const dateDataBlock = stepDate.querySelector('.data');
            dateDataBlock.innerHTML = `
            <img src="../img/avatars/${doctor.photo || 'none.svg'}" alt="">
            <div class="vert">
                <p>${doctor.name}</p>
                <p>${service.name}</p>
                <p>${service.price} ₽</p>
            </div>
        `;

            // Инициализируем flatpickr и сохраняем в переменную
            const fp = flatpickr('#reschedule-popup #dateInput', {
                locale: 'ru',
                minDate: 'today',
                maxDate: new Date().fp_incr(60),
                dateFormat: 'd.m.Y',
                disable: [function (date) {
                    if (!schedule || schedule.length === 0) return true;
                    return !schedule.includes(date.getDay());
                }],
                onChange: function (selectedDates, dateStr) {
                    document.querySelector('#reschedule-popup #madeAppDate').disabled = false;
                    window.rescheduleDate = dateStr;
                }
            });

            // Устанавливаем текущую дату
            const currentDate = new Date(data.app_datetime);
            fp.setDate(currentDate, true);

            // Переход на шаг времени
            document.querySelector('#reschedule-popup #madeAppDate').onclick = function () {
                document.querySelector('#reschedule-popup #stepDate').style.display = 'none';
                document.querySelector('#reschedule-popup #stepTime').style.display = 'flex';
                document.querySelector('#reschedule-popup #stepDate').style.visibility = 'hidden';
                document.querySelector('#reschedule-popup #stepTime').style.visibility = 'visible';

                const [day, month, year] = window.rescheduleDate.split('.');
                const dateObj = new Date(year, month - 1, day);
                document.querySelector('#reschedule-popup .selected-date-display').textContent =
                    dateObj.toLocaleDateString('ru-RU', {
                        weekday: 'short',
                        day: 'numeric',
                        month: 'numeric',
                        year: 'numeric'
                    });

                fetch(`../func/getFreeTime.php?doctor_id=${doctor.id}&date=${window.rescheduleDate}`)
                    .then(response => response.text())
                    .then(data => {
                        document.querySelector('#reschedule-popup .time-grid').innerHTML = data;
                    });
            };

            // Выбор времени
            document.querySelector('#reschedule-popup .time').onclick = function (e) {
                const slot = e.target.closest('.time-slot');
                if (!slot) return;

                document.querySelectorAll('#reschedule-popup .time-slot').forEach(s => s.classList.remove('selected'));
                slot.classList.add('selected');
                window.rescheduleTime = slot.dataset.time;
                document.querySelector('#reschedule-popup #madeAppReschedule').disabled = false;
            };
            // Возврат на шаг даты
            document.querySelector('#reschedule-popup .time-header').onclick = function () {
                document.querySelector('#reschedule-popup #stepTime').style.display = 'none';
                document.querySelector('#reschedule-popup #stepDate').style.display = 'flex';
                document.querySelector('#reschedule-popup #stepTime').style.visibility = 'hidden';
                document.querySelector('#reschedule-popup #stepDate').style.visibility = 'visible';
            };
            // Отправка
            document.querySelector('#reschedule-popup #madeAppReschedule').onclick = function () {
                fetch('../func/rescheduleAppointment.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        appointment_id: rescheduleId,
                        date: window.rescheduleDate,
                        time: window.rescheduleTime
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Запись перенесена');
                            hidePopup('reschedule-popup');
                            location.reload();
                        } else {
                            alert(data.message);
                        }
                    });
            };

            showPopup('reschedule-popup');
        });
    });
    // === УДАЛЕНИЕ АВАТАРКИ ===
    setTimeout(() => {
        const deleteBtn = document.getElementById('deleteAvatarBtn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', async function (e) {
                e.preventDefault();
                if (!confirm('Удалить фото?')) return;

                const formData = new FormData();
                formData.append('delete_photo', '1');

                try {
                    const response = await fetch('../func/updateProfile.php', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();

                    if (result.success) {
                        document.getElementById('profilePhoto').src = '../img/avatars/none.svg';
                        document.getElementById('editPhoto').src = '../img/avatars/none.svg';
                        deleteBtn.remove();
                        alert('Фото удалено');
                    } else {
                        alert(result.message);
                    }
                } catch (error) {
                    alert('Ошибка при удалении');
                }
            });
        }
    }, 200);
});