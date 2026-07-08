(function () {
  const searchBox = document.getElementById('searchBox');
  const searchResults = document.getElementById('searchResults');
  const searchWrap = document.getElementById('globalSearchBox');

  if (!searchBox || !searchResults) return;

  const ICONS = {
    Room: 'bi-door-closed-fill',
    'Room Type': 'bi-grid-fill',
    Reservation: 'bi-journal-check',
    Promotion: 'bi-tag-fill',
    Facility: 'bi-stars',
  };

  let debounceTimer = null;
  let currentResults = [];
  let activeIndex = -1;
  let currentController = null;

  function closeDropdown() {
    searchResults.classList.remove('show');
    searchResults.innerHTML = '';
    activeIndex = -1;
    currentResults = [];
  }

  function openDropdown() {
    searchResults.classList.add('show');
  }

  function renderLoading() {
    searchResults.innerHTML = '<div class="search-state">Searching...</div>';
    openDropdown();
  }

  function renderError() {
    searchResults.innerHTML = '<div class="search-state search-state-error">Failed to search. Please try again.</div>';
    openDropdown();
  }

  function renderEmpty() {
    searchResults.innerHTML = '<div class="search-state">No results found</div>';
    openDropdown();
  }

  function renderResults(results) {
    currentResults = results;
    activeIndex = -1;

    if (!results.length) {
      renderEmpty();
      return;
    }

    // Group hasil per tipe modul supaya rapi & mudah dipindai.
    const groups = {};
    results.forEach((r, idx) => {
      r.__idx = idx;
      if (!groups[r.type]) groups[r.type] = [];
      groups[r.type].push(r);
    });

    let html = '';
    Object.keys(groups).forEach((type) => {
      html += `<div class="result-group"><small>${escapeHtml(type)}</small>`;
      groups[type].forEach((r) => {
        html += `
          <a href="${escapeHtml(r.url)}" class="search-result-item" data-idx="${r.__idx}">
            <i class="bi ${ICONS[r.type] || 'bi-search'}"></i>
            <span class="search-result-text">
              <span class="search-result-title">${escapeHtml(r.title)}</span>
              <span class="search-result-subtitle">${escapeHtml(r.subtitle || '')}</span>
            </span>
          </a>`;
      });
      html += `</div>`;
    });

    searchResults.innerHTML = html;
    openDropdown();
  }

  function escapeHtml(str) {
    return String(str ?? '').replace(/[&<>"']/g, (c) => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[c]));
  }

  function updateActiveHighlight() {
    searchResults.querySelectorAll('.search-result-item').forEach((el) => {
      el.classList.toggle('active', Number(el.dataset.idx) === activeIndex);
    });
    const activeEl = searchResults.querySelector('.search-result-item.active');
    if (activeEl) activeEl.scrollIntoView({ block: 'nearest' });
  }

  function navigateTo(result) {
    if (!result) return;
    window.location.href = result.url;
  }

  function performSearch(query) {
    if (currentController) currentController.abort();
    currentController = new AbortController();

    renderLoading();

    fetch(`/admin/search?q=${encodeURIComponent(query)}`, {
      headers: { 'Accept': 'application/json' },
      signal: currentController.signal,
    })
      .then((res) => {
        if (!res.ok) throw new Error('Request failed');
        return res.json();
      })
      .then((data) => renderResults(data.results || []))
      .catch((err) => {
        if (err.name === 'AbortError') return;
        console.error('Global search error:', err);
        renderError();
      });
  }

  searchBox.addEventListener('input', function () {
    clearTimeout(debounceTimer);
    const query = this.value.trim();

    if (query.length < 2) {
      closeDropdown();
      return;
    }

    debounceTimer = setTimeout(() => performSearch(query), 300);
  });

  searchBox.addEventListener('focus', function () {
    if (this.value.trim().length >= 2 && currentResults.length) openDropdown();
  });

  searchBox.addEventListener('keydown', function (e) {
    const items = currentResults;
    if (!items.length && e.key !== 'Escape') return;

    if (e.key === 'ArrowDown') {
      e.preventDefault();
      activeIndex = Math.min(activeIndex + 1, items.length - 1);
      updateActiveHighlight();
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      activeIndex = Math.max(activeIndex - 1, 0);
      updateActiveHighlight();
    } else if (e.key === 'Enter') {
      e.preventDefault();
      const chosen = activeIndex >= 0 ? items[activeIndex] : items[0];
      navigateTo(chosen);
    } else if (e.key === 'Escape') {
      closeDropdown();
      searchBox.blur();
    }
  });

  // Klik pada salah satu hasil.
  searchResults.addEventListener('click', function (e) {
    const item = e.target.closest('.search-result-item');
    if (!item) return;
    e.preventDefault();
    const idx = Number(item.dataset.idx);
    navigateTo(currentResults[idx]);
  });

  // Klik di luar dropdown → tutup.
  document.addEventListener('click', function (e) {
    if (searchWrap && !searchWrap.contains(e.target)) {
      closeDropdown();
    }
  });

  // Shortcut Ctrl+/ (atau Cmd+/) untuk langsung fokus ke Global Search, sesuai
  // hint "Ctrl /" yang ditampilkan di sebelah input.
  document.addEventListener('keydown', function (e) {
    if ((e.ctrlKey || e.metaKey) && e.key === '/') {
      e.preventDefault();
      searchBox.focus();
      searchBox.select();
    }
  });
})();
