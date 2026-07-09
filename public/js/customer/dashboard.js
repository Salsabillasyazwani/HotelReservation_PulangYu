document.getElementById('datePickerToggle')?.addEventListener('click', function () {
    document.getElementById('datePickerPanel')?.classList.toggle('open');
});

document.addEventListener('click', function (e) {
    const toggle = document.getElementById('datePickerToggle');
    const panel = document.getElementById('datePickerPanel');
    if (panel && toggle && !toggle.contains(e.target)) {
        panel.classList.remove('open');
    }
});

function showToast(title, message, type = 'success') {
    const toast = document.getElementById('toast');
    const icon = document.getElementById('toastIcon');
    const toastTitle = document.getElementById('toastTitle');
    const toastMessage = document.getElementById('toastMessage');
    if (!toast) return;

    toastTitle.textContent = title;
    toastMessage.textContent = message;

    const map = {
        success: { bg: 'rgba(16,185,129,0.2)', color: '#34d399', text: '✓' },
        info: { bg: 'rgba(59,130,246,0.2)', color: '#60a5fa', text: 'i' },
        warning: { bg: 'rgba(245,158,11,0.2)', color: '#fbbf24', text: '!' },
        error: { bg: 'rgba(239,68,68,0.2)', color: '#f87171', text: '!' },
    };
    const conf = map[type] || map.success;
    icon.style.background = conf.bg;
    icon.style.color = conf.color;
    icon.textContent = conf.text;

    toast.classList.add('show');
    clearTimeout(window.__toastTimer);
    window.__toastTimer = setTimeout(() => toast.classList.remove('show'), 3000);
}

function bookNow(button) {
    const roomId = button.dataset.roomId;
    const roomName = button.dataset.roomName;
    const price = parseFloat(button.dataset.roomPrice || 0);

    document.getElementById('modalRoomId').value = roomId;
    document.getElementById('modalRoomName').textContent = roomName;
    document.getElementById('modalPrice').textContent = formatRupiah(price);

    window.__selectedRoomPrice = price;
    recalculateTotal();

    const modal = document.getElementById('bookingModal');
    const content = document.getElementById('bookingModalContent');
    modal.classList.remove('hidden');
    modal.classList.add('open');
    requestAnimationFrame(() => content.classList.add('open'));
}

function closeBookingModal() {
    const modal = document.getElementById('bookingModal');
    const content = document.getElementById('bookingModalContent');
    content.classList.remove('open');
    setTimeout(() => {
        modal.classList.remove('open');
        modal.classList.add('hidden');
    }, 250);
}

function formatRupiah(value) {
    return 'Rp ' + Number(value || 0).toLocaleString('id-ID');
}

function recalculateTotal() {
    const form = document.getElementById('bookingForm');
    if (!form) return;

    const checkIn = form.querySelector('[name="check_in"]')?.value;
    const checkOut = form.querySelector('[name="check_out"]')?.value;
    const pricePerNight = window.__selectedRoomPrice || 0;

    let nights = 1;
    if (checkIn && checkOut) {
        const diff = (new Date(checkOut) - new Date(checkIn)) / (1000 * 60 * 60 * 24);
        nights = diff > 0 ? diff : 1;
    }

    const total = pricePerNight * nights;
    const priceEl = document.getElementById('modalPrice');
    if (priceEl) priceEl.textContent = formatRupiah(total);
}

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('bookingForm');
    form?.querySelector('[name="check_in"]')?.addEventListener('change', recalculateTotal);
    form?.querySelector('[name="check_out"]')?.addEventListener('change', recalculateTotal);
});

function toggleFavorite(btn, e) {
    e.stopPropagation();
    const roomId = btn.dataset.roomId;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    if (!roomId) {
        console.warn('toggleFavorite: room-id tidak ditemukan pada tombol.');
        return;
    }

    const wasActive = btn.classList.contains('active');
    btn.classList.toggle('active');
    btn.disabled = true;

    fetch(`/customer/wishlist/${roomId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken || '',
            'Accept': 'application/json',
        },
    })
        .then((res) => {
            if (!res.ok) throw new Error('Request gagal');
            return res.json();
        })
        .then((data) => {
            const isActive = data.wishlisted ?? !wasActive;
            btn.classList.toggle('active', isActive);
            if (isActive) {
                showToast('Saved to Wishlist', 'Room added to your favorites', 'info');
            } else {
                showToast('Removed from Wishlist', 'Room removed from favorites', 'info');
            }
        })
        .catch(() => {
            btn.classList.toggle('active', wasActive);
            showToast('Failed', 'Could not update wishlist, try again', 'error');
        })
        .finally(() => {
            btn.disabled = false;
        });
}

function usePromo(promoCode) {
    window.location.href = `/customer/rooms?promo=${encodeURIComponent(promoCode)}`;
}

function scrollRooms(direction) {
    const grid = document.getElementById('roomsGrid');
    if (!grid) return;
    grid.scrollBy({ left: direction * 300, behavior: 'smooth' });
}

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closeBookingModal();
        document.getElementById('datePickerPanel')?.classList.remove('open');
    }
});
