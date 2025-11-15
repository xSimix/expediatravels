(() => {
  const doc = document;
  const prefersReducedMotion =
    typeof window !== 'undefined' && typeof window.matchMedia === 'function'
      ? window.matchMedia('(prefers-reduced-motion: reduce)')
      : null;

  const accordionGroups = doc.querySelectorAll('[data-accordion]');
  accordionGroups.forEach((group) => {
    const items = Array.from(group.querySelectorAll('[data-accordion-item]'));
    items.forEach((item) => {
      const trigger = item.querySelector('[data-accordion-trigger]');
      const content = item.querySelector('[data-accordion-content]');
      if (!trigger || !content) {
        return;
      }

      trigger.addEventListener('click', () => {
        const isOpen = item.classList.contains('is-open');
        items.forEach((other) => {
          if (other === item) {
            return;
          }
          other.classList.remove('is-open');
          const otherTrigger = other.querySelector('[data-accordion-trigger]');
          const otherContent = other.querySelector('[data-accordion-content]');
          if (otherTrigger) {
            otherTrigger.setAttribute('aria-expanded', 'false');
          }
          if (otherContent) {
            otherContent.hidden = true;
          }
        });

        if (isOpen) {
          item.classList.remove('is-open');
          trigger.setAttribute('aria-expanded', 'false');
          content.hidden = true;
        } else {
          item.classList.add('is-open');
          trigger.setAttribute('aria-expanded', 'true');
          content.hidden = false;
        }
      });
    });
  });

  const counters = doc.querySelectorAll('[data-counter]');
  counters.forEach((counter) => {
    const input = counter.querySelector('input[type="number"]');
    const decrease = counter.querySelector('[data-counter-decrease]');
    const increase = counter.querySelector('[data-counter-increase]');
    if (!input || !decrease || !increase) {
      return;
    }

    const min = Number(input.getAttribute('min')) || 0;

    const sync = () => {
      const value = Number(input.value);
      decrease.disabled = value <= min;
    };

    decrease.addEventListener('click', () => {
      const current = Number(input.value);
      if (current > min) {
        input.value = String(current - 1);
        sync();
      }
    });

    increase.addEventListener('click', () => {
      const current = Number(input.value);
      input.value = String(current + 1);
      sync();
    });

    sync();
  });

  const gallerySliders = doc.querySelectorAll('[data-gallery-slider]');
  gallerySliders.forEach((slider) => {
    const track = slider.querySelector('[data-gallery-track]');
    const viewport = slider.querySelector('[data-gallery-viewport]');
    if (!track || !viewport) {
      return;
    }

    const slides = Array.from(track.querySelectorAll('[data-gallery-slide]'));
    if (!slides.length) {
      return;
    }

    viewport.style.touchAction = 'pan-y';

    const dots = Array.from(slider.querySelectorAll('[data-gallery-dot]'));
    let currentIndex = 0;
    let autoplayId;
    let isPointerDown = false;
    let startX = 0;
    let preventClick = false;

    const intervalAttr = Number(slider.getAttribute('data-gallery-interval'));
    const autoplayInterval = Number.isFinite(intervalAttr) && intervalAttr > 0 ? intervalAttr : 5000;

    const setActiveDot = (index) => {
      dots.forEach((dot, dotIndex) => {
        const isActive = dotIndex === index;
        dot.classList.toggle('is-active', isActive);
        if (isActive) {
          dot.setAttribute('aria-current', 'true');
        } else {
          dot.removeAttribute('aria-current');
        }
      });
    };

    const goTo = (index) => {
      if (!slides.length) {
        return;
      }
      const total = slides.length;
      currentIndex = ((index % total) + total) % total;
      track.style.transition = '';
      track.style.transform = `translateX(-${currentIndex * 100}%)`;
      setActiveDot(currentIndex);
    };

    const stopAutoplay = () => {
      if (autoplayId) {
        window.clearInterval(autoplayId);
        autoplayId = undefined;
      }
    };

    const startAutoplay = () => {
      if (slides.length <= 1) {
        return;
      }
      if (prefersReducedMotion && prefersReducedMotion.matches) {
        return;
      }
      stopAutoplay();
      autoplayId = window.setInterval(() => {
        goTo(currentIndex + 1);
      }, autoplayInterval);
    };

    if (prefersReducedMotion) {
      const handleMotionChange = (event) => {
        const shouldReduce = event && typeof event.matches === 'boolean' ? event.matches : prefersReducedMotion.matches;
        if (shouldReduce) {
          stopAutoplay();
        } else {
          startAutoplay();
        }
      };

      if (typeof prefersReducedMotion.addEventListener === 'function') {
        prefersReducedMotion.addEventListener('change', handleMotionChange);
      } else if (typeof prefersReducedMotion.addListener === 'function') {
        prefersReducedMotion.addListener(handleMotionChange);
      }
    }

    dots.forEach((dot, dotIndex) => {
      dot.addEventListener('click', () => {
        goTo(dotIndex);
        startAutoplay();
      });
    });

    slider.addEventListener('mouseenter', stopAutoplay);
    slider.addEventListener('mouseleave', startAutoplay);
    slider.addEventListener('focusin', stopAutoplay);
    slider.addEventListener('focusout', () => {
      if (!slider.contains(doc.activeElement)) {
        startAutoplay();
      }
    });
    slider.addEventListener('touchstart', stopAutoplay, { passive: true });
    slider.addEventListener('touchend', startAutoplay);
    slider.addEventListener('touchcancel', startAutoplay);

    const getViewportWidth = () => viewport.getBoundingClientRect().width || 1;

    const finishDrag = (event) => {
      if (!isPointerDown) {
        return;
      }
      isPointerDown = false;
      track.style.transition = '';
      if (typeof viewport.releasePointerCapture === 'function' && typeof event.pointerId === 'number') {
        try {
          viewport.releasePointerCapture(event.pointerId);
        } catch (error) {
          // ignore release errors
        }
      }

      const clientX = typeof event.clientX === 'number' ? event.clientX : startX;
      const delta = clientX - startX;
      const width = getViewportWidth();
      const threshold = width * 0.18;

      if (Math.abs(delta) > threshold) {
        if (delta < 0) {
          goTo(currentIndex + 1);
        } else {
          goTo(currentIndex - 1);
        }
      } else {
        goTo(currentIndex);
      }

      window.setTimeout(() => {
        preventClick = false;
      }, 0);
      startAutoplay();
    };

    viewport.addEventListener(
      'pointerdown',
      (event) => {
        if (event.button !== undefined && event.button !== 0 && event.pointerType === 'mouse') {
          return;
        }
        isPointerDown = true;
        preventClick = false;
        startX = event.clientX;
        track.style.transition = 'none';
        stopAutoplay();

        if (typeof viewport.setPointerCapture === 'function' && typeof event.pointerId === 'number') {
          try {
            viewport.setPointerCapture(event.pointerId);
          } catch (error) {
            // ignore capture errors
          }
        }

        if (event.pointerType === 'mouse') {
          event.preventDefault();
        }
      },
      { passive: false }
    );

    viewport.addEventListener(
      'pointermove',
      (event) => {
        if (!isPointerDown) {
          return;
        }

        const clientX = typeof event.clientX === 'number' ? event.clientX : startX;
        const delta = clientX - startX;
        const width = getViewportWidth();
        if (Math.abs(delta) > 5) {
          preventClick = true;
        }

        const percentDelta = (delta / width) * 100;
        track.style.transform = `translateX(${-(currentIndex * 100) + percentDelta}%)`;
      },
      { passive: true }
    );

    viewport.addEventListener('pointerup', finishDrag, { passive: true });
    viewport.addEventListener('pointercancel', finishDrag, { passive: true });

    viewport.addEventListener(
      'click',
      (event) => {
        if (!preventClick) {
          return;
        }
        event.preventDefault();
        event.stopPropagation();
        preventClick = false;
      },
      true
    );

    goTo(0);
    startAutoplay();
  });

  const galleryLightboxes = doc.querySelectorAll('[data-gallery-lightbox]');
  galleryLightboxes.forEach((lightbox) => {
    const imageEl = lightbox.querySelector('[data-lightbox-image]');
    const captionEl = lightbox.querySelector('[data-lightbox-caption]');
    const closeElements = lightbox.querySelectorAll('[data-lightbox-close]');
    if (!imageEl || !captionEl) {
      return;
    }

    const gallerySection = lightbox.closest('.detail-section--gallery');
    const scope = gallerySection || doc;
    const triggers = scope.querySelectorAll('[data-gallery-lightbox-trigger]');
    if (!triggers.length) {
      return;
    }

    if (gallerySection && lightbox.parentElement !== doc.body) {
      doc.body.appendChild(lightbox);
    }

    let lastTrigger = null;

    const closeLightbox = () => {
      lightbox.classList.remove('is-open');
      lightbox.hidden = true;
      imageEl.removeAttribute('src');
      imageEl.setAttribute('alt', '');
      captionEl.textContent = '';
      captionEl.hidden = true;
      doc.body.classList.remove('is-lightbox-open');
      if (lastTrigger) {
        lastTrigger.focus();
        lastTrigger = null;
      }
    };

    const openLightbox = (trigger) => {
      const { lightboxSrc, lightboxAlt, lightboxCaption } = trigger.dataset;
      if (!lightboxSrc) {
        return;
      }

      lastTrigger = trigger;
      imageEl.setAttribute('src', lightboxSrc);
      imageEl.setAttribute('alt', lightboxAlt || '');
      const captionText = lightboxCaption && lightboxCaption.trim() ? lightboxCaption.trim() : '';
      captionEl.textContent = captionText;
      captionEl.hidden = captionText === '';
      lightbox.hidden = false;
      doc.body.classList.add('is-lightbox-open');
      window.requestAnimationFrame(() => {
        lightbox.classList.add('is-open');
        lightbox.focus();
      });
    };

    triggers.forEach((trigger) => {
      trigger.addEventListener('click', () => {
        openLightbox(trigger);
      });
    });

    closeElements.forEach((element) => {
      element.addEventListener('click', closeLightbox);
    });

    lightbox.addEventListener('click', (event) => {
      if (event.target === lightbox) {
        closeLightbox();
      }
    });

    doc.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && !lightbox.hidden) {
        closeLightbox();
      }
    });
  });

  const videoLightbox = doc.querySelector('[data-video-lightbox]');
  if (videoLightbox) {
    const frame = videoLightbox.querySelector('[data-video-frame]');
    const player = videoLightbox.querySelector('[data-video-player]');
    const closeElements = videoLightbox.querySelectorAll('[data-lightbox-close]');
    const triggers = doc.querySelectorAll('[data-video-lightbox-trigger]');

    if (frame && player && triggers.length) {
      let lastTrigger = null;

      const resetSources = () => {
        frame.setAttribute('src', '');
        frame.hidden = true;
        player.pause();
        player.removeAttribute('src');
        player.hidden = true;
      };

      const closeLightbox = () => {
        videoLightbox.classList.remove('is-open');
        videoLightbox.hidden = true;
        resetSources();
        doc.body.classList.remove('is-lightbox-open');
        if (lastTrigger) {
          lastTrigger.focus();
          lastTrigger = null;
        }
      };

      const openLightbox = (trigger) => {
        const src = trigger.getAttribute('data-video-src');
        const kind = trigger.getAttribute('data-video-kind');
        if (!src) {
          return;
        }

        resetSources();
        lastTrigger = trigger;

        if (kind === 'file') {
          player.hidden = false;
          player.setAttribute('src', src);
          player.load();
          const playPromise = player.play();
          if (playPromise && typeof playPromise.catch === 'function') {
            playPromise.catch(() => {});
          }
        } else {
          frame.hidden = false;
          const hasQuery = src.includes('?');
          const autoplaySrc = `${src}${hasQuery ? '&' : '?'}autoplay=1`;
          frame.setAttribute('src', autoplaySrc);
        }

        videoLightbox.hidden = false;
        doc.body.classList.add('is-lightbox-open');
        window.requestAnimationFrame(() => {
          videoLightbox.classList.add('is-open');
          videoLightbox.focus();
        });
      };

      triggers.forEach((trigger) => {
        trigger.addEventListener('click', () => {
          openLightbox(trigger);
        });
      });

      closeElements.forEach((element) => {
        element.addEventListener('click', closeLightbox);
      });

      videoLightbox.addEventListener('click', (event) => {
        if (event.target === videoLightbox) {
          closeLightbox();
        }
      });

      doc.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !videoLightbox.hidden) {
          closeLightbox();
        }
      });
    }
  }

  const bookingForm = doc.querySelector('[data-whatsapp-booking]');
  if (bookingForm) {
    bookingForm.addEventListener('submit', (event) => {
      event.preventDefault();

      const formData = new FormData(bookingForm);
      const nameValue = String(formData.get('name') ?? '').trim();
      const phoneValue = String(formData.get('phone') ?? '').trim();
      const emailValue = String(formData.get('email') ?? '').trim();
      const dateValue = String(formData.get('date') ?? '').trim();
      const notesValue = String(formData.get('message') ?? '').trim();
      const packageName = String(bookingForm.getAttribute('data-package-name') ?? '').trim();

      let formattedDate = '';
      if (dateValue) {
        const parsedDate = new Date(`${dateValue}T00:00:00`);
        if (!Number.isNaN(parsedDate.getTime())) {
          try {
            formattedDate = new Intl.DateTimeFormat('es-PE', {
              dateStyle: 'long',
            }).format(parsedDate);
          } catch (error) {
            formattedDate = dateValue;
          }
        } else {
          formattedDate = dateValue;
        }
      }

      const travellerDefinitions = [
        { name: 'adults', label: 'Adultos' },
        { name: 'children', label: 'Niños' },
        { name: 'infant', label: 'Infantes' },
      ];

      const messageLines = [];
      messageLines.push('*🧾 Solicitud de reserva*');

      if (packageName) {
        messageLines.push(`*Circuito:* ${packageName}`);
      }
      if (nameValue) {
        messageLines.push(`*Nombre:* ${nameValue}`);
      }
      if (phoneValue) {
        messageLines.push(`*Teléfono:* ${phoneValue}`);
      }
      if (emailValue) {
        messageLines.push(`*Correo:* ${emailValue}`);
      }
      if (dateValue) {
        messageLines.push(`*Fecha de viaje:* ${formattedDate || dateValue}`);
      }

      const travellerLines = travellerDefinitions
        .map(({ name, label }) => {
          const rawValue = formData.get(name);
          const numericValue = typeof rawValue === 'string' && rawValue.trim() !== '' ? Number(rawValue) : 0;
          if (!Number.isFinite(numericValue)) {
            return null;
          }
          return `*${label}:* ${numericValue}`;
        })
        .filter((line) => line);

      if (travellerLines.length) {
        messageLines.push('');
        messageLines.push('*Detalle de viajeros*');
        messageLines.push(...travellerLines.map((line) => `• ${line}`));
      }

      if (notesValue) {
        messageLines.push('');
        messageLines.push('*Comentarios adicionales*');
        messageLines.push(notesValue);
      }

      if (messageLines.length === 1) {
        messageLines.push('Solicitud de reserva desde la web.');
      }

      messageLines.push('');
      messageLines.push('*Enviado desde la web de Expedia Travels*');

      const whatsappNumber = '51930140668';
      const whatsappUrl = `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(messageLines.join('\n'))}`;

      const newWindow = window.open(whatsappUrl, '_blank', 'noopener');
      if (!newWindow) {
        window.location.href = whatsappUrl;
      }
    });
  }
})();
