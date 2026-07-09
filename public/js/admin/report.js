document.addEventListener('DOMContentLoaded', () => {
  if (window.lucide) lucide.createIcons();

  const drawerPanel = document.getElementById('drawerPanel');
  const drawerOverlay = document.getElementById('drawerOverlay');

  function field(label, value) {
    return `<div class="flex items-start justify-between gap-3">
      <span class="text-slate-500 shrink-0">${label}</span>
      <span class="text-slate-800 font-semibold text-right">${value ?? '-'}</span>
    </div>`;
  }

  function slug(str) {
    return (str || '').toString().toLowerCase().trim().replace(/\s+/g, '-');
  }

  function statusBadge(label, value, type) {
    return `<div class="flex items-start justify-between gap-3">
      <span class="text-slate-500">${label}</span>
      <span class="badge ${type}-${slug(value)}">${value ?? '-'}</span>
    </div>`;
  }

  function formatDate(value) {
    if (!value) return '-';
    const d = new Date(value);
    if (isNaN(d)) return value;
    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
  }

  function formatRupiah(value) {
    const num = Number(value);
    if (isNaN(num)) return '-';
    return 'Rp' + num.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
  }

  function fillDrawer(r) {
    document.getElementById('guestInfo').innerHTML =
      field('Guest Name', r.guest_name) +
      field('Phone', r.phone) +
      field('Email', r.email) +
      field('Identity Number', r.identity_number) +
      field('Nationality', r.nationality);

    document.getElementById('reservationInfo').innerHTML =
      field('Reservation Code', r.reservation_code) +
      field('Room Number', r.room?.room_number) +
      field('Room Type', r.room?.roomType?.name) +
      field('Check In', formatDate(r.check_in)) +
      field('Check Out', formatDate(r.check_out)) +
      field('Nights', r.nights ? `${r.nights} Night(s)` : '-') +
      field('Guests', r.guests ? `${r.guests} Guest(s)` : '-') +
      statusBadge('Reservation Status', r.reservation_status, 'status');

    document.getElementById('paymentInfo').innerHTML =
      statusBadge('Payment Status', r.payment_status, 'payment') +
      field('Payment Method', r.payment_method) +
      field('Price per Night', formatRupiah(r.price_per_night)) +
      field('Tax', formatRupiah(r.tax)) +
      field('Discount', formatRupiah(r.discount)) +
      field('Additional Charges', formatRupiah(r.additional_charges)) +
      field('Deposit', formatRupiah(r.deposit)) +
      `<div class="flex items-start justify-between gap-3 pt-2 border-t border-slate-100">
        <span class="text-slate-600 font-bold">Total Amount</span>
        <span class="text-slate-900 font-extrabold">${formatRupiah(r.total_amount)}</span>
      </div>`;

    document.getElementById('otherInfo').innerHTML =
      field('Special Request', r.special_request) +
      field('Notes', r.notes) +
      (r.reservation_status === 'Cancelled' ? field('Cancellation Reason', r.cancellation_reason) : '') +
      field('Actual Check In', r.actual_check_in ? formatDate(r.actual_check_in) : '-') +
      field('Actual Check Out', r.actual_check_out ? formatDate(r.actual_check_out) : '-');

    if (window.lucide) lucide.createIcons();
    showDrawer();
  }

  function loadDetail(url) {
    fetch(url, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'application/json',
      },
    })
      .then((res) => {
        if (!res.ok) throw new Error('Failed to fetch reservation detail');
        return res.json();
      })
      .then((data) => fillDrawer(data))
      .catch(() => toast('Failed to load reservation detail'));
  }

  function showDrawer() {
    drawerPanel.classList.remove('translate-x-full');
    drawerOverlay.classList.remove('opacity-0', 'pointer-events-none');
    document.body.style.overflow = 'hidden';
  }

  function closeDrawer() {
    drawerPanel.classList.add('translate-x-full');
    drawerOverlay.classList.add('opacity-0', 'pointer-events-none');
    document.body.style.overflow = '';
  }

  document.querySelectorAll('.btn-detail').forEach((btn) => {
    btn.addEventListener('click', () => loadDetail(btn.dataset.detailUrl));
  });

  drawerOverlay?.addEventListener('click', closeDrawer);
  document.getElementById('drawerCloseBtn')?.addEventListener('click', closeDrawer);
  document.getElementById('drawerCloseBtnBottom')?.addEventListener('click', closeDrawer);

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeDrawer();
  });

  let toastTimer;
  window.toast = function (msg) {
    const box = document.getElementById('toastBox');
    const msgEl = document.getElementById('toastMsg');
    if (!box || !msgEl) return;
    msgEl.textContent = msg;
    box.classList.remove('opacity-0', 'pointer-events-none');
    box.classList.add('opacity-100');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
      box.classList.remove('opacity-100');
      box.classList.add('opacity-0', 'pointer-events-none');
    }, 2200);
  };
});
