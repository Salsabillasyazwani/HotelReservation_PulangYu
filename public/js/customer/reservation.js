(function () {
    'use strict';

    const rupiah = (n) => 'Rp ' + Number(n).toLocaleString('id-ID');

    let currentFilter = 'all';

    function row(label, val, cls = '') {
        return `<div class="flex justify-between text-sm"><span class="text-slate-500">${label}</span><span class="font-medium text-navy ${cls}">${val}</span></div>`;
    }

    function applyFilterAndSearch() {
        const search = (document.getElementById('searchInput')?.value || '').toLowerCase();
        const rows = document.querySelectorAll('.reservation-row');
        let visibleCount = 0;

        rows.forEach((tr) => {
            const status = tr.dataset.status;
            const code = (tr.dataset.code || '').toLowerCase();
            const matchStatus = currentFilter === 'all' || status === currentFilter;
            const matchSearch = code.includes(search);
            const show = matchStatus && matchSearch;
            tr.classList.toggle('is-hidden', !show);
            if (show) visibleCount++;
        });

        const emptyState = document.getElementById('emptyState');
        if (emptyState) emptyState.classList.toggle('hidden', visibleCount > 0);
    }

    function setFilter(btn) {
        currentFilter = btn.dataset.status;
        document.querySelectorAll('.filter-btn').forEach((b) => b.classList.remove('active'));
        btn.classList.add('active');
        applyFilterAndSearch();
    }

    function openDetail(tr) {
        const data = JSON.parse(tr.dataset.detail);
        const detailCode = document.getElementById('detailCode');
        const detailBody = document.getElementById('detailBody');
        if (detailCode) detailCode.textContent = '#' + data.code;

        if (detailBody) {
            detailBody.innerHTML = `
                <div class="flex items-center justify-between">
                    <span class="status-badge status-${data.status} text-xs font-semibold px-3 py-1 rounded-full">${data.status_label}</span>
                    <span class="text-xs text-slate-400">Payment: ${data.payment_status}</span>
                </div>
                ${row('Room Name', data.room)}
                ${row('Room Type', data.type)}
                ${row('Room Number', data.number)}
                ${row('Guests', data.guests)}
                <div class="border-t border-slate-100 my-2"></div>
                ${row('Check In', data.checkin)}
                ${row('Check Out', data.checkout)}
                ${row('Total Nights', data.nights + ' night' + (data.nights !== 1 ? 's' : ''))}
                ${row('Price Per Night', rupiah(data.price_per_night))}
                <div class="border-t border-slate-100 my-2"></div>
                ${row('Promotion Used', data.promo === '-' ? 'None' : data.promo)}
                ${row('Discount', '- ' + rupiah(data.discount), 'text-green-600')}
                ${row('Tax', rupiah(data.tax))}
                ${data.special_request ? row('Special Request', data.special_request) : ''}
                ${data.cancellation_reason ? row('Cancellation Reason', data.cancellation_reason, 'text-red-500') : ''}
                <div class="bg-navy text-white rounded-xl p-4 flex justify-between items-center mt-3">
                    <span class="text-sm">Total Payment</span>
                    <span class="text-xl font-bold text-gold">${rupiah(data.total)}</span>
                </div>`;
        }

        showModal('detailModal');
    }

    function showModal(id) {
        const m = document.getElementById(id);
        if (!m) return;
        m.classList.remove('hidden');
        m.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function hideModal(id) {
        const m = document.getElementById(id);
        if (!m) return;
        m.classList.add('hidden');
        m.classList.remove('flex');
        document.body.style.overflow = '';
    }

    function handleCancelSubmit(form) {
        const code = form.querySelector('.btn-cancel')?.dataset.code || '';

        Swal.fire({
            title: 'Cancel Reservation?',
            text: `Reservation #${code} will be cancelled.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#0B1739',
            confirmButtonText: 'Yes, cancel it',
        }).then((res) => {
            if (!res.isConfirmed) return;

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json',
                },
                body: new FormData(form),
            })
                .then((r) => r.json().then((data) => ({ ok: r.ok, data })))
                .then(({ ok, data }) => {
                    if (ok && data.success) {
                        Swal.fire({
                            title: 'Cancelled',
                            text: data.message || 'Your reservation has been cancelled.',
                            icon: 'success',
                            confirmButtonColor: '#D4AF37',
                        }).then(() => window.location.reload());
                    } else {
                        Swal.fire('Failed', data.message || 'Something went wrong.', 'error');
                    }
                })
                .catch(() => {
                    Swal.fire('Error', 'Failed to cancel reservation. Please try again.', 'error');
                });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('searchInput')?.addEventListener('keyup', applyFilterAndSearch);

        document.querySelectorAll('.filter-btn').forEach((btn) => {
            btn.addEventListener('click', () => setFilter(btn));
        });

        document.querySelectorAll('.btn-view-detail').forEach((btn) => {
            btn.addEventListener('click', () => openDetail(btn.closest('.reservation-row')));
        });

        document.getElementById('btnCloseDetail')?.addEventListener('click', () => hideModal('detailModal'));
        document.getElementById('btnCloseDetailFooter')?.addEventListener('click', () => hideModal('detailModal'));

        document.querySelectorAll('.form-cancel').forEach((form) => {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                handleCancelSubmit(form);
            });
        });
    });
})();
