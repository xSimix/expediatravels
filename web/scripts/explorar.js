document.addEventListener('DOMContentLoaded', () => {
    const filtersPanel = document.querySelector('[data-filters-panel]');
    const toggleButton = document.querySelector('[data-filters-toggle]');
    const closeButton = document.querySelector('[data-filters-close]');
    const sortSelect = document.querySelector('[data-sort-select]');

    const openFilters = () => {
        if (!filtersPanel || !toggleButton) {
            return;
        }

        filtersPanel.classList.add('explore__filters--open');
        toggleButton.setAttribute('aria-expanded', 'true');
        filtersPanel.focus();
    };

    const closeFilters = () => {
        if (!filtersPanel || !toggleButton) {
            return;
        }

        filtersPanel.classList.remove('explore__filters--open');
        toggleButton.setAttribute('aria-expanded', 'false');
        toggleButton.focus();
    };

    if (filtersPanel) {
        filtersPanel.setAttribute('tabindex', '-1');
    }

    if (toggleButton && filtersPanel) {
        toggleButton.addEventListener('click', () => {
            const isOpen = filtersPanel.classList.contains('explore__filters--open');
            if (isOpen) {
                closeFilters();
            } else {
                openFilters();
            }
        });
    }

    if (closeButton) {
        closeButton.addEventListener('click', () => {
            closeFilters();
        });
    }

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && filtersPanel?.classList.contains('explore__filters--open')) {
            closeFilters();
        }
    });

    if (sortSelect) {
        sortSelect.addEventListener('change', () => {
            const form = sortSelect.closest('form');
            if (form) {
                form.requestSubmit();
            }
        });
    }
});
