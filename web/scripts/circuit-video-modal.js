(function () {
    const modal = document.querySelector('[data-video-modal]');
    const openButtons = document.querySelectorAll('[data-video-modal-open]');

    if (!modal || openButtons.length === 0) {
        return;
    }

    const dialog = modal.querySelector('[data-video-modal-dialog]');
    const frame = modal.querySelector('[data-video-modal-frame]');
    const closeElements = modal.querySelectorAll('[data-video-modal-close]');
    const body = document.body;
    let lastActiveElement = null;

    const getVideoSrc = (trigger) => {
        if (trigger && trigger.dataset.videoSrc) {
            return trigger.dataset.videoSrc;
        }
        return modal.dataset.videoSrc || '';
    };

    const openModal = (src) => {
        if (!src || !frame) {
            return;
        }

        lastActiveElement = document.activeElement;
        frame.src = src;
        modal.hidden = false;
        body.classList.add('video-modal-open');

        window.requestAnimationFrame(() => {
            if (dialog) {
                dialog.focus();
            }
        });
    };

    const closeModal = () => {
        if (frame) {
            frame.src = '';
        }
        modal.hidden = true;
        body.classList.remove('video-modal-open');

        if (lastActiveElement && typeof lastActiveElement.focus === 'function') {
            lastActiveElement.focus();
        }
    };

    openButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const src = getVideoSrc(button);
            openModal(src);
        });
    });

    closeElements.forEach((element) => {
        element.addEventListener('click', closeModal);
    });

    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });

    modal.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' || event.key === 'Esc') {
            event.preventDefault();
            closeModal();
        }
    });
})();
