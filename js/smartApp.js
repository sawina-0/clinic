// Умная запись - анализ симптомов
(function () {
    const defineBtn = document.getElementById('define');
    const symptomsTextarea = document.getElementById('sympSearch');
    const specialistsContainer = document.querySelector('.specialistsCard');
    const recommendContainer = document.getElementById('recommendTitleContainer');

    if (!defineBtn || !symptomsTextarea || !specialistsContainer || !recommendContainer) return;

    defineBtn.addEventListener('click', async function () {
        const symptoms = symptomsTextarea.value.trim();

        if (!symptoms) {
            alert('Пожалуйста, опишите ваши симптомы');
            return;
        }

        const originalText = defineBtn.textContent;
        defineBtn.textContent = 'Анализируем...';
        defineBtn.disabled = true;
        specialistsContainer.innerHTML = '<div class="loading">Анализируем симптомы...</div>';
        recommendContainer.innerHTML = '';

        try {
            const response = await fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'symptoms=' + encodeURIComponent(symptoms)
            });

            const data = await response.json();

            if (data.error) {
                specialistsContainer.innerHTML = `<div class="nothingFound">${data.error}</div>`;
                recommendContainer.innerHTML = '';
                return;
            }

            if (data.success && data.doctors.length > 0) {
                // Выводим заголовок в отдельный контейнер
                recommendContainer.innerHTML = `<p style="margin-bottom: 1rem;">Рекомендуем вам записаться к ${data.specialist_name} (${data.direction_name})</p>`;

                let html = '';
                data.doctors.forEach(doctor => {
                    const photoPath = doctor.photo
                        ? `../img/avatars/${doctor.photo}`
                        : `../img/avatars/none.svg`;

                    const fullName = `${doctor.surname} ${doctor.name} ${doctor.sec_name || ''}`.trim();

                    html += `
                        <div class="doctorCard" data-doctor-id="${doctor.doctor_id}">
                            <img src="${photoPath}" alt="${fullName}">
                            <p>${fullName}</p>
                            <p>${data.specialist_name}</p>
                            <p>Стаж: ${doctor.exp} лет</p>
                            <button class="commonBtn" data-type="doctor" 
                                    data-doctor-id="${doctor.doctor_id}"
                                    data-doctor-name="${fullName}"
                                    data-doctor-photo="${doctor.photo || 'none.svg'}">Записаться</button>
                        </div>
                    `;
                });

                specialistsContainer.innerHTML = html;
            }
        } catch (error) {
            console.error('Ошибка:', error);
            specialistsContainer.innerHTML = '<div class="nothingFound">Произошла ошибка. Попробуйте позже.</div>';
            recommendContainer.innerHTML = '';
        } finally {
            defineBtn.textContent = originalText;
            defineBtn.disabled = false;
        }
    });
})();

// Запись на приём из карточки врача (для умной записи)
document.addEventListener('click', function (e) {
    const btn = e.target.closest('.doctorCard .commonBtn');
    if (!btn) return;

    // Проверка роли через глобальные переменные
    if (!window.isLogged) {
        window.location.href = '../pages/auth.php';
        return;
    }
    if (!window.isPatient) {
        alert('Работники клиники не могут записываться на приём');
        return;
    }

    const doctorCard = btn.closest('.doctorCard');
    const doctorId = doctorCard.dataset.doctorId;
    const doctorName = doctorCard.querySelector('p:first-of-type')?.innerText || '';
    const img = doctorCard.querySelector('img');
    const doctorPhoto = img ? img.src.split('/').pop() : 'none.svg';

    // Сохраняем глобально
    window.selectedDoctor = {
        id: doctorId,
        name: doctorName,
        photo: doctorPhoto
    };

    // Загружаем услугу-консультацию и открываем модалку
    fetch(`../func/getConsultationService.php?doctor_id=${doctorId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }

            window.selectedService = {
                id: data.service_id,
                name: data.service_name,
                price: data.price
            };

            // Открываем модалку на шаге выбора даты
            if (typeof openSmartAppBooking === 'function') {
                openSmartAppBooking();
            } else {
                console.error('Функция openSmartAppBooking не найдена');
                alert('Ошибка инициализации записи');
            }
        })
        .catch(err => {
            console.error('Ошибка загрузки услуги:', err);
            alert('Не удалось загрузить услугу для записи');
        });
});