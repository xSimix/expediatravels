(function () {
  const updatePreview = (input, target) => {
    if (!target) {
      return;
    }
    const fallback = target.dataset.iconFallback || '⦿';
    const value = (input.value || '').trim();
    target.innerHTML = '';

    if (value === '') {
      target.textContent = fallback;
      return;
    }

    if (value.includes('fa-')) {
      const icon = document.createElement('i');
      icon.className = value;
      icon.setAttribute('aria-hidden', 'true');
      target.appendChild(icon);
    } else {
      target.textContent = value;
    }
  };

  const inputs = document.querySelectorAll('[data-icon-preview-target]');
  inputs.forEach((input) => {
    const selector = input.getAttribute('data-icon-preview-target');
    if (!selector) {
      return;
    }
    const target = document.querySelector(selector);
    if (!target) {
      return;
    }

    const refresh = () => updatePreview(input, target);
    input.addEventListener('input', refresh);
    refresh();
  });

  const openSearch = (query) => {
    const baseUrl = 'https://fontawesome.com/search';
    const url = `${baseUrl}?q=${encodeURIComponent(query)}&m=free`;
    window.open(url, '_blank', 'noopener');
  };

  document.querySelectorAll('[data-icon-search-container]').forEach((container) => {
    const searchInput = container.querySelector('[data-icon-search-input]');
    const button = container.querySelector('[data-icon-search-button]');
    if (!button) {
      return;
    }

    const targetSelector = button.getAttribute('data-icon-search-target');
    const targetInput = targetSelector ? document.querySelector(targetSelector) : null;

    const handleSearch = () => {
      const querySource = searchInput && searchInput.value.trim() !== ''
        ? searchInput.value.trim()
        : (targetInput ? targetInput.value.trim() : '');

      const query = querySource !== '' ? querySource : 'travel';
      openSearch(query);
      if (searchInput) {
        searchInput.focus();
      }
    };

    button.addEventListener('click', (event) => {
      event.preventDefault();
      handleSearch();
    });

    if (searchInput) {
      searchInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
          event.preventDefault();
          handleSearch();
        }
      });
    }
  });
})();
