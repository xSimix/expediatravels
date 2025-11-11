(function () {
    'use strict';

    const selectAll = (root, selector) => Array.prototype.slice.call(root.querySelectorAll(selector));

    document.addEventListener('DOMContentLoaded', () => {
        const form = document.querySelector('.admin-form');
        if (!form) {
            return;
        }

        initItinerary(form);
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
})();
