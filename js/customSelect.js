document.addEventListener('DOMContentLoaded', function() {
    document.body.addEventListener('click', function(e) {
        const btn = e.target.closest('.commonBtn[data-doctor-id], .commonBtn[data-service-id]');
        if (!btn) return;

        setTimeout(function() {
            const trigger = document.querySelector('.custom-select-trigger');
            const wrapper = document.querySelector('.custom-select-wrapper');
            if (!trigger || !wrapper) return;

            const newTrigger = trigger.cloneNode(true);
            trigger.parentNode.replaceChild(newTrigger, trigger);

            const freshTrigger = document.querySelector('.custom-select-trigger');
            const freshWrapper = document.querySelector('.custom-select-wrapper');

            freshTrigger.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                freshWrapper.classList.toggle('open');
            });

            document.querySelector('.options-container').addEventListener('click', function(ev) {
                const option = ev.target.closest('.serviceOption');
                if (!option) return;

                const triggerSpan = freshTrigger.querySelector('span');
                const optionText = option.querySelector('p').textContent;
                const optionSpan = option.querySelector('span')?.textContent || '';
                triggerSpan.textContent = optionText + (optionSpan ? ' — ' + optionSpan : '');

                document.querySelectorAll('.serviceOption').forEach(opt => opt.classList.remove('selected'));
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

            document.querySelector('.search-input')?.addEventListener('input', function(ev) {
                const searchText = ev.target.value.toLowerCase();
                document.querySelectorAll('.serviceOption').forEach(opt => {
                    opt.style.display = opt.textContent.toLowerCase().includes(searchText) ? '' : 'none';
                });
            });

        }, 500);
    });
});