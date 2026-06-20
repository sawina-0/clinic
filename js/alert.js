function customAlert(message) {
    const alertDiv = document.getElementById('customAlert');
    const msgSpan = document.getElementById('customAlertMessage');
    msgSpan.innerText = message;
    alertDiv.style.display = 'flex';

    const okBtn = document.getElementById('customAlertOk');
    const closeHandler = function() {
        alertDiv.style.display = 'none';
        okBtn.removeEventListener('click', closeHandler);
    };
    okBtn.addEventListener('click', closeHandler);
}
function customConfirm(message, onConfirm) {
    const confirmDiv = document.createElement('div');
    confirmDiv.className = 'custom-confirm';
    confirmDiv.innerHTML = `
        <div class="custom-confirm-content">
            <p>${message}</p>
            <div class="confirm-buttons">
                <button id="confirmYes">Да</button>
                <button id="confirmNo">Нет</button>
            </div>
        </div>
    `;
    document.body.appendChild(confirmDiv);
    confirmDiv.style.display = 'flex';

    document.getElementById('confirmYes').onclick = () => {
        confirmDiv.remove();
        if (onConfirm) onConfirm();
    };
    document.getElementById('confirmNo').onclick = () => {
        confirmDiv.remove();
    };
}