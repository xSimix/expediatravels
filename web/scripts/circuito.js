(() => {
  const doc = document;

  const clamp = (value, min, max) => Math.min(Math.max(value, min), max);

  const heroGallery = doc.querySelector('[data-hero-gallery]');
  const heroTrack = heroGallery ? heroGallery.querySelector('[data-hero-track]') : null;
  if (heroTrack) {
    const slides = Array.from(heroTrack.querySelectorAll('[data-hero-slide]'));
    const prevButton = doc.querySelector('[data-hero-prev]');
    const nextButton = doc.querySelector('[data-hero-next]');
    const lightbox = doc.querySelector('[data-lightbox]');
    const lightboxImage = lightbox ? lightbox.querySelector('[data-lightbox-image]') : null;
    const lightboxClose = lightbox ? lightbox.querySelector('[data-lightbox-close]') : null;
    const lightboxBackdrop = lightbox ? lightbox.querySelector('[data-lightbox-backdrop]') : null;
    let index = 0;
    let autoTimer = null;
    const autoDelay = 5000;
    let lastFocusedSlide = null;

    const getColumns = () => {
      const raw = getComputedStyle(heroTrack).getPropertyValue('--hero-columns');
      const parsed = parseInt(raw, 10);
      return Number.isNaN(parsed) || parsed <= 0 ? 1 : parsed;
    };

    const getMaxIndex = () => {
      const columns = getColumns();
      return Math.max(0, Math.ceil(slides.length / columns) - 1);
    };

    const syncNavigation = () => {
      const maxIndex = getMaxIndex();
      const isStatic = maxIndex === 0;
      if (prevButton) {
        prevButton.disabled = isStatic || index === 0;
      }
      if (nextButton) {
        nextButton.disabled = isStatic || index >= maxIndex;
      }
    };

    const goTo = (target, { wrap = false } = {}) => {
      const maxIndex = getMaxIndex();
      if (maxIndex === 0) {
        index = 0;
      } else if (wrap) {
        if (target > maxIndex) {
          index = 0;
        } else if (target < 0) {
          index = maxIndex;
        } else {
          index = target;
        }
      } else {
        index = clamp(target, 0, maxIndex);
      }
      heroTrack.style.setProperty('--hero-offset', `${index * 100}%`);
      syncNavigation();
    };

    const stopAuto = () => {
      if (autoTimer) {
        window.clearInterval(autoTimer);
        autoTimer = null;
      }
    };

    const startAuto = () => {
      if (autoTimer || getMaxIndex() === 0) {
        return;
      }
      autoTimer = window.setInterval(() => {
        goTo(index + 1, { wrap: true });
      }, autoDelay);
    };

    const restartAuto = () => {
      stopAuto();
      startAuto();
    };

    prevButton?.addEventListener('click', () => {
      goTo(index - 1);
      restartAuto();
    });

    nextButton?.addEventListener('click', () => {
      goTo(index + 1);
      restartAuto();
    });

    window.addEventListener('resize', () => {
      goTo(index);
      restartAuto();
    });

    heroGallery?.addEventListener('mouseenter', stopAuto);
    heroGallery?.addEventListener('mouseleave', startAuto);
    heroGallery?.addEventListener('focusin', stopAuto);
    heroGallery?.addEventListener('focusout', startAuto);

    const openLightbox = (slide, src, alt) => {
      if (!lightbox || !lightboxImage) {
        return;
      }
      lightboxImage.src = src;
      lightboxImage.alt = alt || '';
      lightbox.hidden = false;
      requestAnimationFrame(() => {
        lightbox.classList.add('is-open');
        lightboxClose?.focus({ preventScroll: true });
      });
      doc.body.classList.add('has-lightbox-open');
      lastFocusedSlide = slide;
      stopAuto();
    };

    const closeLightbox = () => {
      if (!lightbox || lightbox.hidden) {
        return;
      }
      lightbox.classList.remove('is-open');
      const finalize = () => {
        lightbox.hidden = true;
        lightbox.removeEventListener('transitionend', finalize);
      };
      lightbox.addEventListener('transitionend', finalize, { once: true });
      window.setTimeout(finalize, 320);
      doc.body.classList.remove('has-lightbox-open');
      if (lastFocusedSlide) {
        lastFocusedSlide.focus({ preventScroll: true });
        lastFocusedSlide = null;
      }
      startAuto();
    };

    heroTrack.addEventListener('click', (event) => {
      const slide = event.target.closest('[data-hero-slide]');
      if (!slide) {
        return;
      }
      const src = slide.dataset.lightboxSrc || slide.querySelector('img')?.getAttribute('src');
      if (!src) {
        return;
      }
      const alt = slide.dataset.lightboxAlt || slide.querySelector('img')?.getAttribute('alt') || '';
      openLightbox(slide, src, alt);
    });

    slides.forEach((slide) => {
      slide.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
          event.preventDefault();
          const src = slide.dataset.lightboxSrc || slide.querySelector('img')?.getAttribute('src');
          if (!src) {
            return;
          }
          const alt = slide.dataset.lightboxAlt || slide.querySelector('img')?.getAttribute('alt') || '';
          openLightbox(slide, src, alt);
        }
      });
    });

    lightboxClose?.addEventListener('click', closeLightbox);
    lightboxBackdrop?.addEventListener('click', closeLightbox);
    doc.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') {
        closeLightbox();
      }
    });

    goTo(index);
    startAuto();
  }

  const selectionInputs = doc.querySelectorAll('[data-selection-source]');
  const selectionCloud = doc.querySelector('[data-selection-cloud]');
  const updateSelectionCloud = () => {
    if (!selectionCloud) {
      return;
    }
    selectionCloud.innerHTML = '';
    const selected = Array.from(selectionInputs).filter((input) => input.checked);
    if (selected.length === 0) {
      const emptyChip = doc.createElement('span');
      emptyChip.className = 'selection-chip selection-chip--empty';
      emptyChip.textContent = 'Selecciona elementos para construir tu nube.';
      selectionCloud.appendChild(emptyChip);
      return;
    }
    selected.forEach((input) => {
      const chip = doc.createElement('span');
      const group = input.dataset.selectionGroup || 'include';
      chip.className = `selection-chip selection-chip--${group}`;
      chip.textContent = input.dataset.label || input.value;
      selectionCloud.appendChild(chip);
    });
  };

  if (selectionInputs.length > 0) {
    selectionInputs.forEach((input) => {
      input.addEventListener('change', updateSelectionCloud);
    });
    updateSelectionCloud();
  }

  const mapFrame = doc.querySelector('[data-map-frame]');
  const mapButtons = doc.querySelectorAll('[data-map-marker]');
  if (mapFrame && mapButtons.length > 0) {
    mapButtons.forEach((button) => {
      button.addEventListener('click', () => {
        const src = button.dataset.mapMarker;
        if (!src) {
          return;
        }
        mapButtons.forEach((btn) => btn.classList.remove('is-active'));
        button.classList.add('is-active');
        mapFrame.setAttribute('src', src);
      });
    });
  }

  const setStatusMessage = (element, message, type) => {
    if (!element) {
      return;
    }
    element.textContent = message;
    element.classList.remove('is-success', 'is-error');
    if (type) {
      element.classList.add(type === 'success' ? 'is-success' : 'is-error');
    }
  };

  const toggleFormLoading = (form, isLoading) => {
    if (!form) {
      return;
    }
    const buttons = form.querySelectorAll('[data-loading]');
    buttons.forEach((button) => {
      if (!button.dataset.originalLabel) {
        button.dataset.originalLabel = button.textContent;
      }
      button.disabled = isLoading;
      button.textContent = isLoading ? 'Enviando…' : button.dataset.originalLabel;
    });
  };

  const createReviewCard = (review) => {
    const li = doc.createElement('li');
    li.className = 'review-card';

    const header = doc.createElement('div');
    header.className = 'review-card__header';

    const name = doc.createElement('strong');
    name.textContent = review.nombre || 'Viajero';
    header.appendChild(name);

    const stars = doc.createElement('div');
    stars.className = 'review-card__stars';
    const scoreRaw = Number(review.rating);
    const score = Number.isNaN(scoreRaw) ? 0 : Math.max(0, Math.min(10, scoreRaw));
    stars.style.setProperty('--rating', score.toFixed(1));
    stars.setAttribute('aria-label', `${score.toFixed(1)} de 10`);
    header.appendChild(stars);

    li.appendChild(header);

    if (review.comentario) {
      const comment = doc.createElement('p');
      comment.className = 'review-card__comment';
      comment.textContent = review.comentario;
      li.appendChild(comment);
    }

    if (review.creado_en) {
      const date = doc.createElement('small');
      date.className = 'review-card__date';
      date.textContent = `Publicado el ${review.creado_en}`;
      li.appendChild(date);
    }

    return li;
  };

  const updateReviewSummary = (payload) => {
    const average = Number(payload.average || 0).toFixed(1);
    const count = Number(payload.count || 0);
    const stars = doc.querySelector('[data-review-stars]');
    const starsSecondary = doc.querySelector('[data-review-stars-secondary]');
    const averageEls = doc.querySelectorAll('[data-review-average], [data-review-average-secondary]');
    const countEls = doc.querySelectorAll('[data-review-count], [data-review-count-secondary]');

    stars?.style.setProperty('--rating', average);
    starsSecondary?.style.setProperty('--rating', average);

    averageEls.forEach((node) => {
      node.textContent = average;
    });

    countEls.forEach((node) => {
      node.textContent = new Intl.NumberFormat('es-PE').format(count);
    });
  };

  const renderReviews = (payload) => {
    const list = doc.querySelector('[data-review-list]');
    if (!list) {
      return;
    }
    list.innerHTML = '';
    const reviews = Array.isArray(payload.reviews) ? payload.reviews : [];
    if (reviews.length === 0) {
      const empty = doc.createElement('li');
      empty.className = 'reviews__empty';
      empty.textContent = 'Sé la primera persona en dejar una reseña sobre este circuito.';
      list.appendChild(empty);
    } else {
      reviews.forEach((review) => {
        list.appendChild(createReviewCard(review));
      });
    }
    updateReviewSummary(payload);
  };

  const reservationForm = doc.querySelector('[data-reservation-form]');
  if (reservationForm) {
    const status = reservationForm.querySelector('[data-reservation-status]');
    reservationForm.addEventListener('submit', async (event) => {
      event.preventDefault();
      setStatusMessage(status, '', null);
      toggleFormLoading(reservationForm, true);
      const formData = new FormData(reservationForm);
      const payload = Object.fromEntries(formData.entries());
      payload.cantidad_personas = Number(payload.cantidad_personas || 1);

      try {
        const response = await fetch(reservationForm.getAttribute('action') || (window.circuitoPageConfig?.reservationEndpoint ?? ''), {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload),
        });

        const data = await response.json();
        if (!response.ok || !data.ok) {
          const message = data.message || 'Ocurrió un problema al registrar tu reserva.';
          setStatusMessage(status, message, 'error');
          return;
        }

        setStatusMessage(status, data.message || '¡Gracias! Registramos tu solicitud.', 'success');
        reservationForm.reset();
        updateSelectionCloud();
      } catch (error) {
        setStatusMessage(status, 'No pudimos conectar con el servidor. Inténtalo nuevamente.', 'error');
      } finally {
        toggleFormLoading(reservationForm, false);
      }
    });
  }

  const reviewForm = doc.querySelector('[data-review-form]');
  if (reviewForm) {
    const status = reviewForm.querySelector('[data-review-status]');
    reviewForm.addEventListener('submit', async (event) => {
      event.preventDefault();
      setStatusMessage(status, '', null);
      toggleFormLoading(reviewForm, true);

      const formData = new FormData(reviewForm);
      const payload = Object.fromEntries(formData.entries());
      const rawRating = Number(payload.rating || 10);
      const sanitizedRating = Number.isNaN(rawRating) ? 10 : Math.round(rawRating);
      payload.rating = Math.max(1, Math.min(10, sanitizedRating));

      try {
        const response = await fetch(reviewForm.getAttribute('action') || (window.circuitoPageConfig?.reviewEndpoint ?? ''), {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload),
        });
        const data = await response.json();
        if (!response.ok || !data.ok) {
          const message = data.message || 'No pudimos registrar tu reseña.';
          setStatusMessage(status, message, 'error');
          return;
        }

        if (data.payload) {
          renderReviews(data.payload);
        }
        setStatusMessage(status, data.message || '¡Gracias por compartir tu reseña!', 'success');
        reviewForm.reset();
      } catch (error) {
        setStatusMessage(status, 'No pudimos conectar con el servidor. Inténtalo más tarde.', 'error');
      } finally {
        toggleFormLoading(reviewForm, false);
      }
    });
  }
})();
