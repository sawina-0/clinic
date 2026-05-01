function init_accordion(el) {
  el.addEventListener('click', (evt) => toggle_accordion(evt.target, el));
}

function toggle_accordion(target, parent) {
  if (!target.classList.contains('accordion')) return;

  if (target.classList.contains('active')) { 
    target.classList.remove('active');
    target.nextElementSibling.style.maxHeight = null;
    return 
  }

  const active = parent.querySelector('.active');
  if (active) {
    active.classList.remove('active');
    active.nextElementSibling.style.maxHeight = null;
  }

  target.classList.add('active');
  target.nextElementSibling.style.maxHeight = target.nextElementSibling.scrollHeight + "px";
}

init_accordion(document.querySelector('#accServices'));