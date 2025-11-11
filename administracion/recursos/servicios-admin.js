(function () {
  const modal = document.querySelector('[data-service-modal]');
  if (!modal) {
    return;
  }

  const form = modal.querySelector('[data-service-modal-form]');
  if (!form) {
    return;
  }

  const fields = {
    id: form.querySelector('[data-service-field="id"]'),
    nombre: form.querySelector('[data-service-field="nombre"]'),
    icono: form.querySelector('[data-service-field="icono"]'),
    tipo: form.querySelector('[data-service-field="tipo"]'),
    descripcion: form.querySelector('[data-service-field="descripcion"]'),
    activo: form.querySelector('[data-service-field="activo"]'),
  };

  const state = {
    lastFocused: null,
  };

  const setModalState = (isOpen) => {
    modal.dataset.state = isOpen ? 'visible' : 'hidden';
    modal.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
    document.body.classList.toggle('modal-open', isOpen);
    if (isOpen) {
      modal.removeAttribute('hidden');
    } else {
      modal.setAttribute('hidden', 'hidden');
    }
  };

  const refreshIconPreview = () => {
    if (!fields.icono) {
      return;
    }
    fields.icono.dispatchEvent(new Event('input', { bubbles: true }));
  };

  const fillForm = (button) => {
    if (!button) {
      return;
    }

    const dataset = button.dataset;

    if (fields.id) {
      fields.id.value = dataset.serviceId || '';
    }
    if (fields.nombre) {
      fields.nombre.value = dataset.serviceNombre || '';
    }
    if (fields.icono) {
      fields.icono.value = dataset.serviceIcono || '';
    }
    if (fields.tipo) {
      const tipo = dataset.serviceTipo === 'excluido' ? 'excluido' : 'incluido';
      fields.tipo.value = tipo;
    }
    if (fields.descripcion) {
      fields.descripcion.value = dataset.serviceDescripcion || '';
    }
    if (fields.activo) {
      fields.activo.checked = dataset.serviceActivo === '1';
    }

    refreshIconPreview();
  };

  const focusFirstField = () => {
    if (fields.nombre) {
      fields.nombre.focus();
    }
  };

  const openModal = (button) => {
    state.lastFocused = document.activeElement instanceof HTMLElement ? document.activeElement : null;
    fillForm(button);
    setModalState(true);
    focusFirstField();
  };

  const closeModal = () => {
    setModalState(false);
    if (state.lastFocused && typeof state.lastFocused.focus === 'function') {
      state.lastFocused.focus();
    }
  };

  const closeTriggers = modal.querySelectorAll('[data-service-modal-close]');
  closeTriggers.forEach((trigger) => {
    trigger.addEventListener('click', (event) => {
      event.preventDefault();
      closeModal();
    });
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && modal.dataset.state === 'visible') {
      closeModal();
    }
  });

  const editButtons = document.querySelectorAll('[data-edit-service]');
  editButtons.forEach((button) => {
    button.addEventListener('click', () => {
      openModal(button);
    });
  });

  setModalState(false);
})();
