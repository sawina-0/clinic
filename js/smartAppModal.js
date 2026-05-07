// smartAppModal.js — управление модалкой записи для умной записи
(function() {
    let selectedDate = null;
    let selectedTime = null;

    function openSmartAppBooking() {
        // Проверяем, что всё необходимое есть
        if (!window.selectedDoctor || !window.selectedService) {
            alert('Ошибка: данные для записи не загружены');
            return;
        }

        // Загружаем расписание врача
        fetch(`../func/getDoctorSchedule.php?doctor_id=${window.selectedDoctor.id}`)
            .then(response => response.json())
            .then(schedule => {
                window.doctorSchedule = schedule;
                
                // Показываем модалку
                showPopup('app-popup');
                
                // Скрываем шаг выбора услуги (если вдруг есть), показываем шаг даты
                const stepService = document.getElementById('stepService');
                const stepDate = document.getElementById('stepDate');
                const stepTime = document.getElementById('stepTime');
                
                if (stepService) stepService.style.display = 'none';
                stepDate.style.display = 'flex';
                stepDate.style.visibility = 'visible';
                stepTime.style.display = 'none';
                stepTime.style.visibility = 'hidden';
                
                // Заполняем шапку в stepDate
                const dateDataBlock = stepDate.querySelector('.data');
                const photoHtml = window.selectedDoctor.photo && window.selectedDoctor.photo !== 'none.svg'
                    ? `<img src="../img/avatars/${window.selectedDoctor.photo}" alt="">`
                    : '<img src="../img/avatars/none.svg" alt="">';
                
                dateDataBlock.innerHTML = `
                    ${photoHtml}
                    <div class="vert">
                        <p>${window.selectedDoctor.name}</p>
                        <p>${window.selectedService.name}</p>
                        <p>${window.selectedService.price} ₽</p>
                    </div>
                `;
                
                // Инициализируем Flatpickr
                flatpickr('#dateInput', {
                    locale: 'ru',
                    minDate: 'today',
                    maxDate: new Date().fp_incr(60),
                    dateFormat: 'd.m.Y',
                    disable: [
                        function(date) {
                            const day = date.getDay(); // 0 вс, 1 пн ... 6 сб
                            if (!window.doctorSchedule || window.doctorSchedule.length === 0) return true;
                            return !window.doctorSchedule.includes(day);
                        }
                    ],
                    onChange: function(selectedDates, dateStr) {
                        document.getElementById('madeAppDate').disabled = false;
                        selectedDate = dateStr;
                    }
                });
            });
    }

    // Кнопка "выбрать время" (переход на шаг времени)
    document.getElementById('madeAppDate')?.addEventListener('click', function() {
        if (!selectedDate) return;
        
        document.getElementById('madeAppTime').disabled = true;
        
        // Переход на шаг времени
        document.getElementById('stepDate').style.display = 'none';
        document.getElementById('stepTime').style.display = 'flex';
        document.getElementById('stepTime').style.visibility = 'visible';
        
        // Загружаем свободное время
        fetch(`../func/getFreeTime.php?doctor_id=${window.selectedDoctor.id}&date=${selectedDate}`)
            .then(response => response.text())
            .then(html => {
                document.querySelector('.time-grid').innerHTML = html;
            });
        
        // Заполняем шапку в stepTime
        const timeDataBlock = document.querySelector('#stepTime .data');
        const photoHtml = window.selectedDoctor.photo && window.selectedDoctor.photo !== 'none.svg'
            ? `<img src="../img/avatars/${window.selectedDoctor.photo}" alt="">`
            : '<img src="../img/avatars/none.svg" alt="">';
        
        timeDataBlock.innerHTML = `
            ${photoHtml}
            <div class="vert">
                <p>${window.selectedDoctor.name}</p>
                <p>${window.selectedService.name}</p>
                <p>${window.selectedService.price} ₽</p>
            </div>
        `;
        
        // Форматируем выбранную дату для отображения
        const [day, month, year] = selectedDate.split('.');
        const dateObj = new Date(year, month - 1, day);
        document.querySelector('.selected-date-display').textContent =
            dateObj.toLocaleDateString('ru-RU', {
                weekday: 'short',
                day: 'numeric',
                month: 'numeric',
                year: 'numeric'
            });
    });

    // Выбор времени
    document.querySelector('.time-grid')?.addEventListener('click', function(e) {
        const slot = e.target.closest('.time-slot');
        if (!slot || slot.disabled) return;
        
        document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
        slot.classList.add('selected');
        selectedTime = slot.dataset.time;
        document.getElementById('madeAppTime').disabled = false;
    });

    // Кнопка "записаться"
    document.getElementById('madeAppTime')?.addEventListener('click', function() {
        fetch('../func/createAppointment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                doctor_id: window.selectedDoctor.id,
                service_id: window.selectedService.id,
                date: selectedDate,
                time: selectedTime
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Запись создана');
                hidePopup('app-popup');
                location.reload(); // или обновить список записей
            } else {
                alert('Ошибка: ' + data.message);
            }
        });
    });

    // Кнопка "<" в шаге времени — возврат к дате
    document.querySelector('.back-to-date')?.addEventListener('click', function() {
        document.getElementById('stepTime').style.display = 'none';
        document.getElementById('stepDate').style.display = 'flex';
        document.getElementById('stepDate').style.visibility = 'visible';
        document.getElementById('madeAppTime').disabled = true;
        selectedTime = null;
    });

    // Клик на шапку в stepTime (по аналогии с doctorsModalData)
    document.querySelector('#stepTime .data')?.addEventListener('click', function() {
        document.getElementById('stepTime').style.display = 'none';
        document.getElementById('stepDate').style.display = 'flex';
        document.getElementById('stepDate').style.visibility = 'visible';
        document.getElementById('madeAppTime').disabled = true;
        selectedTime = null;
    });

    // Экспортируем функцию открытия
    window.openSmartAppBooking = openSmartAppBooking;
})();