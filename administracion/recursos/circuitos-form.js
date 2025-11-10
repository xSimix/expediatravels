(function () {
    'use strict';

    const selectAll = (root, selector) => Array.prototype.slice.call(root.querySelectorAll(selector));

    document.addEventListener('DOMContentLoaded', () => {
        const form = document.querySelector('.admin-form');
        if (!form) {
            return;
        }

        initItinerary(form);
        initMapManager(form);
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

    function initMapManager(root) {
        const manager = root.querySelector('[data-map-manager]');
        const template = document.getElementById('map-marker-template');
        if (!manager || !template) {
            return;
        }

        const canvas = manager.querySelector('[data-map-canvas]');
        const list = manager.querySelector('[data-map-list]');
        const addButton = manager.querySelector('[data-map-add]');
        const emptyState = manager.querySelector('[data-map-empty]');
        const apiKey = manager.getAttribute('data-api-key') || '';

        const markerEntries = [];
        let map = null;
        let infoWindow = null;

        const updateEmptyState = () => {
            if (!emptyState) {
                return;
            }
            const hasItems = list && list.querySelector('[data-map-item]');
            emptyState.hidden = !!hasItems;
        };

        const updateIndices = () => {
            selectAll(list, '[data-map-item]').forEach((item, index) => {
                const badge = item.querySelector('[data-map-index]');
                if (badge) {
                    badge.textContent = String(index + 1);
                }
            });
            markerEntries.forEach((entry, index) => {
                if (entry.marker) {
                    entry.marker.setLabel(String(index + 1));
                }
            });
        };

        const parseLatLng = (item) => {
            const latInput = item.querySelector('input[name="marcadores[latitud][]"]');
            const lngInput = item.querySelector('input[name="marcadores[longitud][]"]');
            const lat = latInput ? parseFloat(latInput.value) : NaN;
            const lng = lngInput ? parseFloat(lngInput.value) : NaN;
            if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
                return null;
            }
            return { lat, lng };
        };

        const focusOnItem = (item, position) => {
            if (!map) {
                return;
            }
            const entry = markerEntries.find((candidate) => candidate.item === item);
            if (!entry) {
                return;
            }
            const target = position || (entry.marker ? entry.marker.getPosition() : null);
            if (!target) {
                return;
            }
            map.panTo(target);
            if (map.getZoom() < 11) {
                map.setZoom(11);
            }
            if (!entry.marker) {
                return;
            }
            if (!infoWindow) {
                infoWindow = new google.maps.InfoWindow();
            }
            const titleInput = item.querySelector('input[name="marcadores[titulo][]"]');
            const title = titleInput ? titleInput.value.trim() : '';
            infoWindow.setContent(title !== '' ? title : 'Punto del circuito');
            infoWindow.open({ anchor: entry.marker, map });
            item.scrollIntoView({ behavior: 'smooth', block: 'center' });
        };

        const updateMarkerPosition = (entry) => {
            if (!map) {
                return;
            }
            const position = parseLatLng(entry.item);
            if (!position) {
                if (entry.marker) {
                    entry.marker.setMap(null);
                    entry.marker = null;
                }
                return;
            }
            if (!entry.marker) {
                entry.marker = new google.maps.Marker({
                    map,
                    position,
                    label: String(markerEntries.indexOf(entry) + 1),
                });
            } else {
                entry.marker.setPosition(position);
            }
        };

        const attachMarkerEvents = (item, entry) => {
            const removeButton = item.querySelector('[data-map-remove]');
            if (removeButton) {
                removeButton.addEventListener('click', () => {
                    const index = markerEntries.indexOf(entry);
                    if (index >= 0) {
                        markerEntries.splice(index, 1);
                    }
                    if (entry.marker) {
                        entry.marker.setMap(null);
                    }
                    item.remove();
                    updateIndices();
                    updateEmptyState();
                });
            }

            const focusButton = item.querySelector('[data-map-focus]');
            if (focusButton) {
                focusButton.addEventListener('click', () => {
                    focusOnItem(item);
                });
            }

            const titleInput = item.querySelector('input[name="marcadores[titulo][]"]');
            const latInput = item.querySelector('input[name="marcadores[latitud][]"]');
            const lngInput = item.querySelector('input[name="marcadores[longitud][]"]');

            const onChange = () => {
                updateMarkerPosition(entry);
                updateIndices();
            };

            if (titleInput) {
                titleInput.addEventListener('input', () => {
                    if (entry.marker && infoWindow && infoWindow.getAnchor() === entry.marker) {
                        const title = titleInput.value.trim();
                        infoWindow.setContent(title !== '' ? title : 'Punto del circuito');
                    }
                });
            }
            if (latInput) {
                latInput.addEventListener('input', onChange);
            }
            if (lngInput) {
                lngInput.addEventListener('input', onChange);
            }
        };

        const addMarkerItem = (values = {}) => {
            const fragment = template.content.cloneNode(true);
            const item = fragment.querySelector('[data-map-item]');
            if (!item) {
                return null;
            }

            const titleInput = item.querySelector('input[name="marcadores[titulo][]"]');
            if (titleInput) {
                titleInput.value = values.titulo || '';
            }
            const descriptionInput = item.querySelector('textarea[name="marcadores[descripcion][]"]');
            if (descriptionInput) {
                descriptionInput.value = values.descripcion || '';
            }
            const latInput = item.querySelector('input[name="marcadores[latitud][]"]');
            const lngInput = item.querySelector('input[name="marcadores[longitud][]"]');
            if (latInput) {
                latInput.value = values.latitud || '';
            }
            if (lngInput) {
                lngInput.value = values.longitud || '';
            }

            const entry = { item, marker: null };
            markerEntries.push(entry);
            attachMarkerEvents(item, entry);
            list.appendChild(fragment);
            updateMarkerPosition(entry);
            updateIndices();
            updateEmptyState();

            return entry.item;
        };

        selectAll(list, '[data-map-item]').forEach((item) => {
            const entry = { item, marker: null };
            markerEntries.push(entry);
            attachMarkerEvents(item, entry);
        });
        updateMarkerPositionForAll();
        updateIndices();
        updateEmptyState();

        if (addButton) {
            addButton.addEventListener('click', () => {
                const position = map ? map.getCenter() : null;
                const values = {
                    titulo: '',
                    descripcion: '',
                    latitud: position ? position.lat().toFixed(6) : '',
                    longitud: position ? position.lng().toFixed(6) : '',
                };
                const item = addMarkerItem(values);
                if (item) {
                    focusOnItem(item);
                }
            });
        }

        if (canvas) {
            canvas.setAttribute('data-map-ready', 'false');
        }

        if (apiKey) {
            loadGoogleMaps(apiKey)
                .then((loaded) => {
                    if (loaded === false) {
                        return;
                    }
                    if (!(window.google && window.google.maps) || !canvas) {
                        return;
                    }
                    canvas.setAttribute('data-map-ready', 'true');
                    map = new google.maps.Map(canvas, {
                        center: { lat: -12.046374, lng: -77.042793 },
                        zoom: 7,
                        mapTypeControl: false,
                        streetViewControl: false,
                    });
                    updateMarkerPositionForAll();
                    if (markerEntries.length > 0) {
                        const bounds = new google.maps.LatLngBounds();
                        markerEntries.forEach((entry) => {
                            if (entry.marker) {
                                bounds.extend(entry.marker.getPosition());
                            }
                        });
                        if (!bounds.isEmpty()) {
                            map.fitBounds(bounds, 64);
                        }
                    }
                    map.addListener('click', (event) => {
                        const latLng = event.latLng;
                        const item = addMarkerItem({
                            titulo: 'Nuevo punto',
                            descripcion: '',
                            latitud: latLng.lat().toFixed(6),
                            longitud: latLng.lng().toFixed(6),
                        });
                        if (item) {
                            focusOnItem(item, latLng);
                        }
                    });
                })
                .catch(() => {
                    if (canvas) {
                        canvas.innerHTML = '<div class="admin-help">No se pudo cargar Google Maps. Verifica tu conexión o la clave API configurada.</div>';
                    }
                });
        } else if (canvas) {
            canvas.innerHTML = '<div class="admin-help">Agrega una clave de Google Maps en tu configuración para habilitar el mapa interactivo.</div>';
        }

        function updateMarkerPositionForAll() {
            markerEntries.forEach((entry) => {
                updateMarkerPosition(entry);
            });
        }
    }

    function loadGoogleMaps(apiKey) {
        if (typeof window === 'undefined') {
            return Promise.resolve(false);
        }
        if (window.google && window.google.maps) {
            return Promise.resolve(true);
        }
        if (!apiKey) {
            return Promise.resolve(false);
        }
        if (window.__circuitMapsLoading) {
            return window.__circuitMapsLoading;
        }

        window.__circuitMapsLoading = new Promise((resolve, reject) => {
            const script = document.createElement('script');
            const callbackName = '__circuitMapsInit';
            script.src = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(apiKey) + '&callback=' + callbackName;
            script.async = true;
            script.onerror = (error) => {
                delete window.__circuitMapsLoading;
                delete window[callbackName];
                reject(error);
            };
            window[callbackName] = () => {
                delete window.__circuitMapsLoading;
                delete window[callbackName];
                resolve(true);
            };
            document.head.appendChild(script);
        }).catch(() => false);

        return window.__circuitMapsLoading;
    }
})();
