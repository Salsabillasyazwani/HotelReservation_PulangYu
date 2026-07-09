document.addEventListener('DOMContentLoaded', function () {

  if (document.body.dataset.profileJsLoaded === '1') {
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

  function showToast(msg) {
    toast.textContent = msg;
    toast.classList.remove('hidden');
    clearTimeout(window.toastTimer);
    window.toastTimer = setTimeout(() => toast.classList.add('hidden'), 2200);
  }

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

  fullName.addEventListener('input', () => {
    const name = fullName.value.trim();
    displayName.textContent = name || displayName.textContent;
    if (avatarImage.classList.contains('hidden')) {
      avatarLetter.textContent = (name[0] || avatarLetter.textContent || 'A').toUpperCase();
    }
  });

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

  [photoMenu, statusMenu].forEach(el => {
    el.addEventListener('click', e => e.stopPropagation());
  });

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
  });

});
