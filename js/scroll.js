const box = document.getElementById('box');
const swapRight = document.getElementById('swapRight');
const swapLeft = document.getElementById('swapLeft');

let isDown = false;
let startX;
let scrollLeft;
let x;
let walkX;

// Для переключения disabled у кнопок
const contents = box.querySelectorAll('div');
const content_right = contents[box.querySelectorAll('div').length - 1];
const content_left = contents[0];

function update_overflow() {
    const container_rect = box.getBoundingClientRect();
    const content_rect_left = content_left.getBoundingClientRect();
    const content_rect_right = content_right.getBoundingClientRect();

    swapLeft.disabled = !(content_rect_left.left < container_rect.left);
    swapRight.disabled = !(content_rect_right.right > container_rect.right);
}

function idDown_true(evt) {
    isDown = true;
    startX = evt.pageX - box.offsetLeft;
    scrollLeft = box.scrollLeft;
    box.style.cursor = 'grabbing';
}

function isDown_false() {
    isDown = false;
    box.style.cursor = 'grab';
}

function mouse_scroll(evt) {
    if (!isDown) return;
    evt.preventDefault();
    x = evt.pageX - box.offsetLeft;
    walkX = (x - startX) * 1.8;
    box.scrollLeft = scrollLeft - walkX;
    update_overflow();
}

function swap(type) {
    box.style.scrollBehavior = 'smooth';
    box.scrollLeft += (type * 200);
    setTimeout(() => {
        box.style.scrollBehavior = 'auto';
        update_overflow();
    }, 200);
}

document.getElementById('specialists').addEventListener('mousemove', (evt) => mouse_scroll(evt));

box.addEventListener('mousedown', (evt) => idDown_true(evt));
box.addEventListener('mouseup', () => isDown_false());
box.addEventListener('mouseleave', () => isDown_false());

swapRight.addEventListener('click', () => swap(1));
swapLeft.addEventListener('click', () => swap(-1));

update_overflow()