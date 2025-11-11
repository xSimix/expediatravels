(function () {
    'use strict';

    const selectAll = (root, selector) => Array.prototype.slice.call(root.querySelectorAll(selector));

    document.addEventListener('DOMContentLoaded', () => {
        const form = document.querySelector('.admin-form');
        if (!form) {
            return;
        }

        initItinerary(form);
        initServiceSelectors(form);
    });

    function initItinerary(root) {
        const list = root.querySelector('[data-itinerary-list]');
        const template = document.getElementById('itinerary-item-template');
        const addButton = root.querySelector('[data-itinerary-add]');
        if (!list || !template || !addButton) {
            return;
        }

        const updateIndices = () => {
            selectAll(list, '[data-itinerary-item]').forEach((item, index) => {
                const badge = item.querySelector('[data-itinerary-index]');
                if (badge) {
                    badge.textContent = String(index + 1);
                }
            });
        };

        const attachEvents = (item) => {
            const removeButton = item.querySelector('[data-itinerary-remove]');
            if (removeButton) {
                removeButton.addEventListener('click', () => {
                    item.remove();
                    updateIndices();
                });
            }
        };

        const addItem = (values = {}) => {
            const fragment = template.content.cloneNode(true);
            const item = fragment.querySelector('[data-itinerary-item]');
            if (!item) {
                return;
            }

            const dia = item.querySelector('input[name="itinerario[dia][]"]');
            if (dia) {
                dia.value = values.dia || '';
            }
            const hora = item.querySelector('input[name="itinerario[hora][]"]');
            if (hora) {
                hora.value = values.hora || '';
            }
            const titulo = item.querySelector('input[name="itinerario[titulo][]"]');
            if (titulo) {
                titulo.value = values.titulo || '';
            }
            const descripcion = item.querySelector('textarea[name="itinerario[descripcion][]"]');
            if (descripcion) {
                descripcion.value = values.descripcion || '';
            }
            const ubicacion = item.querySelector('input[name="itinerario[ubicacion_maps][]"]');
            if (ubicacion) {
                ubicacion.value = values.ubicacion_maps || '';
            }

            attachEvents(item);
            list.appendChild(fragment);
            updateIndices();
        };

        selectAll(list, '[data-itinerary-item]').forEach(attachEvents);
        updateIndices();

        addButton.addEventListener('click', () => {
            addItem();
        });
    }

    function initServiceSelectors(root) {
        const selectors = selectAll(root, '[data-service-selector]');
        if (!selectors.length) {
            return;
        }

        selectors.forEach((selector) => {
            const list = selector.querySelector('[data-service-list]');
            const chipsContainer = selector.querySelector('[data-service-chips]');
            const searchInput = selector.querySelector('[data-service-search]');
            const emptyMessage = selector.querySelector('[data-service-empty]');

            if (!list || !chipsContainer) {
                return;
            }

            const checkboxes = selectAll(list, 'input[type="checkbox"][data-service-checkbox]');

            const toggleChipsEmptyState = () => {
                if (!chipsContainer) {
                    return;
                }
                const isEmpty = chipsContainer.querySelectorAll('[data-service-chip]').length === 0;
                chipsContainer.setAttribute('data-empty', isEmpty ? 'true' : 'false');
            };

            const ensureChip = (checkbox) => {
                const id = checkbox.value;
                let chip = chipsContainer.querySelector(`[data-service-chip="${id}"]`);

                if (checkbox.checked) {
                    if (!chip) {
                        chip = document.createElement('span');
                        chip.className = 'service-chip';
                        chip.setAttribute('data-service-chip', id);

                        const label = document.createElement('span');
                        label.className = 'service-chip__label';
                        label.textContent = checkbox.getAttribute('data-service-name') || checkbox.value;

                        const removeButton = document.createElement('button');
                        removeButton.type = 'button';
                        removeButton.className = 'service-chip__remove';
                        removeButton.setAttribute('aria-label', `Quitar ${label.textContent}`.trim());
                        removeButton.setAttribute('data-service-chip-remove', id);
                        removeButton.textContent = '×';

                        chip.appendChild(label);
                        chip.appendChild(removeButton);
                        chipsContainer.appendChild(chip);
                    }
                } else if (chip) {
                    chip.remove();
                }

                toggleChipsEmptyState();
            };

            checkboxes.forEach((checkbox) => {
                ensureChip(checkbox);
                checkbox.addEventListener('change', () => {
                    ensureChip(checkbox);
                });
            });

            toggleChipsEmptyState();

            chipsContainer.addEventListener('click', (event) => {
                const button = event.target.closest('[data-service-chip-remove]');
                if (!button) {
                    return;
                }

                const id = button.getAttribute('data-service-chip-remove');
                const target = checkboxes.find((input) => input.value === id);
                if (target) {
                    target.checked = false;
                    target.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });

            if (searchInput) {
                const updateVisibility = () => {
                    const query = searchInput.value.trim().toLowerCase();
                    let visibleItems = 0;

                    checkboxes.forEach((checkbox) => {
                        const item = checkbox.closest('[data-service-item]');
                        if (!item) {
                            return;
                        }
                        const labelText = (item.getAttribute('data-service-label') || '').toLowerCase();
                        const matches = query === '' || labelText.includes(query);
                        item.hidden = !matches;
                        if (matches) {
                            visibleItems += 1;
                        }
                    });

                    if (emptyMessage) {
                        emptyMessage.hidden = visibleItems > 0;
                    }
                };

                searchInput.addEventListener('input', updateVisibility);
                updateVisibility();
            }
        });
    }
})();
