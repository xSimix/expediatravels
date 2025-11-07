(function () {
  'use strict';

  const API_ENDPOINT = 'api/medios.php';

  const qs = (root, selector) => root.querySelector(selector);
  const qsa = (root, selector) => Array.from(root.querySelectorAll(selector));

  const createElement = (tag, attrs = {}, children = []) => {
    const el = document.createElement(tag);
    Object.entries(attrs).forEach(([key, value]) => {
      if (value === null || value === undefined) {
        return;
      }
      if (key === 'class') {
        el.className = String(value);
      } else if (key === 'dataset') {
        Object.entries(value).forEach(([dataKey, dataValue]) => {
          el.dataset[dataKey] = String(dataValue);
        });
      } else if (key === 'text') {
        el.textContent = String(value);
      } else {
        el.setAttribute(key, String(value));
      }
    });
    children.forEach((child) => {
      if (child instanceof Node) {
        el.appendChild(child);
      } else if (typeof child === 'string') {
        el.appendChild(document.createTextNode(child));
      }
    });
    return el;
  };

  const formatBytes = (bytes) => {
    if (!Number.isFinite(bytes) || bytes <= 0) {
      return '—';
    }
    const units = ['B', 'KB', 'MB', 'GB'];
    const index = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
    const value = bytes / (1024 ** index);
    return `${value.toFixed(index === 0 ? 0 : 1)} ${units[index]}`;
  };

  class MediaLibrary {
    constructor() {
      this.items = [];
      this.loaded = false;
      this.loading = false;
      this.subscribers = [];
    }

    async list() {
      if (this.loaded) {
        return this.items;
      }
      if (this.loading) {
        return new Promise((resolve) => {
          this.subscribers.push(resolve);
        });
      }
      this.loading = true;
      try {
        const response = await fetch(API_ENDPOINT, {
          headers: { 'Accept': 'application/json' },
        });
        if (!response.ok) {
          throw new Error('No se pudo cargar la biblioteca de medios.');
        }
        const payload = await response.json();
        if (!payload || payload.ok !== true || !Array.isArray(payload.items)) {
          throw new Error('Respuesta inválida del servidor.');
        }
        this.items = payload.items;
        this.loaded = true;
        this.loading = false;
        this.subscribers.splice(0).forEach((fn) => fn(this.items));
        return this.items;
      } catch (error) {
        this.loading = false;
        this.loaded = false;
        this.subscribers.splice(0).forEach((fn) => fn([]));
        throw error;
      }
    }

    add(item) {
      if (!item) {
        return;
      }
      const existingIndex = this.items.findIndex((entry) => entry.id === item.id);
      if (existingIndex >= 0) {
        this.items[existingIndex] = item;
      } else {
        this.items.unshift(item);
      }
      this.loaded = true;
    }
  }

  class MediaModal {
    constructor(library) {
      this.library = library;
      this.multiple = false;
      this.selected = new Set();
      this.onConfirm = null;
      this.items = [];
      this.filteredItems = [];
      this.active = false;
      this.build();
    }

    build() {
      this.overlay = createElement('div', { class: 'media-modal', dataset: { state: 'hidden' } });
      const dialog = createElement('div', { class: 'media-modal__dialog', role: 'dialog', 'aria-modal': 'true', 'aria-label': 'Biblioteca de imágenes' });
      const header = createElement('header', { class: 'media-modal__header' }, [
        createElement('h2', { text: 'Seleccionar imágenes' }),
        createElement('button', { type: 'button', class: 'media-modal__close', 'aria-label': 'Cerrar' }, ['×']),
      ]);
      const search = createElement('div', { class: 'media-modal__search' }, [
        createElement('input', { type: 'search', placeholder: 'Buscar por título…', 'aria-label': 'Buscar imagen', autocomplete: 'off' }),
      ]);
      const body = createElement('div', { class: 'media-modal__body' }, [
        createElement('div', { class: 'media-modal__grid' }),
        createElement('div', { class: 'media-modal__empty', text: 'No hay imágenes disponibles todavía.' }),
      ]);
      const footer = createElement('footer', { class: 'media-modal__footer' }, [
        createElement('span', { class: 'media-modal__status', text: '0 seleccionadas' }),
        createElement('div', { class: 'media-modal__actions' }, [
          createElement('button', { type: 'button', class: 'admin-button secondary media-modal__cancel' }, ['Cancelar']),
          createElement('button', { type: 'button', class: 'admin-button media-modal__confirm', disabled: 'disabled' }, ['Usar selección']),
        ]),
      ]);

      dialog.appendChild(header);
      dialog.appendChild(search);
      dialog.appendChild(body);
      dialog.appendChild(footer);
      this.overlay.appendChild(dialog);
      document.body.appendChild(this.overlay);

      this.closeButton = qs(header, '.media-modal__close');
      this.searchInput = qs(search, 'input');
      this.grid = qs(body, '.media-modal__grid');
      this.emptyState = qs(body, '.media-modal__empty');
      this.status = qs(footer, '.media-modal__status');
      this.confirmButton = qs(footer, '.media-modal__confirm');
      this.cancelButton = qs(footer, '.media-modal__cancel');

      this.overlay.addEventListener('click', (event) => {
        if (event.target === this.overlay) {
          this.close();
        }
      });
      this.closeButton.addEventListener('click', () => this.close());
      this.cancelButton.addEventListener('click', () => this.close());
      this.confirmButton.addEventListener('click', () => this.confirm());
      this.searchInput.addEventListener('input', () => this.applyFilter());

      document.addEventListener('keydown', (event) => {
        if (!this.active) {
          return;
        }
        if (event.key === 'Escape') {
          event.preventDefault();
          this.close();
        }
      });
    }

    async open({ multiple = false, preselected = [], onConfirm }) {
      this.multiple = multiple;
      this.onConfirm = typeof onConfirm === 'function' ? onConfirm : null;
      this.selected = new Set(preselected.filter((value) => typeof value === 'string' && value !== ''));
      this.active = true;
      this.overlay.dataset.state = 'visible';
      document.body.classList.add('media-modal-open');
      this.updateStatus();
      this.renderItems([]);
      try {
        const items = await this.library.list();
        this.items = Array.isArray(items) ? items : [];
        this.applyFilter();
      } catch (error) {
        this.items = [];
        this.renderError(error instanceof Error ? error.message : 'No se pudieron cargar las imágenes.');
      }
    }

    close() {
      this.active = false;
      this.overlay.dataset.state = 'hidden';
      document.body.classList.remove('media-modal-open');
      this.searchInput.value = '';
      this.onConfirm = null;
    }

    confirm() {
      if (!this.onConfirm) {
        this.close();
        return;
      }
      const values = Array.from(this.selected);
      if (!this.multiple && values.length > 1) {
        values.splice(1);
      }
      const selectedItems = values.map((value) => this.items.find((item) => item.url === value || `/${item.ruta}` === value)).filter(Boolean);
      this.onConfirm(values, selectedItems);
      this.close();
    }

    applyFilter() {
      const term = this.searchInput.value.trim().toLowerCase();
      if (!term) {
        this.filteredItems = this.items.slice();
      } else {
        this.filteredItems = this.items.filter((item) => String(item.titulo ?? '').toLowerCase().includes(term));
      }
      this.renderItems(this.filteredItems);
    }

    renderItems(items) {
      this.grid.innerHTML = '';
      if (!items || items.length === 0) {
        this.emptyState.style.display = 'flex';
        this.confirmButton.disabled = this.selected.size === 0;
        this.updateStatus();
        return;
      }
      this.emptyState.style.display = 'none';
      items.forEach((item) => {
        const figure = createElement('figure', { class: 'media-tile', dataset: { value: item.url } });
        const image = createElement('img', {
          src: item.url,
          alt: item.texto_alternativo || item.titulo || 'Imagen',
        });
        const caption = createElement('figcaption', { class: 'media-tile__caption' }, [
          createElement('strong', { text: item.titulo || item.nombre_original || 'Imagen sin título' }),
          createElement('small', { text: `${item.ancho ?? '—'}×${item.alto ?? '—'} px · ${formatBytes(item.tamano_bytes)}` }),
        ]);
        figure.appendChild(image);
        figure.appendChild(caption);
        if (this.selected.has(item.url)) {
          figure.classList.add('media-tile--selected');
        }
        figure.addEventListener('click', () => {
          if (!this.multiple) {
            this.selected.clear();
            this.selected.add(item.url);
            qsa(this.grid, '.media-tile').forEach((tile) => tile.classList.remove('media-tile--selected'));
            figure.classList.add('media-tile--selected');
          } else {
            if (this.selected.has(item.url)) {
              this.selected.delete(item.url);
              figure.classList.remove('media-tile--selected');
            } else {
              this.selected.add(item.url);
              figure.classList.add('media-tile--selected');
            }
          }
          this.updateStatus();
        });
        this.grid.appendChild(figure);
      });
      this.updateStatus();
    }

    renderError(message) {
      this.grid.innerHTML = '';
      this.emptyState.style.display = 'flex';
      this.emptyState.textContent = message;
      this.confirmButton.disabled = true;
      this.updateStatus();
    }

    updateStatus() {
      const count = this.selected.size;
      this.status.textContent = `${count} seleccionada${count === 1 ? '' : 's'}`;
      this.confirmButton.disabled = count === 0;
    }
  }

  class MediaPickerField {
    constructor(element, modal, library) {
      this.element = element;
      this.modal = modal;
      this.library = library;
      this.multiple = element.dataset.multiple === 'true';
      this.input = qs(element, '[data-media-input]');
      this.preview = qs(element, '[data-media-preview]');
      this.selectedContainer = qs(element, '[data-media-selected]');
      this.openButton = qs(element, '[data-media-open]');
      this.uploadInput = qs(element, '[data-media-upload]');
      this.helpText = qs(element, '.admin-help');
      this.fieldName = this.multiple ? (element.dataset.field || (this.selectedContainer ? this.selectedContainer.dataset.field : 'galeria')) : (this.input ? this.input.name : 'imagen');
      this.init();
    }

    init() {
      if (this.openButton) {
        this.openButton.addEventListener('click', () => this.openModal());
      }
      if (this.uploadInput) {
        this.uploadInput.addEventListener('change', (event) => this.handleUpload(event));
      }
      if (this.input) {
        this.input.addEventListener('input', () => this.refreshPreview());
      }
      if (this.selectedContainer) {
        this.selectedContainer.addEventListener('click', (event) => {
          const button = event.target.closest('[data-media-remove]');
          if (!button) {
            return;
          }
          const item = button.closest('[data-media-item]');
          if (item) {
            item.remove();
          }
        });
      }
      this.refreshPreview();
    }

    openModal() {
      const preselected = this.multiple ? this.getValues() : [this.getValue()].filter(Boolean);
      this.modal.open({
        multiple: this.multiple,
        preselected,
        onConfirm: (values, items) => {
          if (this.multiple) {
            this.setValues(values, items);
          } else if (values.length > 0) {
            this.setValue(values[0]);
          }
        },
      });
    }

    getValue() {
      return this.input ? this.input.value.trim() : '';
    }

    setValue(value) {
      if (this.input) {
        this.input.value = value ?? '';
      }
      this.refreshPreview();
    }

    getValues() {
      if (!this.selectedContainer) {
        return [];
      }
      return qsa(this.selectedContainer, 'input[name="' + this.fieldName + '[]"]').map((input) => input.value.trim()).filter((value) => value !== '');
    }

    setValues(values, items) {
      if (!this.selectedContainer) {
        return;
      }
      const unique = Array.from(new Set(values.filter((value) => typeof value === 'string' && value !== '')));
      this.selectedContainer.innerHTML = '';
      unique.forEach((value) => {
        const label = this.resolveLabel(value, items);
        const chip = createElement('div', { class: 'media-chip', dataset: { mediaItem: value } }, [
          createElement('input', { type: 'hidden', name: `${this.fieldName}[]`, value }),
          createElement('span', { class: 'media-chip__label', text: label }),
          createElement('button', { type: 'button', class: 'media-chip__remove', 'aria-label': 'Quitar', dataset: { mediaRemove: 'true' } }, ['×']),
        ]);
        this.selectedContainer.appendChild(chip);
      });
    }

    resolveLabel(value, items) {
      if (Array.isArray(items)) {
        const match = items.find((item) => item && (item.url === value || `/${item.ruta}` === value));
        if (match) {
          return match.titulo || match.nombre_original || value.split('/').pop();
        }
      }
      return value.split('/').pop() || value;
    }

    refreshPreview() {
      if (!this.preview) {
        return;
      }
      const value = this.getValue();
      if (!value) {
        this.preview.innerHTML = '';
        this.preview.dataset.empty = 'true';
        this.preview.textContent = this.preview.dataset.emptyText || 'Sin selección';
        return;
      }
      this.preview.dataset.empty = 'false';
      this.preview.innerHTML = '';
      const img = createElement('img', { src: value, alt: 'Vista previa', loading: 'lazy' });
      this.preview.appendChild(img);
    }

    async handleUpload(event) {
      const input = event.target;
      if (!(input instanceof HTMLInputElement) || !input.files || input.files.length === 0) {
        return;
      }
      const files = Array.from(input.files);
      input.value = '';
      for (const file of files) {
        try {
          const media = await this.uploadFile(file);
          this.library.add(media);
          if (this.multiple) {
            const current = this.getValues();
            current.push(media.url);
            this.setValues(current, [media]);
          } else {
            this.setValue(media.url);
          }
        } catch (error) {
          const message = error instanceof Error ? error.message : 'No se pudo subir la imagen seleccionada.';
          window.alert(message);
        }
      }
    }

    async uploadFile(file) {
      const formData = new FormData();
      formData.append('archivo', file, file.name);
      formData.append('titulo', file.name.replace(/\.[^.]+$/, ''));
      const response = await fetch(API_ENDPOINT, {
        method: 'POST',
        body: formData,
      });
      if (!response.ok) {
        const payload = await safeParseJson(response);
        const errorMessage = payload && typeof payload.error === 'string' ? payload.error : 'No se pudo subir la imagen.';
        throw new Error(errorMessage);
      }
      const payload = await response.json();
      if (!payload || payload.ok !== true || !payload.item) {
        throw new Error('Respuesta inválida del servidor.');
      }
      return payload.item;
    }
  }

  const safeParseJson = async (response) => {
    try {
      return await response.json();
    } catch (error) {
      return null;
    }
  };

  const initMediaPickers = () => {
    const library = new MediaLibrary();
    const modal = new MediaModal(library);
    qsa(document, '[data-media-picker]').forEach((element) => {
      // eslint-disable-next-line no-new
      new MediaPickerField(element, modal, library);
    });
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMediaPickers);
  } else {
    initMediaPickers();
  }
})();
