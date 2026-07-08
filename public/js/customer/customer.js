/*loader + smooth page transition*/
(function () {
  const loader = document.getElementById('pageLoader');
  if (!loader) return;

  const showLoader = () => loader.classList.remove('hide');
  const hideLoader = () => loader.classList.add('hide');

  /* fade the loader out once the new page has actually finished rendering */
  window.addEventListener('load', () => {
    setTimeout(hideLoader, 350);
  });

  /* if the page is restored from bfcache (browser back/forward), make sure
     it isn't stuck showing the loader */
  window.addEventListener('pageshow', (e) => {
    if (e.persisted) hideLoader();
  });

  /* intercept normal internal link clicks: fade the loader back in first,
     THEN navigate — so moving between pages looks like a smooth cross-fade
     instead of the browser's raw white-flash reload */
  document.addEventListener('click', (e) => {
    if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

    const link = e.target.closest('a[href]');
    if (!link) return;
    if (link.target && link.target !== '_self') return;
    if (link.hasAttribute('download') || link.hasAttribute('data-export') || link.hasAttribute('data-no-transition')) return;

    const href = link.getAttribute('href') || '';
    if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:')) return;
    if (link.origin !== window.location.origin) return;

    e.preventDefault();
    showLoader();
    setTimeout(() => { window.location.href = link.href; }, 180);
  });
})();

/*dark mode persistence*/
(function initDarkModePreference(){
  try {
    if (localStorage.getItem('hpy-dark-mode') === '1') {
      document.body.classList.add('dark');
    }
  } catch (e) { /* localStorage unavailable, ignore */ }
})();

/*highlight row dari global search*/
document.addEventListener('DOMContentLoaded', function () {
  const highlightId = new URLSearchParams(window.location.search).get('highlight');
  if (!highlightId) return;

  const target = document.querySelector(
    `[data-id="${highlightId}"], [data-facility-id="${highlightId}"], [data-room-id="${highlightId}"], [data-promotion-id="${highlightId}"]`
  );
  if (!target) return;

  setTimeout(() => {
    target.scrollIntoView({ behavior: 'smooth', block: 'center' });
    target.classList.add('search-highlight');
    setTimeout(() => target.classList.remove('search-highlight'), 2600);
  }, 300);
});

/*sidebar toggle*/
const sidebar = document.getElementById('sidebar');
const mainWrap = document.getElementById('main-wrap');
const overlay = document.getElementById('overlay');
let isMobile = window.innerWidth <= 900;

function toggleSidebar(){
  if(window.innerWidth <= 900){
    sidebar.classList.toggle('mobile-open');
    overlay.classList.toggle('show');
  } else {
    sidebar.classList.toggle('collapsed');
    mainWrap.classList.toggle('collapsed');
  }
}
function closeMobileSidebar(){
  sidebar.classList.remove('mobile-open');
  overlay.classList.remove('show');
}
window.addEventListener('resize', ()=>{
  const nowMobile = window.innerWidth <= 900;
  if(nowMobile !== isMobile){
    isMobile = nowMobile;
    sidebar.classList.remove('collapsed','mobile-open');
    mainWrap.classList.remove('collapsed');
    overlay.classList.remove('show');
  }
});
if(window.innerWidth <= 1200 && window.innerWidth > 900){
  sidebar.classList.add('collapsed');
  mainWrap.classList.add('collapsed');
}

/*submenu accordion*/
document.querySelectorAll('[data-toggle]').forEach(item=>{
  item.addEventListener('click', ()=>{
    const key = item.getAttribute('data-toggle');
    const sub = document.getElementById('sub-'+key);
    const isOpen = sub.classList.contains('open');
    document.querySelectorAll('.submenu').forEach(s=>s.classList.remove('open'));
    document.querySelectorAll('[data-toggle]').forEach(n=>n.classList.remove('open'));
    if(!isOpen){
      sub.classList.add('open');
      item.classList.add('open');
    }
    if(sidebar.classList.contains('collapsed')){
      sidebar.classList.remove('collapsed');
      mainWrap.classList.remove('collapsed');
    }
  });
});

/*nav active state / page switch*/
document.querySelectorAll('.nav-link[data-page]').forEach(item=>{
  item.addEventListener('click', ()=>{
    document.querySelectorAll('.nav-link').forEach(n=>n.classList.remove('active'));
    item.classList.add('active');
    const page = item.getAttribute('data-page');
    const title = item.getAttribute('data-title');
    if(page === 'dashboard'){
      goDashboard();
    } else {
      showPlaceholder(title);
    }
    if(window.innerWidth<=900) closeMobileSidebar();
  });
});

function showPlaceholder(title){
  const dash = document.getElementById('page-dashboard');
  const ph = document.getElementById('page-placeholder');
  if(!dash || !ph) return;
  dash.classList.add('hidden');
  ph.classList.remove('hidden');
  document.getElementById('placeholderTitle').innerText = title;
  document.getElementById('placeholderName').innerText = title;
}
function goDashboard(){
  document.querySelectorAll('.nav-link').forEach(n=>n.classList.remove('active'));
  const dashLink = document.querySelector('[data-page="dashboard"]');
  if(dashLink) dashLink.classList.add('active');
  const dash = document.getElementById('page-dashboard');
  const ph = document.getElementById('page-placeholder');
  if(!dash || !ph) return;
  ph.classList.add('hidden');
  dash.classList.remove('hidden');
}
function placeholderNav(el, title){
  document.querySelectorAll('.nav-link').forEach(n=>n.classList.remove('active'));
  showPlaceholder(title);
  closeAllDropdowns();
  return false;
}

/*dropdowns (navbar: notif, msg, profile)*/
const allDropdownIds = ['notifDrop','msgDrop','profileDrop','dateDrop','resPeriodDrop','revPeriodDrop'];
function toggleDrop(id){
  const el = document.getElementById(id);
  if(!el) return;
  const willOpen = !el.classList.contains('show');
  closeAllDropdowns();
  if(willOpen) el.classList.add('show');
}
function closeAllDropdowns(){
  allDropdownIds.forEach(id=>{
    const el = document.getElementById(id);
    if(el) el.classList.remove('show');
  });
  document.querySelectorAll('.action-drop').forEach(el=>el.classList.remove('show'));
}
document.addEventListener('click', (e)=>{
  if(!e.target.closest('.relative')) closeAllDropdowns();
});

/*notification / message badge clear on open*/
const notifDropEl = document.getElementById('notifDrop');
if(notifDropEl){
  notifDropEl.addEventListener('transitionend', ()=>{
    if(notifDropEl.classList.contains('show')){
      document.getElementById('notifBadge').style.display='none';
    }
  });
}
const msgDropEl = document.getElementById('msgDrop');
if(msgDropEl){
  msgDropEl.addEventListener('transitionend', ()=>{
    if(msgDropEl.classList.contains('show')){
      document.getElementById('msgBadge').style.display='none';
    }
  });
}

/*dark mode*/
function toggleDark(){
  document.body.classList.toggle('dark');
  const isDark = document.body.classList.contains('dark');
  try { localStorage.setItem('hpy-dark-mode', isDark ? '1' : '0'); } catch (e) { /* ignore */ }
  const icon = document.getElementById('darkIcon');
  if(isDark){
    icon.classList.remove('bi-moon-stars'); icon.classList.add('bi-sun-fill');
  } else {
    icon.classList.remove('bi-sun-fill'); icon.classList.add('bi-moon-stars');
  }
  if(typeof refreshChartColors === 'function') refreshChartColors();
}

/*sinkronkan ikon bulan/matahari saat halaman baru dimuat*/
document.addEventListener('DOMContentLoaded', function () {
  if (document.body.classList.contains('dark')) {
    const icon = document.getElementById('darkIcon');
    if (icon) { icon.classList.remove('bi-moon-stars'); icon.classList.add('bi-sun-fill'); }
  }
});

/*ripple effect*/
document.querySelectorAll('.btn-ripple').forEach(btn=>{
  btn.addEventListener('click', function(e){
    const rect = this.getBoundingClientRect();
    const ripple = document.createElement('span');
    const size = Math.max(rect.width, rect.height);
    ripple.className='ripple';
    ripple.style.width = ripple.style.height = size+'px';
    ripple.style.left = (e.clientX-rect.left-size/2)+'px';
    ripple.style.top = (e.clientY-rect.top-size/2)+'px';
    this.appendChild(ripple);
    setTimeout(()=>ripple.remove(),650);
  });
});

/*search shortcut*/
document.addEventListener('keydown',(e)=>{
  if(e.ctrlKey && e.key==='/'){
    e.preventDefault();
    const box = document.getElementById('searchBox');
    if(box) box.focus();
  }
  if(e.key === 'Escape'){
    closeAllDropdowns();
    if(searchResults) searchResults.classList.remove('show');
    if(typeof closeModal === 'function') closeModal();
  }
});

/*global search*/
const searchBox = document.getElementById('searchBox');
const searchResults = document.getElementById('searchResults');
let searchTimeout;

if (searchBox && searchResults) {
  searchBox.addEventListener('input', function () {
    clearTimeout(searchTimeout);
    const query = this.value.trim();

    if (query.length < 2) {
      searchResults.innerHTML = '';
      searchResults.classList.remove('show');
      return;
    }

    searchTimeout = setTimeout(() => {
      fetch(`/admin/search?q=${encodeURIComponent(query)}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
        .then(res => res.json())
        .then(data => renderSearchResults(data))
        .catch(err => console.error('Search error:', err));
    }, 300);
  });

  document.addEventListener('click', (e) => {
    if (!e.target.closest('#globalSearchBox')) {
      searchResults.classList.remove('show');
    }
  });
}

function renderSearchResults(data) {
  if (!data.length) {
    searchResults.innerHTML = '<div class="search-empty px-3 py-2 text-sm text-gray-400">Tidak ditemukan</div>';
    searchResults.classList.add('show');
    return;
  }

  searchResults.innerHTML = data.map(item => `
    <a href="${item.url}" class="search-result-item block px-3 py-2 hover:bg-slate-100 rounded-lg">
      <span class="text-xs text-gray-400 uppercase mr-2">${item.type}</span>
      <span class="text-sm font-medium">${item.label}</span>
    </a>
  `).join('');
  searchResults.classList.add('show');
}

/*logout*/
function logoutAction(){
  if(confirm('Yakin ingin logout dari Hotel Pulang Yo Admin?')){
    document.getElementById('pageLoader').classList.remove('hide');
    setTimeout(()=>{ alert('Anda telah logout. (Demo)'); document.getElementById('pageLoader').classList.add('hide'); },800);
  }
}
