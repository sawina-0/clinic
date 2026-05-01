document.addEventListener('DOMContentLoaded', function() {
    // Открытие/закрытие кастомных селектов
    document.addEventListener('click', function(e) {
        const trigger = e.target.closest('.custom-select-trigger');
        if (trigger) {
            e.preventDefault();
            e.stopPropagation();
            
            const wrapper = trigger.closest('.custom-select-wrapper');
            document.querySelectorAll('.custom-select-wrapper').forEach(w => {
                if (w !== wrapper) w.classList.remove('open');
            });
            wrapper.classList.toggle('open');
            return;
        }
        
        if (!e.target.closest('.custom-select-wrapper')) {
            document.querySelectorAll('.custom-select-wrapper').forEach(w => {
                w.classList.remove('open');
            });
        }
    });

    // Выбор опции в фильтре
    document.addEventListener('click', function(e) {
        const option = e.target.closest('.filter-option');
        if (!option) return;
        
        const wrapper = option.closest('.custom-select-wrapper');
        const triggerSpan = wrapper.querySelector('.custom-select-trigger span');
        triggerSpan.textContent = option.textContent;
        wrapper.querySelectorAll('.filter-option').forEach(opt => opt.classList.remove('selected'));
        option.classList.add('selected');
        
        wrapper.dataset.value = option.dataset.value;
        wrapper.classList.remove('open');
        
        const form = wrapper.closest('form');
        if (form) {
            const event = new Event('change', { bubbles: true });
            form.dispatchEvent(event);

            // Добавляем фильтр чендж для селектов в модалке
            wrapper.dispatchEvent(new CustomEvent('filterChange', { bubbles: true }));
        }
    });

    // Поиск внутри селекта
    document.addEventListener('input', function(ev) {
        const input = ev.target.closest('.custom-select-dropdown .search-input');
        if (!input) return;
        
        ev.stopPropagation();
        const searchText = input.value.toLowerCase();
        const container = input.closest('.custom-select-dropdown').querySelector('.options-container');
        const options = container.querySelectorAll('.serviceOption, .filter-option');
        
        options.forEach(opt => {
            const text = opt.textContent.toLowerCase();
            opt.style.display = text.includes(searchText) ? '' : 'none';
        });
    });

    // Основная логика фильтрации
    const filterForms = document.querySelectorAll('.filters');
    
    filterForms.forEach(form => {
        const targetId = form.dataset.target;
        const url = form.dataset.url;
        
        if (!targetId || !url) return;
        
        const target = document.getElementById(targetId);
        const nativeSelects = form.querySelectorAll('select');
        const customSelects = form.querySelectorAll('.custom-select-wrapper');
        const searchInput = form.querySelector('input[type="text"]:not(.custom-select-dropdown .search-input)');
        
        function getFilterValue(customSelect) {
            const selectedOption = customSelect.querySelector('.filter-option.selected');
            if (selectedOption && selectedOption.dataset.value !== undefined) {
                return selectedOption.dataset.value;
            }
            return customSelect.dataset.value || '';
        }
        
        function filterData() {
            const params = new URLSearchParams();
            
            nativeSelects.forEach(select => {
                if (select.name && select.value) {
                    params.append(select.name, select.value);
                }
            });
            
            customSelects.forEach(select => {
                const value = getFilterValue(select);
                if (value) {
                    const sectionInput = form.querySelector('input[name="section"]');
                    const section = sectionInput ? sectionInput.value : '';
                    
                    if (section === 'users') {
                        params.append('role', value);
                    } else if (section === 'appointments') {
                        params.append('status', value);
                    } else {
                        params.append('direction', value);
                    }
                }
            });
            
            if (searchInput && searchInput.value) {
                params.append('search', searchInput.value);
            }
            
            const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
            window.history.replaceState({}, '', newUrl);
            
            fetch(url + '?' + params.toString())
                .then(response => response.text())
                .then(data => {
                    target.innerHTML = data;
                });
        }
        
        nativeSelects.forEach(select => {
            select.addEventListener('change', filterData);
        });
        
        customSelects.forEach(select => {
            select.addEventListener('filterChange', filterData);
            
            const observer = new MutationObserver(() => {
                filterData();
            });
            observer.observe(select, { attributes: true, attributeFilter: ['data-value'] });
        });
        
        if (searchInput) {
            searchInput.addEventListener('input', filterData);
        }
        
        filterData();
    });
    // Делаем функцию глобальной
    window.initFilters = function() {
        const filterForms = document.querySelectorAll('.filters');
        
        filterForms.forEach(form => {
            const targetId = form.dataset.target;
            const url = form.dataset.url;
            
            if (!targetId || !url) return;
            
            const target = document.getElementById(targetId);
            const nativeSelects = form.querySelectorAll('select');
            const customSelects = form.querySelectorAll('.custom-select-wrapper');
            const searchInput = form.querySelector('input[type="text"]:not(.custom-select-dropdown .search-input)');
            
            function getFilterValue(customSelect) {
                const selectedOption = customSelect.querySelector('.filter-option.selected');
                if (selectedOption && selectedOption.dataset.value !== undefined) {
                    return selectedOption.dataset.value;
                }
                return customSelect.dataset.value || '';
            }
            
            function filterData() {
                const params = new URLSearchParams();
                
                nativeSelects.forEach(select => {
                    if (select.name && select.value) {
                        params.append(select.name, select.value);
                    }
                });
                
                customSelects.forEach(select => {
                    const value = getFilterValue(select);
                    if (value) {
                        const sectionInput = form.querySelector('input[name="section"]');
                        const section = sectionInput ? sectionInput.value : '';
                        
                        if (section === 'users') {
                            params.append('role', value);
                        } else if (section === 'appointments') {
                            params.append('status', value);
                        } else {
                            params.append('direction', value);
                        }
                    }
                });
                
                if (searchInput && searchInput.value) {
                    params.append('search', searchInput.value);
                }
                
                const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
                window.history.replaceState({}, '', newUrl);
                
                fetch(url + '?' + params.toString())
                    .then(response => response.text())
                    .then(data => {
                        target.innerHTML = data;
                    });
            }
            
            nativeSelects.forEach(select => {
                select.addEventListener('change', filterData);
            });
            
            customSelects.forEach(select => {
                select.addEventListener('filterChange', filterData);
                
                const observer = new MutationObserver(() => {
                    filterData();
                });
                observer.observe(select, { attributes: true, attributeFilter: ['data-value'] });
            });
            
            if (searchInput) {
                searchInput.addEventListener('input', filterData);
            }
            
            filterData();
        });
    };

    // Вызываем при загрузке
    window.initFilters();

    // Инициализация кастомных селектов
    function initCustomSelects() {
        document.querySelectorAll('.custom-select-wrapper').forEach(wrapper => {
            if (wrapper.dataset.initialized) return;
            
            const trigger = wrapper.querySelector('.custom-select-trigger');
            const dropdown = wrapper.querySelector('.custom-select-dropdown');
            const optionsContainer = dropdown?.querySelector('.options-container');
            
            if (!trigger || !optionsContainer) return;
            
            // Открытие/закрытие
            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                document.querySelectorAll('.custom-select-wrapper').forEach(w => {
                    if (w !== wrapper) w.classList.remove('open');
                });
                wrapper.classList.toggle('open');
            });
            
            // Выбор опции
            optionsContainer.addEventListener('click', function(ev) {
                const option = ev.target.closest('.filter-option, .serviceOption');
                if (!option) return;
                
                const triggerSpan = trigger.querySelector('span');
                triggerSpan.textContent = option.textContent;
                
                optionsContainer.querySelectorAll('.filter-option, .serviceOption').forEach(opt => {
                    opt.classList.remove('selected');
                });
                option.classList.add('selected');
                
                wrapper.dataset.value = option.dataset.value;
                wrapper.classList.remove('open');
                
                // Запускаем фильтрацию
                const form = wrapper.closest('form');
                if (form) {
                    const event = new Event('change', { bubbles: true });
                    form.dispatchEvent(event);
                }
            });
            
            wrapper.dataset.initialized = 'true';
        });
    }

    // Запускаем при загрузке
    initCustomSelects();

    // Делаем глобальной для вызова из других скриптов
    window.initCustomSelects = initCustomSelects;
});