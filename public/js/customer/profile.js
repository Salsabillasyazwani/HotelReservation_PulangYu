document.addEventListener('DOMContentLoaded', function () {

    if (document.body.dataset.customerProfileJsLoaded === '1') {
        console.warn('profile.js (customer) sudah pernah dijalankan sebelumnya — cek apakah file ini ke-include dua kali di Blade.');
        return;
    }
    document.body.dataset.customerProfileJsLoaded = '1';

    const serverFlash = document.getElementById('serverFlash');
    if (serverFlash) {
        showToast(serverFlash.dataset.message, serverFlash.dataset.type);
    }

    const photoMenuBtn   = document.getElementById('photoMenuBtn');
    const photoMenu      = document.getElementById('photoMenu');
    const choosePhotoBtn = document.getElementById('choosePhotoBtn');
    const removePhotoBtn = document.getElementById('removePhotoBtn');
    const photoInput     = document.getElementById('photoInput');

    if (photoMenuBtn && photoMenu) {
        photoMenuBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            photoMenu.classList.toggle('hidden');
        });

        photoMenu.addEventListener('click', (e) => e.stopPropagation());

        document.addEventListener('click', (e) => {
            if (
                !photoMenu.contains(e.target) &&
                e.target !== photoMenuBtn &&
                !photoMenuBtn.contains(e.target)
            ) {
                photoMenu.classList.add('hidden');
            }
        }, true);
    }

    if (choosePhotoBtn && photoInput) {
        choosePhotoBtn.addEventListener('click', () => {
            photoInput.click();
            photoMenu.classList.add('hidden');
        });
    }

    if (removePhotoBtn) {
        removePhotoBtn.addEventListener('click', () => {
            photoMenu.classList.add('hidden');
            const form = document.getElementById('avatarDeleteForm');
            if (form && confirm('Hapus foto profil?')) {
                form.submit();
            }
        });
    }

    const editName = document.getElementById('editName');
    const displayName = document.getElementById('displayName');
    if (editName && displayName) {
        editName.addEventListener('input', () => {
            displayName.textContent = editName.value.trim() || displayName.textContent;
        });
    }
});

function validatePasswordForm(e) {
    const form = e.target;
    const newPassword = form.querySelector('[name="new_password"]').value;
    const confirmPassword = form.querySelector('[name="new_password_confirmation"]').value;

    if (newPassword.length < 8) {
        e.preventDefault();
        showToast('Password baru minimal 8 karakter!', 'error');
        return false;
    }

    if (newPassword !== confirmPassword) {
        e.preventDefault();
        showToast('Password baru dan konfirmasi tidak sama!', 'error');
        return false;
    }

    return true;
}

function checkPasswordStrength(password) {
    let strength = 0;
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    if (/\d/.test(password)) strength++;
    if (/[^a-zA-Z\d]/.test(password)) strength++;

    const colors = ['#ef4444', '#f97316', '#eab308', '#22c55e'];
    const texts = ['Weak', 'Fair', 'Good', 'Strong'];

    for (let i = 1; i <= 4; i++) {
        const bar = document.getElementById('str' + i);
        if (bar) {
            bar.style.backgroundColor = (i <= strength && password.length > 0)
                ? colors[strength - 1]
                : '#e2e8f0';
        }
    }

    const strengthText = document.getElementById('strengthText');
    if (strengthText) {
        strengthText.textContent = password.length > 0 ? (texts[strength - 1] || 'Too weak') : 'Password strength';
        strengthText.style.color = password.length > 0 ? colors[Math.max(0, strength - 1)] : '#64748b';
    }
}

function togglePassword(inputName) {
    const input = document.querySelector(`[name="${inputName}"]`);
    if (input) {
        input.type = input.type === 'password' ? 'text' : 'password';
    }
}

function previewPhoto(event) {
    const file = event.target.files[0];
    if (!file) return;

    if (file.size > 2 * 1024 * 1024) {
        showToast('File size must be less than 2MB!', 'error');
        event.target.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = function (e) {
        const preview = document.getElementById('profileAvatar');
        if (preview) preview.src = e.target.result;
    };
    reader.readAsDataURL(file);

    document.getElementById('avatarUploadForm').submit();
}

function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    if (!toast) return;

    toast.textContent = message;
    toast.style.background = type === 'error' ? '#dc2626' : '#0f172a';
    toast.classList.remove('hidden');

    clearTimeout(window.toastTimer);
    window.toastTimer = setTimeout(() => toast.classList.add('hidden'), 2600);
}
