const hat = document.querySelector('.hat'); 
const burger = document.querySelector('.burger');

window.addEventListener('scroll', function() {
    if (!document.querySelector('.mobileMenu')?.classList.contains('show')) {
        if (hat) hat.classList.toggle('scrolled', window.scrollY > 20);
        if (burger) burger.classList.toggle('scrolled', window.scrollY > 20);
    }
});


function showPopup(id){
    const popupOverlay = document.getElementById(`${id}-overlay`);
    const popupContainer = document.getElementById(id);
    popupOverlay.classList.add('show');
    popupContainer.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function hidePopup(id){
    
    const popupOverlay = document.getElementById(`${id}-overlay`);
    const popupContainer = document.getElementById(id);
    
    if (popupOverlay) popupOverlay.classList.remove('show');
    if (popupContainer) popupContainer.classList.remove('show');
    document.body.style.overflow = 'auto';

    // Шаги модалки записи — только если они существуют на странице
    const stepService = document.getElementById('stepService');
    const stepDate = document.getElementById('stepDate');
    const stepTime = document.getElementById('stepTime');
    const dateInput = document.getElementById('dateInput');
    const madeAppService = document.getElementById('madeAppService');
    const madeAppDate = document.getElementById('madeAppDate');
    const madeAppTime = document.getElementById('madeAppTime');

    if (stepService) {
        stepService.style.display = 'flex';
        stepService.style.visibility = 'visible';
    }
    if (stepDate) {
        stepDate.style.display = 'none';
        stepDate.style.visibility = 'hidden';
    }
    if (stepTime) {
        stepTime.style.display = 'none';
        stepTime.style.visibility = 'hidden';
    }
    if (dateInput) {
        dateInput.value = '';
    }
    if (madeAppService) {
        madeAppService.disabled = true;
    }
    if (madeAppDate) {
        madeAppDate.disabled = true;
    }
    if (madeAppTime) {
        madeAppTime.disabled = true;
    }

    document.querySelectorAll('.custom-select-wrapper').forEach(w => {
        w.classList.remove('open');
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Обработчик для всех оверлеев
    document.querySelectorAll('.popupOverlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            // Проверяем, что кликнули именно на оверлей (а не на его детей)
            if (e.target === this) {
                const popupId = this.id.replace('-overlay', '');
                hidePopup(popupId);
            }
        });
    });

    // Запрещаем всплытие кликов для содержимого попапов
    document.querySelectorAll('.popupContainer').forEach(container => {
        container.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
});