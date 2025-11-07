(function () {
  const uploadForm = document.getElementById('uploadForm');
  const grid = document.getElementById('lib');
  const modal = document.getElementById('modal');

  if (!uploadForm || !grid || !modal) {
    return;
  }

  const state = {
    mode: 'create',
    current: null
  };

  const fileInput = document.getElementById('file');
  const browseBtn = document.getElementById('browseBtn');
  const addBtn = document.getElementById('addBtn');
  const clearBtn = document.getElementById('clearFile');
  const fileNameLabel = document.getElementById('filename');
  const drop = document.getElementById('drop');

  const uploadHidden = {
    title: document.getElementById('uploadTitulo'),
    alt: document.getElementById('uploadAlt'),
    desc: document.getElementById('uploadDescripcion'),
    credits: document.getElementById('uploadCreditos')
  };

  const updateForm = document.getElementById('mediaUpdateForm');
  const deleteForm = document.getElementById('mediaDeleteForm');
  const updateHidden = {
    id: document.getElementById('updateMediaId'),
    title: document.getElementById('updateTitulo'),
    alt: document.getElementById('updateAlt'),
    desc: document.getElementById('updateDescripcion'),
    credits: document.getElementById('updateCreditos')
  };
  const deleteHidden = {
    id: document.getElementById('deleteMediaId')
  };

  const searchInput = document.getElementById('q');
  const typeSelect = document.getElementById('type');
  const sortSelect = document.getElementById('sort');
  const countEl = document.getElementById('count');
  const emptyState = document.getElementById('empty');
  const clearFiltersBtn = document.getElementById('clearFilters');
  const bulkSelectBtn = document.getElementById('bulkSelectBtn');

  const modalTitle = document.getElementById('modalTitle');
  const modalPreview = document.getElementById('modalPreview');
  const modalMeta = document.getElementById('modalMeta');
  const modalFields = {
    title: document.getElementById('m_title'),
    alt: document.getElementById('m_alt'),
    desc: document.getElementById('m_desc'),
    credits: document.getElementById('m_credits')
  };
  const saveBtn = document.getElementById('saveCard');
  const deleteBtn = document.getElementById('deleteCard');
  const closeBtn = document.getElementById('closeModal');

  function showToast(message) {
    if (!message) {
      return;
    }
    const toast = document.createElement('div');
    toast.className = 'media-toast';
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => {
      toast.classList.add('hide');
      setTimeout(() => {
        toast.remove();
      }, 220);
    }, 1400);
  }

  function updateFileName() {
    if (!fileNameLabel || !fileInput) {
      return;
    }
    fileNameLabel.textContent = fileInput.files && fileInput.files[0]
      ? `Archivo: ${fileInput.files[0].name}`
      : '';
  }

  function clearUploadFields() {
    if (fileInput) {
      fileInput.value = '';
    }
    updateFileName();
    Object.values(uploadHidden).forEach((input) => {
      if (input) {
        input.value = '';
      }
    });
  }

  if (browseBtn && fileInput) {
    browseBtn.addEventListener('click', () => fileInput.click());
  }

  if (clearBtn) {
    clearBtn.addEventListener('click', clearUploadFields);
  }

  if (drop && fileInput) {
    drop.addEventListener('click', (event) => {
      if (event.target === drop) {
        fileInput.click();
      }
    });

    drop.addEventListener('dragover', (event) => {
      event.preventDefault();
      drop.classList.add('drag');
    });

    drop.addEventListener('dragleave', () => {
      drop.classList.remove('drag');
    });

    drop.addEventListener('drop', (event) => {
      event.preventDefault();
      drop.classList.remove('drag');
      const files = event.dataTransfer?.files;
      if (files && files[0]) {
        fileInput.files = files;
        updateFileName();
      }
    });
  }

  if (fileInput) {
    fileInput.addEventListener('change', updateFileName);
  }

  function extractCardData(card) {
    const dataset = card.dataset;
    return {
      id: dataset.id || '',
      title: dataset.title || '',
      alt: dataset.alt || '',
      desc: dataset.desc || '',
      credits: dataset.credits || '',
      src: dataset.src || '',
      kind: dataset.kind || 'foto',
      size: dataset.size || '',
      dimensions: dataset.dimensions || '',
      mime: dataset.mime || '',
      createdLabel: dataset.createdLabel || '',
      name: dataset.title || ''
    };
  }

  function updateModalFields(data) {
    if (!modalFields.title || !modalFields.alt || !modalFields.desc || !modalFields.credits) {
      return;
    }
    modalFields.title.value = data.title || '';
    modalFields.alt.value = data.alt || '';
    modalFields.desc.value = data.desc || '';
    modalFields.credits.value = data.credits || '';
  }

  function setFieldsDisabled(disabled) {
    Object.values(modalFields).forEach((field) => {
      if (field) {
        field.disabled = disabled;
      }
    });
  }

  function setPreviewContent(data, mode) {
    if (!modalPreview) {
      return;
    }

    modalPreview.innerHTML = '';
    if (modalMeta) {
      modalMeta.innerHTML = '';
    }

    if ((mode === 'edit' || mode === 'preview') && data.src) {
      if (data.kind === 'video') {
        const video = document.createElement('video');
        video.controls = true;
        video.src = data.src;
        video.preload = 'metadata';
        modalPreview.appendChild(video);
      } else {
        const img = document.createElement('img');
        img.src = data.src;
        img.alt = data.alt || data.title || 'Vista previa';
        modalPreview.appendChild(img);
      }

    if (modalMeta) {
      const lineas = [];
      if (data.dimensions) {
        lineas.push(data.dimensions);
      }
      if (data.size || data.mime) {
        lineas.push([data.size, data.mime].filter(Boolean).join(' · '));
      }
      if (data.createdLabel) {
        lineas.push(`Creado: ${data.createdLabel}`);
      }
      modalMeta.innerHTML = '';
      lineas.forEach((texto) => {
        const span = document.createElement('span');
        span.textContent = texto;
        modalMeta.appendChild(span);
      });
    }
      return;
    }

    if (mode === 'create' && fileInput && fileInput.files && fileInput.files[0]) {
      const archivo = fileInput.files[0];
      if (archivo.type && archivo.type.startsWith('image/')) {
        const lector = new FileReader();
        lector.onload = () => {
          if (!modalPreview) {
            return;
          }
          modalPreview.innerHTML = '';
          const img = document.createElement('img');
          img.src = typeof lector.result === 'string' ? lector.result : '';
          img.alt = archivo.name || 'Vista previa';
          modalPreview.appendChild(img);
        };
        lector.readAsDataURL(archivo);
      }
    }

    const nombre = state.current && state.current.name ? state.current.name : (fileInput && fileInput.files && fileInput.files[0] ? fileInput.files[0].name : 'Vista previa');
    modalPreview.textContent = nombre || 'Vista previa';

    if (modalMeta && data.size) {
      modalMeta.innerHTML = '';
      const span = document.createElement('span');
      span.textContent = data.size;
      modalMeta.appendChild(span);
    }
  }

  function openModal(mode, data) {
    state.mode = mode;
    state.current = data;

    if (modalTitle) {
      modalTitle.textContent = mode === 'create'
        ? 'Agregar a la biblioteca'
        : mode === 'preview'
          ? 'Vista previa'
          : 'Editar metadatos';
    }

    updateModalFields(data);
    setFieldsDisabled(mode === 'preview');

    if (saveBtn) {
      saveBtn.textContent = mode === 'create' ? 'Agregar a la biblioteca' : mode === 'preview' ? 'Cerrar' : 'Guardar cambios';
    }

    if (deleteBtn) {
      deleteBtn.style.display = mode === 'edit' ? '' : 'none';
    }

    setPreviewContent(data, mode);

    if (typeof modal.showModal === 'function') {
      modal.showModal();
    }
  }

  if (closeBtn) {
    closeBtn.addEventListener('click', () => {
      if (typeof modal.close === 'function') {
        modal.close();
      }
    });
  }

  modal.addEventListener('cancel', (event) => {
    event.preventDefault();
    if (typeof modal.close === 'function') {
      modal.close();
    }
  });

  if (saveBtn) {
    saveBtn.addEventListener('click', () => {
      if (state.mode === 'preview') {
        if (typeof modal.close === 'function') {
          modal.close();
        }
        return;
      }

      const titulo = modalFields.title ? modalFields.title.value.trim() : '';
      const alt = modalFields.alt ? modalFields.alt.value.trim() : '';
      const desc = modalFields.desc ? modalFields.desc.value.trim() : '';
      const credits = modalFields.credits ? modalFields.credits.value.trim() : '';

      if (state.mode === 'edit') {
        if (!titulo) {
          showToast('El título no puede estar vacío.');
          if (modalFields.title) {
            modalFields.title.focus();
          }
          return;
        }
        if (updateHidden.id) {
          updateHidden.id.value = state.current ? state.current.id : '';
        }
        if (updateHidden.title) {
          updateHidden.title.value = titulo;
        }
        if (updateHidden.alt) {
          updateHidden.alt.value = alt;
        }
        if (updateHidden.desc) {
          updateHidden.desc.value = desc;
        }
        if (updateHidden.credits) {
          updateHidden.credits.value = credits;
        }
        if (updateForm) {
          if (typeof updateForm.requestSubmit === 'function') {
            updateForm.requestSubmit();
          } else {
            updateForm.submit();
          }
        }
        if (typeof modal.close === 'function') {
          modal.close();
        }
        return;
      }

      if (!fileInput || !fileInput.files || !fileInput.files.length) {
        showToast('Selecciona un archivo antes de continuar.');
        return;
      }

      if (uploadHidden.title) {
        uploadHidden.title.value = titulo || fileInput.files[0].name;
      }
      if (uploadHidden.alt) {
        uploadHidden.alt.value = alt;
      }
      if (uploadHidden.desc) {
        uploadHidden.desc.value = desc;
      }
      if (uploadHidden.credits) {
        uploadHidden.credits.value = credits;
      }

      if (typeof modal.close === 'function') {
        modal.close();
      }

      if (typeof uploadForm.requestSubmit === 'function') {
        uploadForm.requestSubmit();
      } else {
        uploadForm.submit();
      }
    });
  }

  if (deleteBtn) {
    deleteBtn.addEventListener('click', () => {
      if (state.mode !== 'edit' || !state.current) {
        return;
      }
      const confirmacion = window.confirm('¿Eliminar este medio de forma permanente?');
      if (!confirmacion) {
        return;
      }
      if (deleteHidden.id) {
        deleteHidden.id.value = state.current.id;
      }
      if (deleteForm) {
        if (typeof deleteForm.requestSubmit === 'function') {
          deleteForm.requestSubmit();
        } else {
          deleteForm.submit();
        }
      }
      if (typeof modal.close === 'function') {
        modal.close();
      }
    });
  }

  if (addBtn) {
    addBtn.addEventListener('click', () => {
      if (!fileInput || !fileInput.files || !fileInput.files.length) {
        showToast('Selecciona un archivo para agregar a la biblioteca.');
        return;
      }
      const archivo = fileInput.files[0];
      const nombre = archivo.name || 'Nuevo medio';
      const extensionIndex = nombre.lastIndexOf('.');
      const tituloPorDefecto = extensionIndex > 0 ? nombre.slice(0, extensionIndex) : nombre;
      openModal('create', {
        id: '',
        title: tituloPorDefecto,
        alt: '',
        desc: '',
        credits: '',
        src: '',
        kind: archivo.type && archivo.type.startsWith('video') ? 'video' : 'foto',
        size: '',
        dimensions: '',
        mime: archivo.type || '',
        createdLabel: '',
        name: nombre
      });
    });
  }

  if (bulkSelectBtn) {
    bulkSelectBtn.addEventListener('click', () => {
      showToast('La selección múltiple estará disponible próximamente.');
    });
  }

  grid.addEventListener('click', (event) => {
    const target = event.target instanceof HTMLElement ? event.target.closest('[data-action]') : null;
    if (!target) {
      return;
    }
    const card = target.closest('.media-card');
    if (!card) {
      return;
    }
    const data = extractCardData(card);
    const action = target.getAttribute('data-action');

    if (action === 'edit') {
      openModal('edit', data);
    } else if (action === 'preview') {
      openModal('preview', data);
    } else if (action === 'remove') {
      const confirmacion = window.confirm('¿Eliminar este medio de forma permanente?');
      if (!confirmacion) {
        return;
      }
      if (deleteHidden.id) {
        deleteHidden.id.value = data.id;
      }
      if (deleteForm) {
        if (typeof deleteForm.requestSubmit === 'function') {
          deleteForm.requestSubmit();
        } else {
          deleteForm.submit();
        }
      }
    }
  });

  function sortCards(cards, criterio) {
    const copia = cards.slice();
    if (criterio === 'az') {
      copia.sort((a, b) => a.dataset.title.localeCompare(b.dataset.title, 'es', { sensitivity: 'base' }));
    } else if (criterio === 'old') {
      copia.sort((a, b) => Number(a.dataset.created || 0) - Number(b.dataset.created || 0));
    } else {
      copia.sort((a, b) => Number(b.dataset.created || 0) - Number(a.dataset.created || 0));
    }
    return copia;
  }

  function applyFilters() {
    const cards = Array.from(grid.querySelectorAll('.media-card'));
    const ordenadas = sortCards(cards, sortSelect ? sortSelect.value : 'new');
    ordenadas.forEach((card) => {
      grid.appendChild(card);
    });

    const termino = (searchInput ? searchInput.value : '').trim().toLowerCase();
    const tipo = typeSelect ? typeSelect.value : '';
    let visibles = 0;

    ordenadas.forEach((card) => {
      const texto = card.dataset.search || '';
      const coincideTermino = !termino || texto.includes(termino);
      const coincideTipo = !tipo || card.dataset.kind === tipo;
      if (coincideTermino && coincideTipo) {
        card.style.display = '';
        visibles += 1;
      } else {
        card.style.display = 'none';
      }
    });

    if (countEl) {
      countEl.textContent = String(visibles);
    }

    if (emptyState) {
      emptyState.style.display = visibles ? 'none' : 'block';
    }
  }

  if (searchInput) {
    searchInput.addEventListener('input', applyFilters);
  }

  document.addEventListener('keydown', (event) => {
    if (event.key === '/' && !event.defaultPrevented && !event.metaKey && !event.ctrlKey && !event.altKey) {
      const tagName = event.target instanceof HTMLElement ? event.target.tagName.toLowerCase() : '';
      if (tagName === 'input' || tagName === 'textarea') {
        return;
      }
      event.preventDefault();
      if (searchInput) {
        searchInput.focus();
      }
    }
  });

  if (typeSelect) {
    typeSelect.addEventListener('change', applyFilters);
  }

  if (sortSelect) {
    sortSelect.addEventListener('change', applyFilters);
  }

  if (clearFiltersBtn) {
    clearFiltersBtn.addEventListener('click', () => {
      if (searchInput) {
        searchInput.value = '';
      }
      if (typeSelect) {
        typeSelect.value = '';
      }
      if (sortSelect) {
        sortSelect.value = 'new';
      }
      applyFilters();
    });
  }

  applyFilters();
})();
