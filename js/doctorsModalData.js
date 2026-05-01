document.addEventListener('DOMContentLoaded', function() {
    let selectedDoctor = {
        id: null,
        name: '',
        photo: ''
    };
    
    let selectedService = {
        id: null,
        name: '',
        price: null
    };
    let doctorSchedule = [];
    document.addEventListener('serviceSelected', function(e) {
        if (e.detail.type === 'service') {
            selectedService.id = e.detail.id;
            selectedService.name = e.detail.name;
            selectedService.price = e.detail.price;
        } else if (e.detail.type === 'doctor') {
            selectedDoctor.id = e.detail.id;
            selectedDoctor.name = e.detail.name;
            selectedDoctor.photo = e.detail.photo;
            fetch('../func/getDoctorSchedule.php?doctor_id=' + selectedDoctor.id)
                .then(response => response.json())
                .then(data => {
                    doctorSchedule = data;
                });
        }
        
        // Активируем кнопку "выбрать дату"
        document.getElementById('madeAppService').disabled = false;
    });
    document.getElementById('stepDate').addEventListener('click', function(e) {
        const dataBlock = e.target.closest('.data');
        if (!dataBlock) return;
        
        const stepService = document.getElementById('stepService');
        const stepDate = document.getElementById('stepDate');
        
        // Показываем шаг выбора услуги
        stepService.style.display = 'flex';
        stepService.style.visibility = 'visible';
        
        // Прячем шаг с датой
        stepDate.style.display = 'none';
        stepDate.style.visibility = 'hidden';
    });
    // Делегирование для кнопок "записаться"
    const doctorsList = document.getElementById('doctorsList');
    if (doctorsList) {
        doctorsList.addEventListener('click', function(e) {
            const btn = e.target.closest('.commonBtn');
            if (!btn) return;
            const type = btn.dataset.type;

            if (!window.isLogged) {
                window.location.href = '../pages/auth.php';
                return;
            }
            if (!window.isPatient) {
                alert('Работники клиники не могут записываться на приём');
                return;
            }

            if (type === 'doctor') {
                selectedDoctor.id = btn.dataset.doctorId;
                selectedDoctor.name = btn.dataset.doctorName;
                selectedDoctor.photo = btn.dataset.doctorPhoto;

                const stepService = document.getElementById('stepService');
                const dataBlock = stepService.querySelector('.data');
                let photoHtml = '<img src="../img/avatars/none.svg" alt="">';
                if (selectedDoctor.photo && selectedDoctor.photo !== '') {
                    photoHtml = '<img src="../img/avatars/' + selectedDoctor.photo + '" alt="' + selectedDoctor.name + '">';
                }
                dataBlock.innerHTML = photoHtml + '<p>' + selectedDoctor.name + '</p>';

                const optionsContainer = stepService.querySelector('.options-container');
                optionsContainer.innerHTML = 'Загрузка...';
                fetch('../func/getDoctorServices.php?doctor_id=' + selectedDoctor.id)
                    .then(response => response.text())
                    .then(data => {
                        optionsContainer.innerHTML = data;
                        stepService.querySelector('.custom-select-trigger span').textContent = 'Выберите услугу';
                    });
                fetch('../func/getDoctorSchedule.php?doctor_id=' + selectedDoctor.id)
                    .then(response => response.json())
                    .then(data => doctorSchedule = data);

            } 
            showPopup('app-popup');
        });
    }

    // Делегирование для кнопок "записаться" на странице услуг
    const servicesList = document.getElementById('servicesList');
    if (servicesList) {
        servicesList.addEventListener('click', function(e) {
            const btn = e.target.closest('.commonBtn');
            if (!btn) return;
            const type = btn.dataset.type;

            if (!window.isLogged) {
                window.location.href = '../pages/auth.php';
                return;
            }
            if (!window.isPatient) {
                alert('Работники клиники не могут записываться на приём');
                return;
            }

            if (type === 'service') {
                selectedService.id = btn.dataset.serviceId;
                selectedService.name = btn.dataset.serviceName;
                selectedService.price = btn.dataset.servicePrice;

                const stepService = document.getElementById('stepService');
                stepService.querySelector('.data').innerHTML = `<p>${selectedService.name}</p><p>${selectedService.price} ₽</p>`;

                const optionsContainer = stepService.querySelector('.options-container');
                optionsContainer.innerHTML = 'Загрузка...';
                fetch('../func/getServiceDoctors.php?service_id=' + selectedService.id)
                    .then(response => response.text())
                    .then(data => {
                        optionsContainer.innerHTML = data;
                        stepService.querySelector('.custom-select-trigger span').textContent = 'Выберите врача';
                    });
            }

            showPopup('app-popup');
        });
    }
    
    // Делегирование для выбора услуги
    const madeAppBtn = document.getElementById('madeAppService');
    if (madeAppBtn) {
        madeAppBtn.disabled = true;
        
        // Вешаем обработчик на кнопку "выбрать дату"
        madeAppBtn.addEventListener('click', function() {
            // Переход на шаг с датой
            const stepService = document.getElementById('stepService');
            const stepDate = document.getElementById('stepDate');
            
            // Прячем шаг выбора услуги
            stepService.style.display = 'none';
            stepService.style.visibility = 'hidden';
            
            // Показываем шаг с датой
            stepDate.style.display = 'flex';
            stepDate.style.visibility = 'visible';
            
            // Заполняем данные в шапке stepDate
            const dateDataBlock = stepDate.querySelector('.data');
            const photoHtml = selectedDoctor.photo 
                ? `<img src="../img/avatars/${selectedDoctor.photo}" alt="">`
                : '<img src="../img/avatars/none.svg" alt="">';
            
            // Сохраняем локально
            const doctorName = selectedDoctor.name;
            const serviceName = selectedService.name;
            const servicePrice = selectedService.price;

            dateDataBlock.innerHTML = `
                ${photoHtml}
                <div class="vert">
                    <p>${doctorName}</p>
                    <p>${serviceName}</p>
                    <p>${servicePrice} ₽</p>
                </div>
            `;

            flatpickr('#dateInput', {
                locale: 'ru',
                minDate: 'today',
                maxDate: new Date().fp_incr(60),
                dateFormat: 'd.m.Y',
                disable: [
                    function(date) {
                        // Получаем день недели (0 = вс, 1 = пн, ..., 6 = сб)
                        const day = date.getDay();
                        
                        // Если у врача нет расписания или день не входит в его рабочие дни
                        if (!doctorSchedule || doctorSchedule.length === 0) return true;
                        
                        // Возвращаем true, если день НЕ рабочий
                        return !doctorSchedule.includes(day);
                    }
                ],
                onChange: function(selectedDates, dateStr) {
                    document.getElementById('madeAppDate').disabled = false;
                    selectedDate = dateStr;
                }
            });
        });
    }
    document.getElementById('madeAppDate').addEventListener('click', function() {
        document.getElementById('madeAppTime').disabled = true;
        // Переход на шаг 3
        document.getElementById('stepDate').style.display = 'none';
        document.getElementById('stepDate').style.visibility = 'hidden';
        document.getElementById('stepTime').style.display = 'flex';
        document.getElementById('stepTime').style.visibility = 'visible';
        
        // Загружаем свободное время для выбранного врача и даты
        fetch(`../func/getFreeTime.php?doctor_id=${selectedDoctor.id}&date=${selectedDate}`)
            .then(response => response.text())
            .then(data => {
                document.querySelector('.time-grid').innerHTML = data;
            });

        const timeDataBlock = document.querySelector('#stepTime .data');
        const photoHtml = selectedDoctor.photo 
            ? `<img src="../img/avatars/${selectedDoctor.photo}" alt="">`
            : '<img src="../img/avatars/none.svg" alt="">';

        timeDataBlock.innerHTML = `
            ${photoHtml}
            <div class="vert">
                <p>${selectedDoctor.name}</p>
                <p>${selectedService.name}</p>
                <p>${selectedService.price} ₽</p>
            </div>
        `;
        // Разбираем строку "дд.мм.гггг"
        const [day, month, year] = selectedDate.split('.');
        const dateObj = new Date(year, month - 1, day);

        document.querySelector('.selected-date-display').textContent = 
            dateObj.toLocaleDateString('ru-RU', {
                weekday: 'short',
                day: 'numeric',
                month: 'numeric',
                year: 'numeric'
            });
        document.querySelector('.time').addEventListener('click', function(e) {
            const slot = e.target.closest('.time-slot');
            if (!slot || slot.disabled) return;
            
            // Убираем выделение с других
            document.querySelectorAll('.time-slot').forEach(s => {
                s.classList.remove('selected');
            });
            
            // Выделяем выбранное
            slot.classList.add('selected');
            
            // Сохраняем выбранное время
            selectedTime = slot.dataset.time;
            
            // Активируем кнопку записи
            document.getElementById('madeAppTime').disabled = false;
        });

    });
    document.getElementById('madeAppTime').addEventListener('click', function() {
        fetch('../func/createAppointment.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                doctor_id: selectedDoctor.id,
                service_id: selectedService.id,
                date: selectedDate,
                time: selectedTime
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Запись создана');
                hidePopup('app-popup');
            } else {
                alert('Ошибка: ' + data.message);
            }
        });
    });
    document.querySelector('#stepTime .data').addEventListener('click', function() {
        document.getElementById('stepTime').style.display = 'none';
        document.getElementById('stepTime').style.visibility = 'hidden';
        document.getElementById('stepService').style.display = 'flex';
        document.getElementById('stepService').style.visibility = 'visible';;
    });
    document.querySelector('.time-header').addEventListener('click', function() {
        document.getElementById('stepTime').style.display = 'none';
        document.getElementById('stepTime').style.visibility = 'hidden';
        document.getElementById('stepDate').style.display = 'flex';
        document.getElementById('stepDate').style.visibility = 'visible';
    });
});