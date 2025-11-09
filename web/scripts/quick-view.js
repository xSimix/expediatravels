document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('quick-view-modal');
    if (!modal) {
        return;
    }

    const dialog = modal.querySelector('.quick-view-modal__dialog');
    const imageEl = modal.querySelector('[data-quick-view-image]');
    const destinationEl = modal.querySelector('[data-quick-view-destination]');
    const titleEl = modal.querySelector('[data-quick-view-title]');
    const summaryEl = modal.querySelector('[data-quick-view-summary]');
    const durationEl = modal.querySelector('[data-quick-view-duration]');
    const experienceEl = modal.querySelector('[data-quick-view-experience]');
    const groupEl = modal.querySelector('[data-quick-view-group]');
    const departureEl = modal.querySelector('[data-quick-view-departure]');
    const reviewsEl = modal.querySelector('[data-quick-view-reviews]');
    const priceEl = modal.querySelector('[data-quick-view-price]');
    const priceNoteEl = modal.querySelector('[data-quick-view-price-note]');
    const linkEl = modal.querySelector('[data-quick-view-link]');
    const closeTriggers = modal.querySelectorAll('[data-quick-view-close]');

    if (dialog) {
        dialog.setAttribute('tabindex', '-1');
    }

    let lastFocusedElement = null;

    const formatText = (value) => {
        if (value === null || value === undefined) {
            return '';
        }

        return String(value).trim();
    };

    const setFieldText = (element, value, fallback = 'Pronto') => {
        if (!element) {
            return;
        }

        const text = formatText(value);
        if (text !== '') {
            element.textContent = text;
            element.hidden = false;
        } else {
            element.textContent = fallback;
            element.hidden = fallback === '';
        }
    };

    const setImage = (source) => {
        if (!imageEl) {
            return;
        }

        const text = formatText(source);
        if (text !== '') {
            imageEl.style.backgroundImage = `url('${text}')`;
            imageEl.hidden = false;
        } else {
            imageEl.style.backgroundImage = '';
            imageEl.hidden = true;
        }
    };

    const setDestination = (value) => {
        if (!destinationEl) {
            return;
        }

        const text = formatText(value);
        destinationEl.textContent = text;
        destinationEl.hidden = text === '';
    };

    const setLink = (href) => {
        if (!linkEl) {
            return;
        }

        const url = formatText(href) || '#';
        linkEl.setAttribute('href', url);
    };

    const fillModal = (dataset) => {
        setImage(dataset.image || '');
        setDestination(dataset.destination || '');
        setFieldText(titleEl, dataset.title || '', 'Circuito seleccionado');
        setFieldText(summaryEl, dataset.summary || '', 'Estamos preparando el resumen de este circuito.');
        setFieldText(durationEl, dataset.duration || '', 'Pronto');
        setFieldText(experienceEl, dataset.experience || '', 'Pronto');
        setFieldText(groupEl, dataset.group || '', 'Pronto');
        setFieldText(departureEl, dataset.departure || '', 'Pronto');
        setFieldText(reviewsEl, dataset.reviews || dataset.rating || '', 'Pronto');
        setFieldText(priceEl, dataset.price || '', 'Pronto');
        setFieldText(priceNoteEl, dataset.priceNote || '', '');
        setLink(dataset.link || '');
    };

    const openModal = (trigger) => {
        if (!modal || !dialog) {
            return;
        }

        lastFocusedElement = trigger instanceof HTMLElement ? trigger : document.activeElement;
        fillModal(trigger.dataset);

        modal.hidden = false;
        modal.setAttribute('data-open', 'true');
        document.body.classList.add('has-modal-open');

        window.requestAnimationFrame(() => {
            dialog.focus({ preventScroll: true });
        });
    };

    const closeModal = () => {
        if (!modal) {
            return;
        }

        modal.hidden = true;
        modal.removeAttribute('data-open');
        document.body.classList.remove('has-modal-open');

        if (lastFocusedElement && typeof lastFocusedElement.focus === 'function') {
            lastFocusedElement.focus();
        }
    };

    closeTriggers.forEach((element) => {
        element.addEventListener('click', () => closeModal());
    });

    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.getAttribute('data-open') === 'true') {
            event.preventDefault();
            closeModal();
        }
    });

    const trapFocus = (event) => {
        if (event.key !== 'Tab' || !dialog || modal.getAttribute('data-open') !== 'true') {
            return;
        }

        const focusableSelectors = [
            'a[href]:not([tabindex="-1"]):not([aria-disabled="true"])',
            'button:not([disabled])',
            'textarea:not([disabled])',
            'input:not([disabled])',
            'select:not([disabled])',
            '[tabindex]:not([tabindex="-1"])',
        ];

        const focusable = Array.from(
            dialog.querySelectorAll(focusableSelectors.join(','))
        ).filter((element) => element.offsetParent !== null || element === dialog);

        if (!focusable.length) {
            event.preventDefault();
            dialog.focus({ preventScroll: true });
            return;
        }

        const first = focusable[0];
        const last = focusable[focusable.length - 1];

        if (event.shiftKey) {
            if (document.activeElement === first || document.activeElement === dialog) {
                event.preventDefault();
                last.focus({ preventScroll: true });
            }
        } else if (document.activeElement === last) {
            event.preventDefault();
            first.focus({ preventScroll: true });
        }
    };

    modal.addEventListener('keydown', trapFocus);

    document.querySelectorAll('[data-quick-view]').forEach((button) => {
        button.addEventListener('click', () => openModal(button));
    });
});
