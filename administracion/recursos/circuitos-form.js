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

        const configs = selectors
            .map((selector) => setupServiceSelector(selector))
            .filter((config) => config !== null);

        linkServiceSelectors(configs);
    }

    function setupServiceSelector(selector) {
        const list = selector.querySelector('[data-service-list]');
        const chipsContainer = selector.querySelector('[data-service-chips]');
        const searchInput = selector.querySelector('[data-service-search]');
        const emptyMessage = selector.querySelector('[data-service-empty]');
        const panel = selector.querySelector('[data-service-panel]');

        if (!list || !chipsContainer) {
            return null;
        }

        const checkboxes = selectAll(list, 'input[type="checkbox"][data-service-checkbox]');
        const entries = checkboxes
            .map((checkbox) => ({
                id: checkbox.value,
                checkbox,
                item: checkbox.closest('[data-service-item]') || null,
            }))
            .filter((entry) => entry.item);

        const toggleChipsEmptyState = () => {
            const isEmpty = chipsContainer.querySelectorAll('[data-service-chip]').length === 0;
            chipsContainer.setAttribute('data-empty', isEmpty ? 'true' : 'false');
        };

        const updateVisibility = () => {
            const query = searchInput ? searchInput.value.trim().toLowerCase() : '';
            let visibleItems = 0;

            entries.forEach((entry) => {
                if (!entry.item) {
                    return;
                }
                const labelText = (entry.item.getAttribute('data-service-label') || '').toLowerCase();
                const matches = query === '' || labelText.includes(query);
                const hiddenBySelection = entry.item.getAttribute('data-service-hidden') === 'true';
                const shouldShow = matches && !hiddenBySelection;
                entry.item.hidden = !shouldShow;
                if (shouldShow) {
                    visibleItems += 1;
                }
            });

            if (emptyMessage) {
                emptyMessage.hidden = visibleItems > 0;
            }
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
                updateVisibility();
            });
        });

        toggleChipsEmptyState();
        updateVisibility();

        chipsContainer.addEventListener('click', (event) => {
            const button = event.target.closest('[data-service-chip-remove]');
            if (!button) {
                return;
            }

            const id = button.getAttribute('data-service-chip-remove');
            const target = entries.find((entry) => entry.id === id);
            if (target) {
                target.checkbox.checked = false;
                target.checkbox.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });

        if (searchInput) {
            searchInput.addEventListener('input', updateVisibility);
        }

        if (panel) {
            const setPanelOpen = (open) => {
                panel.setAttribute('data-open', open ? 'true' : 'false');
            };

            const isPanelOpen = () => panel.getAttribute('data-open') === 'true';

            const openPanel = () => {
                if (isPanelOpen()) {
                    return;
                }
                setPanelOpen(true);
                if (searchInput) {
                    searchInput.value = '';
                }
                updateVisibility();
            };

            const closePanel = () => {
                if (!isPanelOpen()) {
                    return;
                }
                setPanelOpen(false);
            };

            setPanelOpen(false);

            if (searchInput) {
                searchInput.addEventListener('focus', openPanel);
                searchInput.addEventListener('click', openPanel);
                searchInput.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        closePanel();
                        searchInput.blur();
                    }
                });
                searchInput.addEventListener('blur', () => {
                    window.setTimeout(() => {
                        if (!selector.contains(document.activeElement)) {
                            closePanel();
                        }
                    }, 120);
                });
            }

            document.addEventListener('click', (event) => {
                if (!selector.contains(event.target)) {
                    closePanel();
                }
            });
        }

        return {
            selector,
            type: selector.getAttribute('data-service-selector-type') || 'default',
            entries,
            updateVisibility,
        };
    }

    function linkServiceSelectors(configs) {
        if (configs.length < 2) {
            return;
        }

        const serviceMap = new Map();

        configs.forEach((config) => {
            const type = config.type;
            config.entries.forEach((entry) => {
                if (!serviceMap.has(entry.id)) {
                    serviceMap.set(entry.id, {});
                }
                const existing = serviceMap.get(entry.id);
                existing[type] = { config, entry };
            });
        });

        const syncService = (id) => {
            const pair = serviceMap.get(id);
            if (!pair) {
                return;
            }

            const includedData = pair.included;
            const excludedData = pair.excluded;

            if (!includedData || !excludedData) {
                return;
            }

            let includedSelected = includedData.entry.checkbox.checked;
            let excludedSelected = excludedData.entry.checkbox.checked;

            if (includedSelected) {
                if (excludedSelected) {
                    excludedData.entry.checkbox.checked = false;
                    excludedData.entry.checkbox.dispatchEvent(new Event('change', { bubbles: true }));
                    excludedSelected = false;
                }
                excludedData.entry.item.setAttribute('data-service-hidden', 'true');
            } else {
                excludedData.entry.item.removeAttribute('data-service-hidden');
            }

            if (excludedSelected) {
                if (includedSelected) {
                    includedData.entry.checkbox.checked = false;
                    includedData.entry.checkbox.dispatchEvent(new Event('change', { bubbles: true }));
                    includedSelected = false;
                }
                includedData.entry.item.setAttribute('data-service-hidden', 'true');
            } else {
                includedData.entry.item.removeAttribute('data-service-hidden');
            }

            includedData.config.updateVisibility();
            excludedData.config.updateVisibility();
        };

        configs.forEach((config) => {
            config.entries.forEach((entry) => {
                entry.checkbox.addEventListener('change', () => {
                    syncService(entry.id);
                });
            });
        });

        serviceMap.forEach((_, id) => {
            syncService(id);
        });
    }
})();
