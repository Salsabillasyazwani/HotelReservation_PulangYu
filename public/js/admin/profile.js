/* =========================================================
   My Profile — Content-only JS
   Tidak ada dummy data / fake save. Form profil submit
   beneran ke backend (route profile.update). JS di sini
   cuma handle interaksi UI:
   - dropdown foto (pilih/hapus), dengan preview
   - dropdown status
   - validasi ringan sebelum submit
   ========================================================= */

document.addEventListener('DOMContentLoaded', function () {

  // Guard: kalau file ini ke-load dua kali (misal ter-include di layout
  // DAN di halaman), event listener jangan dipasang dua kali —
  // itu penyebab dropdown "muncul lalu langsung ilang" (toggle 2x).
  if (document.body.dataset.profileJsLoaded === '1') {
    console.warn('profile.js sudah pernah dijalankan sebelumnya — cek apakah file ini ke-include dua kali di Blade.');
    return;
  }
  document.body.dataset.profileJsLoaded = '1';

  const photoMenuBtn   = document.getElementById('photoMenuBtn');
  const photoMenu      = document.getElementById('photoMenu');
  const choosePhotoBtn = document.getElementById('choosePhotoBtn');
  const removePhotoBtn = document.getElementById('removePhotoBtn');

  const avatarImage  = document.getElementById('avatarImage');
  const avatarLetter = document.getElementById('avatarLetter');

  const fullName    = document.getElementById('fullName');
  const displayName = document.getElementById('displayName');

  const statusBtn   = document.getElementById('statusBtn');
  const statusMenu  = document.getElementById('statusMenu');
  const statusText  = document.getElementById('statusText');
  const statusDot   = document.getElementById('statusDot');
  const statusInput = document.getElementById('statusInput');

  const profileForm = document.getElementById('profileForm');
  const toast = document.getElementById('toast');

  // ---------------------------------------------------------
  // Toast
  // ---------------------------------------------------------
  function showToast(msg) {
    toast.textContent = msg;
    toast.classList.remove('hidden');
    clearTimeout(window.toastTimer);
    window.toastTimer = setTimeout(() => toast.classList.add('hidden'), 2200);
  }

  // ---------------------------------------------------------
  // Photo upload (file input & remove-flag dibuat dinamis
  // dan disisipkan ke dalam #profileForm supaya ikut ter-submit)
  // ---------------------------------------------------------
  const photoInput = document.createElement('input');
  photoInput.type = 'file';
  photoInput.name = 'photo';
  photoInput.accept = 'image/*';
  photoInput.hidden = true;
  profileForm.appendChild(photoInput);

  const removePhotoFlag = document.createElement('input');
  removePhotoFlag.type = 'hidden';
  removePhotoFlag.name = 'remove_photo';
  removePhotoFlag.value = '0';
  profileForm.appendChild(removePhotoFlag);

  function openMenu(menu) {
    menu.classList.remove('hidden');
  }
  function closeMenu(menu) {
    menu.classList.add('hidden');
  }
  function isOpen(menu) {
    return !menu.classList.contains('hidden');
  }

  // ---------------------------------------------------------
  // Dropdown foto & status
  // Sekarang pakai pola yang lebih tahan tabrakan: listener penutup
  // dipasang di document dengan capture:true (jadi selalu jalan lebih
  // dulu, apapun urutan listener lain di document), dan syaratnya cuma
  // "klik di luar area menu/tombolnya".
  // ---------------------------------------------------------
  photoMenuBtn.addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();

    const willOpen = !isOpen(photoMenu);
    closeMenu(statusMenu);
    willOpen ? openMenu(photoMenu) : closeMenu(photoMenu);
  });

  choosePhotoBtn.addEventListener('click', () => photoInput.click());

  photoInput.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (!file) return;
    removePhotoFlag.value = '0';
    const reader = new FileReader();
    reader.onload = function (ev) {
      avatarImage.src = ev.target.result;
      avatarImage.classList.remove('hidden');
      avatarLetter.classList.add('hidden');
      closeMenu(photoMenu);
      showToast('Foto siap disimpan — klik Save Changes');
    };
    reader.readAsDataURL(file);
  });

  removePhotoBtn.addEventListener('click', () => {
    avatarImage.src = '';
    avatarImage.classList.add('hidden');
    avatarLetter.classList.remove('hidden');
    photoInput.value = '';
    removePhotoFlag.value = '1';
    closeMenu(photoMenu);
    showToast('Foto akan dihapus — klik Save Changes');
  });

  // ---------------------------------------------------------
  // Live update display name & avatar initial
  // ---------------------------------------------------------
  fullName.addEventListener('input', () => {
    const name = fullName.value.trim();
    displayName.textContent = name || displayName.textContent;
    if (avatarImage.classList.contains('hidden')) {
      avatarLetter.textContent = (name[0] || avatarLetter.textContent || 'A').toUpperCase();
    }
  });

  // ---------------------------------------------------------
  // Status dropdown
  // ---------------------------------------------------------
  statusBtn.addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();

    const willOpen = !isOpen(statusMenu);
    closeMenu(photoMenu);
    willOpen ? openMenu(statusMenu) : closeMenu(statusMenu);
  });

  function applyStatus(status) {
    statusText.textContent = status;
    statusInput.value = status;

    statusDot.className = 'badge-dot';
    if (status === 'Active') {
      statusDot.classList.add('dot-active');
    } else if (status === 'Inactive') {
      statusDot.classList.add('dot-inactive');
    } else {
      statusDot.classList.add('dot-suspended');
    }
  }

  document.querySelectorAll('.status-option').forEach(btn => {
    btn.addEventListener('click', () => {
      applyStatus(btn.dataset.status);
      closeMenu(statusMenu);
    });
  });

  // Klik di dalam menu jangan ikut nutup menu itu sendiri
  [photoMenu, statusMenu].forEach(el => {
    el.addEventListener('click', e => e.stopPropagation());
  });

  // Listener tunggal buat nutup dropdown kalau klik di luar.
  // capture:true supaya ini jalan duluan sebelum listener 'click' lain
  // di document (misalnya punya admin.js) sempat mengubah apa pun.
  document.addEventListener('click', function (e) {
    if (
      !photoMenu.contains(e.target) &&
      e.target !== photoMenuBtn &&
      !photoMenuBtn.contains(e.target)
    ) {
      closeMenu(photoMenu);
    }

    if (
      !statusMenu.contains(e.target) &&
      e.target !== statusBtn &&
      !statusBtn.contains(e.target)
    ) {
      closeMenu(statusMenu);
    }
    }, true);

    const profileArea = document.querySelector('.profile-grid');

    if (profileArea) {
        profileArea.addEventListener('click', function(e){
            e.stopPropagation();
        });
    }

  const validators = {
    fullName: v => v.trim().length > 1,
    username: v => v.trim().length > 0,
    email: v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v),
    phone: v => (v.match(/\d/g) || []).length >= 10
  };

  function validateField(input) {
    const wrap = input.parentElement;
    const error = wrap.querySelector('.error-text');
    let valid = true;
    if (input.id === 'fullName') valid = validators.fullName(input.value);
    if (input.id === 'username') valid = validators.username(input.value);
    if (input.id === 'email') valid = validators.email(input.value);
    if (input.id === 'phone') valid = validators.phone(input.value);

    input.classList.toggle('border-red-300', !valid);
    input.classList.toggle('bg-red-50', !valid);
    error?.classList.toggle('hidden', valid);
    return valid;
  }

  ['fullName', 'username', 'email', 'phone'].forEach(id => {
    const el = document.getElementById(id);
    el.addEventListener('input', e => validateField(e.target));
    el.addEventListener('blur', e => validateField(e.target));
  });

  profileForm.addEventListener('submit', (e) => {
    const inputs = ['fullName', 'username', 'email', 'phone'].map(id => document.getElementById(id));
    const allValid = inputs.every(validateField);
    if (!allValid) {
      e.preventDefault();
      showToast('Periksa kembali data yang diisi');
    }
    // kalau valid, form lanjut submit beneran ke route profile.update
  });

});
