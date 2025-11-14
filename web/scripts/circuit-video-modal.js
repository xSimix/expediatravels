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

    const setFrameSource = (src) => {
        if (!frame) {
            return;
        }

        if (frame instanceof HTMLIFrameElement) {
            frame.src = src;
            return;
        }

        if (frame instanceof HTMLVideoElement) {
            frame.src = src;
            frame.load();
            if (typeof frame.play === 'function') {
                const playPromise = frame.play();
                if (playPromise && typeof playPromise.catch === 'function') {
                    playPromise.catch(() => {});
                }
            }
            return;
        }

        frame.setAttribute('src', src);
    };

    const clearFrameSource = () => {
        if (!frame) {
            return;
        }

        if (frame instanceof HTMLIFrameElement) {
            frame.src = '';
            return;
        }

        if (frame instanceof HTMLVideoElement) {
            if (typeof frame.pause === 'function') {
                frame.pause();
            }
            frame.removeAttribute('src');
            if (typeof frame.load === 'function') {
                frame.load();
            }
            return;
        }

        frame.removeAttribute('src');
    };

    const openModal = (src) => {
        if (!src || !frame) {
            return;
        }

        lastActiveElement = document.activeElement instanceof HTMLElement ? document.activeElement : null;
        setFrameSource(src);
        modal.hidden = false;
        body.classList.add('video-modal-open');

        window.requestAnimationFrame(() => {
            modal.classList.add('is-open');
            if (dialog) {
                dialog.focus();
            }
        });
    };

    const closeModal = () => {
        clearFrameSource();
        modal.classList.remove('is-open');
        body.classList.remove('video-modal-open');

        const finalizeClose = () => {
            modal.hidden = true;
        };

        if (typeof modal.addEventListener === 'function') {
            const handleTransitionEnd = (event) => {
                if (event.target !== modal) {
                    return;
                }
                modal.removeEventListener('transitionend', handleTransitionEnd);
                finalizeClose();
            };
            modal.addEventListener('transitionend', handleTransitionEnd);
            window.setTimeout(() => {
                modal.removeEventListener('transitionend', handleTransitionEnd);
                finalizeClose();
            }, 350);
        } else {
            finalizeClose();
        }

        if (lastActiveElement && typeof lastActiveElement.focus === 'function') {
            lastActiveElement.focus();
        }
        lastActiveElement = null;
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
