(() => {
  const doc = document;

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
})();
