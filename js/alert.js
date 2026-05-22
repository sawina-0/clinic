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