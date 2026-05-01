document.addEventListener('DOMContentLoaded', function() {
    // Инициализация календаря для поля с классом .calendar
    function initFlatpickrForElement(element, options = {}) {
        if (!element || element._flatpickr) return;
        
        const defaultOptions = {
            locale: 'ru',
            dateFormat: 'd.m.Y',
            ...options
        };
        
        return flatpickr(element, defaultOptions);
    }
    
    // Инициализация выбора времени (для записей)
    function initTimeSlots(container, doctorId, date, onSelect) {
        console.log('initTimeSlots вызван, doctorId:', doctorId, 'date:', date);
        if (!doctorId || !date) return;
        
        fetch(`../func/getFreeTime.php?doctor_id=${doctorId}&date=${date}`)
            .then(response => response.text())
            .then(data => {
                console.log('Ответ от getFreeTime.php:', data);
                container.innerHTML = data;
                container.style.display = 'grid';
                
                // Обработка выбора времени
                container.querySelectorAll('.time-slot').forEach(slot => {
                    slot.addEventListener('click', function() {
                        container.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
                        this.classList.add('selected');
                        if (onSelect) onSelect(this.dataset.time);
                    });
                });
            });
    }
    
    // Делаем функции глобальными для использования в других скриптах
    window.adminDateTime = {
        initFlatpickr: initFlatpickrForElement,
        initTimeSlots: initTimeSlots
    };

    document.addEventListener('click', function(e) {
        const backBtn = e.target.closest('.time-header');
        if (backBtn) {
            const timeBlock = backBtn.closest('#stepTimeAdd');
            const dateBlock = document.querySelector('#stepDateAdd');
            if (timeBlock) timeBlock.style.display = 'none';
            if (dateBlock) dateBlock.style.display = 'block';
        }
    });
});