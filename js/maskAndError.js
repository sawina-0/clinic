// Маска для телефона
function phoneMask(selector) {
    const input = document.querySelector(selector);
    if (!input) return;
    
    input.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        
        if (value.length === 0) {
            e.target.value = '';
            return;
        }
        
        // Если первая цифра не 7 или 8, добавляем 8
        if (value[0] !== '7' && value[0] !== '8') {
            value = '8' + value;
        }
        
        // Форматируем: 8(888)888-88-88
        let formatted = '8';
        if (value.length > 1) formatted += '(' + value.substring(1, 4);
        if (value.length >= 5) formatted += ')' + value.substring(4, 7);
        if (value.length >= 8) formatted += '-' + value.substring(7, 9);
        if (value.length >= 10) formatted += '-' + value.substring(9, 11);
        
        e.target.value = formatted;
    });
}

// AJAX отправка формы
async function submitForm(form, url) {
    const formData = new FormData(form);
    
    // Очищаем телефон от форматирования
    const phoneInput = form.querySelector('input[type="tel"]');
    if (phoneInput) {
        const rawPhone = phoneInput.value.replace(/\D/g, '');
        formData.set('phone', rawPhone);
    }
    
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {'X-Requested-With': 'XMLHttpRequest'},
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            // customAlert(result.message);
            if (result.redirect) window.location.href = result.redirect;
        } else {
            customAlert(result.message);
        }
    } catch (error) {
        customAlert('Ошибка соединения');
    }
}

// Запуск при загрузке
document.addEventListener('DOMContentLoaded', function() {
    phoneMask('input[type="tel"]');
    
    const form = document.querySelector('form.block');
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            await submitForm(this, window.location.href);
        });
    }
});