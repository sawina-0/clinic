// Инициализация только внутри модалки
function initModalSelect() {
    const modal = document.getElementById('app-popup');
    if (!modal) return;

    const trigger = modal.querySelector('.custom-select-trigger');
    const wrapper = modal.querySelector('.custom-select-wrapper');
    const optionsContainer = modal.querySelector('.options-container');

    if (!trigger || !wrapper || !optionsContainer) return;

    // Удаляем старые обработчики (через замену)
    const newTrigger = trigger.cloneNode(true);
    trigger.parentNode.replaceChild(newTrigger, trigger);

    const freshTrigger = modal.querySelector('.custom-select-trigger');
    const freshWrapper = modal.querySelector('.custom-select-wrapper');

    // Открытие/закрытие
    freshTrigger.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        freshWrapper.classList.toggle('open');
    });

    // Выбор опции
    optionsContainer.addEventListener('click', function(ev) {
        const option = ev.target.closest('.serviceOption');
        if (!option) return;

        const triggerSpan = freshTrigger.querySelector('span');
        const optionText = option.querySelector('p').textContent;
        const optionPrice = option.querySelector('span')?.textContent || '';
        triggerSpan.textContent = optionText + (optionPrice ? ' — ' + optionPrice : '');

        modal.querySelectorAll('.serviceOption').forEach(opt => {
            opt.classList.remove('selected');
        });
        option.classList.add('selected');
        freshWrapper.classList.remove('open');

        let detail = {};
        if (option.dataset.serviceId) {
            detail = { type: 'service', id: option.dataset.serviceId, name: option.dataset.serviceName, price: option.dataset.servicePrice };
        } else if (option.dataset.doctorId) {
            detail = { type: 'doctor', id: option.dataset.doctorId, name: option.dataset.doctorName, photo: option.dataset.doctorPhoto };
        }

        document.dispatchEvent(new CustomEvent('serviceSelected', { detail }));
    });

    // Поиск внутри селекта
    const searchInput = modal.querySelector('.search-input');
    if (searchInput) {
        searchInput.addEventListener('input', function(ev) {
            const searchText = ev.target.value.toLowerCase();
            modal.querySelectorAll('.serviceOption').forEach(opt => {
                const text = opt.textContent.toLowerCase();
                opt.style.display = text.includes(searchText) ? '' : 'none';
            });
        });
    }
}

// Запускаем при загрузке
document.addEventListener('DOMContentLoaded', initModalSelect);

// И при открытии модалки (если контент подгружается позже)
document.addEventListener('click', function(e) {
    if (e.target.closest('.commonBtn[data-doctor-id]') || e.target.closest('.commonBtn[data-service-id]')) {
        setTimeout(initModalSelect, 200);
    }
});