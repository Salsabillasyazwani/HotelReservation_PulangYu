(function () {
    'use strict';

    const cfg = window.ReservationConfig || {};
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    async function apiFetch(url, options = {}) {
        const opts = {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                ...(options.body instanceof FormData ? {} : { 'Content-Type': 'application/json' }),
                ...(options.headers || {}),
            },
            ...options,
        };

        const res = await fetch(url, opts);
        let json = null;
        try { json = await res.json(); } catch (e) {}

        if (!res.ok) {
            const error = new Error(json?.message || `Request gagal (${res.status})`);
            error.status = res.status;
            error.errors = json?.errors || null;
            throw error;
        }
        return json;
    }

    function formatRupiah(amount) {
        return 'Rp ' + Number(amount || 0).toLocaleString('id-ID');
    }

    function formatDate(dateStr) {
        if (!dateStr) return '-';
        return new Date(dateStr).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    function getNights(checkIn, checkOut) {
        const start = new Date(checkIn);
        const end = new Date(checkOut);
        return Math.max(1, Math.ceil((end - start) / (1000 * 60 * 60 * 24)));
    }

    function getInitials(name) {
        return (name || '-').split(' ').map(n => n[0]).join('').slice(0, 2).toUpperCase();
    }

    function getAvatarColor(name) {
        const colors = ['bg-blue-600', 'bg-green-600', 'bg-orange-500', 'bg-purple-600', 'bg-pink-500', 'bg-teal-500', 'bg-indigo-600'];
        let hash = 0;
        for (let i = 0; i < (name || '').length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash);
        return colors[Math.abs(hash) % colors.length];
    }

    function getStatusClass(status) {
        const map = {
            'Pending': 'status-pending',
            'Confirmed': 'status-confirmed',
            'Checked In': 'status-checked-in',
            'Checked Out': 'status-checked-out',
            'Cancelled': 'status-cancelled',
        };
        return map[status] || 'status-pending';
    }

    function getPaymentClass(payment) {
        const map = {
            'Paid': 'payment-paid',
            'Unpaid': 'payment-unpaid',
            'Partial': 'payment-partial',
            'Refunded': 'payment-refunded',
        };
        return map[payment] || 'payment-unpaid';
    }

    function getStatusIcon(status) {
        const map = {
            'Pending': 'fa-clock',
            'Confirmed': 'fa-check',
            'Checked In': 'fa-door-open',
            'Checked Out': 'fa-sign-out-alt',
            'Cancelled': 'fa-times',
        };
        return map[status] || 'fa-circle';
    }

    function showToast(message) {
        const toast = document.getElementById('toast');
        if (!toast) return;
        document.getElementById('toast-message').textContent = message;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 3000);
    }

    /* =========================================================================
       PAGE 1: All Reservations (table + filters + pagination + modals)
       ========================================================================= */

    const tableBody = document.getElementById('reservation-table-body');

    if (tableBody) {
        const state = {
            page: 1,
            perPage: 10,
            sortColumn: 'id',
            sortDirection: 'desc',
        };

        const localStats = Object.assign({
            pending: 0, confirmed: 0, checked_in: 0, checked_out: 0, cancelled: 0,
        }, cfg.stats || {});

        const statusToStatKey = {
            'Pending': 'pending',
            'Confirmed': 'confirmed',
            'Checked In': 'checked_in',
            'Checked Out': 'checked_out',
            'Cancelled': 'cancelled',
        };

        function renderStats() {
            const total = Math.max(1, Object.keys(statusToStatKey).reduce((sum, s) => sum + (localStats[statusToStatKey[s]] || 0), 0));
            Object.entries(statusToStatKey).forEach(([label, key]) => {
                const el = document.getElementById(`stat-${key.replace('_', '-')}`);
                if (el) el.textContent = localStats[key] || 0;
                const percentEl = el?.closest('.bg-white')?.querySelector('span.rounded-full');
                if (percentEl) percentEl.textContent = Math.round(((localStats[key] || 0) / total) * 100) + '%';
            });
        }

        function adjustStats(oldStatus, newStatus) {
            const oldKey = statusToStatKey[oldStatus];
            const newKey = statusToStatKey[newStatus];
            if (oldKey && localStats[oldKey] > 0) localStats[oldKey]--;
            if (newKey) localStats[newKey] = (localStats[newKey] || 0) + 1;
            renderStats();
        }

        // Dibawa dari Global Search di Navbar (mis. /admin/reservations?search=RSV-2026-00001).
        // Tidak ada input search terpisah di halaman ini (sesuai kebijakan: hanya Navbar
        // yang punya search bar), tapi kalau datang dari link hasil pencarian, tabel
        // tetap ter-filter otomatis lewat parameter URL ini.
        let deepLinkSearch = new URLSearchParams(window.location.search).get('search') || '';

        function getFilterParams(page = state.page) {
            return new URLSearchParams({
                search: deepLinkSearch,
                status: document.getElementById('status-filter')?.value || '',
                payment_status: document.getElementById('payment-filter')?.value || '',
                room_type: document.getElementById('room-type-filter')?.value || '',
                date_from: document.getElementById('date-from')?.value || '',
                date_to: document.getElementById('date-to')?.value || '',
                sort: state.sortColumn,
                dir: state.sortDirection,
                per_page: state.perPage,
                page,
            });
        }

        let lastLoadedData = [];

        async function loadReservations(page = 1) {
            state.page = page;
            tableBody.innerHTML = `<tr><td colspan="11" class="px-6 py-10 text-center text-secondary text-sm">Loading...</td></tr>`;

            try {
                const params = getFilterParams(page);
                const json = await apiFetch(`${cfg.dataUrl}?${params.toString()}`);
                lastLoadedData = json.data || [];
                renderTable(lastLoadedData);
                renderPagination(json);
            } catch (err) {
                tableBody.innerHTML = `<tr><td colspan="11" class="px-6 py-10 text-center text-danger text-sm">Gagal memuat data: ${err.message}</td></tr>`;
            }
        }

        function renderTable(list) {
            tableBody.innerHTML = '';

            if (!list.length) {
                tableBody.innerHTML = `<tr><td colspan="11" class="px-6 py-10 text-center text-secondary text-sm">Tidak ada reservasi ditemukan.</td></tr>`;
                document.getElementById('showing-text').textContent = 'Showing 0 results';
                return;
            }

            list.forEach(rsv => {
                const nights = rsv.nights || getNights(rsv.check_in, rsv.check_out);
                const avatarColor = getAvatarColor(rsv.guest_name);
                const roomNumber = rsv.room?.room_number || '-';
                const roomTypeName = rsv.room?.room_type?.name || '-';
                const floor = rsv.room?.floor ?? '-';

                const tr = document.createElement('tr');
                tr.className = 'group';
                tr.innerHTML = `
                    <td class="px-6 py-2.5">
                        <input type="checkbox" class="custom-checkbox row-checkbox" data-id="${rsv.id}">
                    </td>
                    <td class="px-4 py-2.5">
                        <span class="font-semibold text-sm text-primary">${rsv.reservation_code}</span>
                    </td>
                    <td class="px-4 py-2.5">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full ${avatarColor} text-white flex items-center justify-center text-xs font-bold shadow-sm">
                                ${getInitials(rsv.guest_name)}
                            </div>
                            <div>
                                <p class="font-semibold text-sm text-text">${rsv.guest_name}</p>
                                <p class="text-xs text-secondary">${rsv.phone || ''}</p>
                                <p class="text-xs text-secondary">${rsv.email || ''}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-2.5">
                        <p class="font-semibold text-sm text-text">${roomNumber}</p>
                        <p class="text-xs text-secondary">${roomTypeName}</p>
                        <p class="text-xs text-secondary">Floor ${floor}</p>
                    </td>
                    <td class="px-4 py-2.5">
                        <p class="text-sm font-medium text-text">${formatDate(rsv.check_in)}</p>
                    </td>
                    <td class="px-4 py-2.5">
                        <p class="text-sm font-medium text-text">${formatDate(rsv.check_out)}</p>
                    </td>
                    <td class="px-4 py-2.5 text-center">
                        <span class="inline-flex items-center gap-1 text-sm font-medium text-text">
                            <i class="fa-solid fa-user text-secondary text-xs"></i> ${rsv.guests}
                        </span>
                    </td>
                    <td class="px-4 py-2.5 text-right">
                        <p class="text-sm font-bold text-text">${formatRupiah(rsv.total_amount)}</p>
                        <p class="text-xs text-secondary">${nights} night${nights > 1 ? 's' : ''}</p>
                    </td>
                    <td class="px-4 py-2.5 text-center">
                        <span class="status-badge ${getPaymentClass(rsv.payment_status)}">${rsv.payment_status}</span>
                    </td>
                    <td class="px-4 py-2.5 text-center">
                        <span class="status-badge ${getStatusClass(rsv.reservation_status)}">
                            <i class="fa-solid ${getStatusIcon(rsv.reservation_status)} text-[10px]"></i>
                            ${rsv.reservation_status}
                        </span>
                    </td>
                    <td class="px-6 py-2.5 text-center relative">
                        <button type="button" onclick="ReservationTable.toggleDropdown(event, ${rsv.id})" class="w-9 h-9 rounded-lg border border-border hover:bg-gray-100 hover:border-primary hover:text-primary transition-all text-secondary">
                            <i class="fa-solid fa-ellipsis-vertical"></i>
                        </button>
                    </td>
                `;
                tableBody.appendChild(tr);
            });

            document.querySelectorAll('.row-checkbox').forEach(cb => {
                cb.addEventListener('change', updateSelectAll);
            });
        }

        function renderDropdownItems(rsv) {
            const s = rsv.reservation_status;

            const viewItem = `<div class="dropdown-item" onclick="ReservationTable.openModal('view', ${rsv.id})"><i class="fa-regular fa-eye text-secondary w-4"></i> View</div>`;

            const editItem = (s === 'Pending' || s === 'Confirmed')
                ? `<div class="dropdown-item" onclick="ReservationTable.openModal('edit', ${rsv.id})"><i class="fa-regular fa-pen-to-square text-secondary w-4"></i> Edit</div>`
                : '';

            const checkInItem = s === 'Confirmed'
                ? `<div class="dropdown-item" onclick="ReservationTable.openModal('checkin', ${rsv.id})"><i class="fa-solid fa-door-open text-secondary w-4"></i> Check In</div>`
                : '';

            const checkOutItem = s === 'Checked In'
                ? `<div class="dropdown-item" onclick="ReservationTable.openModal('checkout', ${rsv.id})"><i class="fa-solid fa-sign-out-alt text-secondary w-4"></i> Check Out</div>`
                : '';

            const cancelItem = (s !== 'Checked Out' && s !== 'Cancelled')
                ? `<div class="dropdown-item danger" onclick="ReservationTable.openModal('cancel', ${rsv.id})"><i class="fa-solid fa-ban text-danger w-4"></i> Cancel Reservation</div>`
                : '';

            return [viewItem, editItem, checkInItem, checkOutItem, cancelItem].join('');
        }

        function updateSelectAll() {
            const allCb = document.querySelectorAll('.row-checkbox');
            const checked = document.querySelectorAll('.row-checkbox:checked');
            const selectAll = document.getElementById('select-all');
            if (!selectAll) return;
            if (checked.length === 0) { selectAll.checked = false; selectAll.indeterminate = false; }
            else if (checked.length === allCb.length) { selectAll.checked = true; selectAll.indeterminate = false; }
            else { selectAll.indeterminate = true; }
        }

        function renderPagination(meta) {
            const pagination = document.getElementById('pagination');
            const currentPage = meta.current_page || 1;
            const lastPage = meta.last_page || 1;

            document.getElementById('showing-text').textContent =
                `Showing ${meta.from ?? 0} to ${meta.to ?? 0} of ${meta.total ?? 0} results`;

            pagination.innerHTML = '';
            if (lastPage <= 1) return;

            const prev = document.createElement('button');
            prev.className = `w-9 h-9 rounded-lg border border-border flex items-center justify-center text-sm transition-colors ${currentPage === 1 ? 'text-gray-300 cursor-not-allowed' : 'hover:bg-gray-50 text-secondary'}`;
            prev.innerHTML = '<i class="fa-solid fa-chevron-left"></i>';
            prev.disabled = currentPage === 1;
            prev.onclick = () => loadReservations(currentPage - 1);
            pagination.appendChild(prev);

            const maxVisible = 5;
            let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
            let endPage = Math.min(lastPage, startPage + maxVisible - 1);
            if (endPage - startPage < maxVisible - 1) startPage = Math.max(1, endPage - maxVisible + 1);

            if (startPage > 1) {
                pagination.appendChild(createPageBtn(1, currentPage));
                if (startPage > 2) pagination.appendChild(createEllipsis());
            }
            for (let i = startPage; i <= endPage; i++) pagination.appendChild(createPageBtn(i, currentPage));
            if (endPage < lastPage) {
                if (endPage < lastPage - 1) pagination.appendChild(createEllipsis());
                pagination.appendChild(createPageBtn(lastPage, currentPage));
            }

            const next = document.createElement('button');
            next.className = `w-9 h-9 rounded-lg border border-border flex items-center justify-center text-sm transition-colors ${currentPage === lastPage ? 'text-gray-300 cursor-not-allowed' : 'hover:bg-gray-50 text-secondary'}`;
            next.innerHTML = '<i class="fa-solid fa-chevron-right"></i>';
            next.disabled = currentPage === lastPage;
            next.onclick = () => loadReservations(currentPage + 1);
            pagination.appendChild(next);
        }

        function createPageBtn(page, currentPage) {
            const btn = document.createElement('button');
            btn.className = `w-9 h-9 rounded-lg text-sm font-semibold transition-all ${page === currentPage ? 'bg-primary text-white shadow-glow' : 'border border-border text-secondary hover:bg-gray-50'}`;
            btn.textContent = page;
            btn.onclick = () => loadReservations(page);
            return btn;
        }

        function createEllipsis() {
            const span = document.createElement('span');
            span.className = 'w-9 h-9 flex items-center justify-center text-secondary text-sm';
            span.textContent = '...';
            return span;
        }

        function getSharedDropdown() {
            let el = document.getElementById('shared-action-dropdown');
            if (!el) {
                el = document.createElement('div');
                el.id = 'shared-action-dropdown';
                el.className = 'action-dropdown';
                document.body.appendChild(el);
            }
            return el;
        }

        function closeSharedDropdown() {
            const el = document.getElementById('shared-action-dropdown');
            if (el) {
                el.classList.remove('active');
                el.removeAttribute('data-open-id');
            }
        }

        function toggleDropdown(event, id) {
            event.stopPropagation();
            const dropdown = getSharedDropdown();

            const isOpenForThisRow = dropdown.classList.contains('active') && dropdown.getAttribute('data-open-id') === String(id);
            if (isOpenForThisRow) {
                closeSharedDropdown();
                return;
            }

            const rsv = lastLoadedData.find(r => String(r.id) === String(id));
            if (!rsv) {
                showToast('Data reservasi tidak ditemukan, coba refresh halaman.');
                return;
            }

            dropdown.innerHTML = renderDropdownItems(rsv);
            dropdown.setAttribute('data-open-id', String(id));

            const btn = event.currentTarget;
            const rect = btn.getBoundingClientRect();
            const dropdownWidth = 220;

            let left = rect.right - dropdownWidth;
            if (left < 8) left = 8;
            if (left + dropdownWidth > window.innerWidth - 8) {
                left = window.innerWidth - dropdownWidth - 8;
            }
            dropdown.style.left = `${left}px`;

            dropdown.style.visibility = 'hidden';
            dropdown.classList.add('active');
            const estimatedHeight = dropdown.scrollHeight || 200;

            let top = rect.bottom + 8;
            if (top + estimatedHeight > window.innerHeight - 8) {
                top = rect.top - estimatedHeight - 8;
                if (top < 8) top = 8;
            }
            dropdown.style.top = `${top}px`;
            dropdown.style.visibility = '';
        }

        document.addEventListener('click', closeSharedDropdown);
        document.querySelector('.overflow-x-auto')?.addEventListener('scroll', closeSharedDropdown);
        window.addEventListener('scroll', closeSharedDropdown, true);
        window.addEventListener('resize', closeSharedDropdown);

        function closeModal(modalId) {
            document.getElementById(modalId)?.classList.remove('active');
        }

        async function openModal(type, id) {
            closeSharedDropdown();

            let rsv;
            try {
                rsv = await apiFetch(`${cfg.resourceBaseUrl}/${id}`);
            } catch (err) {
                showToast('Gagal memuat detail reservasi: ' + err.message);
                return;
            }

            if (type === 'view') {
                renderViewModal(rsv);
                document.getElementById('view-modal').classList.add('active');
            } else if (type === 'edit') {
                populateEditForm(rsv);
                document.getElementById('edit-modal').classList.add('active');
            } else if (type === 'checkin') {
                populateCheckInForm(rsv);
                document.getElementById('checkin-modal').classList.add('active');
            } else if (type === 'checkout') {
                populateCheckOutForm(rsv);
                document.getElementById('checkout-modal').classList.add('active');
            } else if (type === 'cancel') {
                document.getElementById('cancel-id').value = rsv.id;
                document.getElementById('cancel-reason').value = '';
                document.getElementById('cancel-modal').classList.add('active');
            }
        }

        function renderViewModal(rsv) {
            const nights = rsv.nights || getNights(rsv.check_in, rsv.check_out);
            const avatarColor = getAvatarColor(rsv.guest_name);
            const roomNumber = rsv.room?.room_number || '-';
            const roomTypeName = rsv.room?.room_type?.name || '-';
            const floor = rsv.room?.floor ?? '-';

            document.getElementById('view-subtitle').textContent = `${rsv.reservation_code} • ${rsv.guest_name}`;

            document.getElementById('view-content').innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-6">
                        <div class="bg-blue-50 rounded-xl p-5 border border-blue-100">
                            <h3 class="font-display font-bold text-lg text-text mb-4">Guest Information</h3>
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-14 h-14 rounded-full ${avatarColor} text-white flex items-center justify-center font-bold text-lg">${getInitials(rsv.guest_name)}</div>
                                <div>
                                    <p class="font-bold text-text">${rsv.guest_name}</p>
                                    <p class="text-sm text-secondary">${rsv.nationality || '-'}</p>
                                </div>
                            </div>
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between"><span class="text-secondary">Email</span><span class="font-medium text-text">${rsv.email || '-'}</span></div>
                                <div class="flex justify-between"><span class="text-secondary">Phone</span><span class="font-medium text-text">${rsv.phone || '-'}</span></div>
                                <div class="flex justify-between"><span class="text-secondary">Number of Guests</span><span class="font-medium text-text">${rsv.guests} person${rsv.guests > 1 ? 's' : ''}</span></div>
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-5 border border-border">
                            <h3 class="font-display font-bold text-lg text-text mb-4">Reservation Information</h3>
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between"><span class="text-secondary">Reservation ID</span><span class="font-bold text-primary">${rsv.reservation_code}</span></div>
                                <div class="flex justify-between"><span class="text-secondary">Check In</span><span class="font-medium text-text">${formatDate(rsv.check_in)}</span></div>
                                <div class="flex justify-between"><span class="text-secondary">Check Out</span><span class="font-medium text-text">${formatDate(rsv.check_out)}</span></div>
                                <div class="flex justify-between"><span class="text-secondary">Duration</span><span class="font-medium text-text">${nights} night${nights > 1 ? 's' : ''}</span></div>
                                <div class="flex justify-between"><span class="text-secondary">Reservation Status</span><span class="status-badge ${getStatusClass(rsv.reservation_status)}">${rsv.reservation_status}</span></div>
                                <div class="flex justify-between"><span class="text-secondary">Payment Status</span><span class="status-badge ${getPaymentClass(rsv.payment_status)}">${rsv.payment_status}</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-6">
                        <div class="bg-purple-50 rounded-xl p-5 border border-purple-100">
                            <h3 class="font-display font-bold text-lg text-text mb-4">Room Information</h3>
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between"><span class="text-secondary">Room Number</span><span class="font-bold text-text">${roomNumber}</span></div>
                                <div class="flex justify-between"><span class="text-secondary">Room Type</span><span class="font-medium text-text">${roomTypeName}</span></div>
                                <div class="flex justify-between"><span class="text-secondary">Floor</span><span class="font-medium text-text">Floor ${floor}</span></div>
                                <div class="flex justify-between"><span class="text-secondary">Price per Night</span><span class="font-medium text-text">${formatRupiah(rsv.price_per_night)}</span></div>
                            </div>
                        </div>
                        <div class="bg-green-50 rounded-xl p-5 border border-green-100">
                            <h3 class="font-display font-bold text-lg text-text mb-4">Payment Information</h3>
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between"><span class="text-secondary">Room Charges</span><span class="font-medium text-text">${formatRupiah(rsv.price_per_night * nights)}</span></div>
                                <div class="flex justify-between"><span class="text-secondary">Tax</span><span class="font-medium text-text">${formatRupiah(rsv.tax)}</span></div>
                                <div class="flex justify-between"><span class="text-secondary">Discount</span><span class="font-medium text-text">- ${formatRupiah(rsv.discount)}</span></div>
                                <div class="flex justify-between"><span class="text-secondary">Additional Charges</span><span class="font-medium text-text">${formatRupiah(rsv.additional_charges)}</span></div>
                                <div class="flex justify-between pt-3 border-t border-green-200"><span class="font-bold text-text">Total Payment</span><span class="font-bold text-primary text-lg">${formatRupiah(rsv.total_amount)}</span></div>
                            </div>
                        </div>
                        ${rsv.special_request ? `
                        <div class="bg-orange-50 rounded-xl p-5 border border-orange-100">
                            <h3 class="font-display font-bold text-lg text-text mb-2">Special Request</h3>
                            <p class="text-sm text-secondary">${rsv.special_request}</p>
                        </div>` : ''}
                    </div>
                </div>
                <div class="flex justify-end pt-4">
                    <button onclick="ReservationTable.closeModal('view-modal')" class="btn-outline px-6 py-3 rounded-xl font-semibold text-sm">Close</button>
                </div>
            `;
        }

        function populateEditForm(rsv) {
            document.getElementById('edit-id').value = rsv.id;
            document.getElementById('edit-subtitle').textContent = `${rsv.reservation_code} • ${rsv.guest_name}`;
            document.getElementById('edit-modal').querySelector('h2').textContent = 'Edit Reservation';
            document.getElementById('edit-guest').value = rsv.guest_name;
            document.getElementById('edit-email').value = rsv.email || '';
            document.getElementById('edit-phone').value = rsv.phone;
            document.getElementById('edit-nationality').value = rsv.nationality || '';
            document.getElementById('edit-checkin').value = rsv.check_in;
            document.getElementById('edit-checkout').value = rsv.check_out;
            document.getElementById('edit-room').value = rsv.room?.room_number || '';
            document.getElementById('edit-guests').value = rsv.guests;
            document.getElementById('edit-status').value = rsv.reservation_status;
            document.getElementById('edit-payment').value = rsv.payment_status;
            document.getElementById('edit-request').value = rsv.special_request || '';

            document.getElementById('edit-form').dataset.currentReservation = JSON.stringify(rsv);
        }

        async function saveEdit(event) {
            event.preventDefault();
            const form = document.getElementById('edit-form');
            const id = document.getElementById('edit-id').value;

            let rsv = {};
            try { rsv = JSON.parse(form.dataset.currentReservation || '{}'); } catch (e) { rsv = {}; }

            const oldStatus = rsv.reservation_status || lastLoadedData.find(r => String(r.id) === String(id))?.reservation_status;

            const payload = {
                guest_name: document.getElementById('edit-guest').value,
                email: document.getElementById('edit-email').value,
                phone: document.getElementById('edit-phone').value,
                nationality: document.getElementById('edit-nationality').value,
                check_in: document.getElementById('edit-checkin').value,
                check_out: document.getElementById('edit-checkout').value,
                room_number: document.getElementById('edit-room').value,
                guests: parseInt(document.getElementById('edit-guests').value, 10),
                reservation_status: document.getElementById('edit-status').value,
                payment_status: document.getElementById('edit-payment').value,
                special_request: document.getElementById('edit-request').value,

                identity_number: rsv.identity_number,
                payment_method: rsv.payment_method,
                price_per_night: rsv.price_per_night,
                deposit: rsv.deposit,
                tax: rsv.tax,
                discount: rsv.discount,
                additional_charges: rsv.additional_charges,
                notes: rsv.notes,
            };

            const submitBtn = form.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn ? submitBtn.textContent : '';
            if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Saving...'; }

            try {
                const json = await apiFetch(`${cfg.resourceBaseUrl}/${id}`, {
                    method: 'PUT',
                    body: JSON.stringify(payload),
                });
                showToast(json.message || 'Reservasi berhasil diperbarui.');
                closeModal('edit-modal');
                if (oldStatus) adjustStats(oldStatus, payload.reservation_status);
                loadReservations(state.page);
            } catch (err) {
                console.error('Update reservasi gagal:', err.status, err.errors, err.message);
                if (err.errors) {
                    showToast(Object.values(err.errors).flat().join(', '));
                } else {
                    showToast(err.message || 'Gagal menyimpan perubahan.');
                }
            } finally {
                if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = originalBtnText; }
            }
        }

        function populateCheckInForm(rsv) {
            document.getElementById('checkin-id').value = rsv.id;
            document.getElementById('checkin-guest').textContent = rsv.guest_name;
            document.getElementById('checkin-room').textContent = `${rsv.room?.room_number || '-'} • ${rsv.room?.room_type?.name || '-'}`;
            document.getElementById('checkin-date').textContent = formatDate(rsv.check_in);
            document.getElementById('checkin-rsv').textContent = rsv.reservation_code;
            document.getElementById('checkin-avatar').textContent = getInitials(rsv.guest_name);
            document.getElementById('checkin-avatar').className = `w-12 h-12 rounded-full ${getAvatarColor(rsv.guest_name)} text-white flex items-center justify-center font-bold`;

            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            document.getElementById('checkin-actual').value = now.toISOString().slice(0, 16);
            document.getElementById('checkin-notes').value = '';
        }

        async function confirmCheckIn(event) {
            event.preventDefault();
            const id = document.getElementById('checkin-id').value;
            try {
                const json = await apiFetch(`${cfg.resourceBaseUrl}/${id}/checkin`, {
                    method: 'PATCH',
                    body: JSON.stringify({
                        actual_check_in: document.getElementById('checkin-actual').value,
                        notes: document.getElementById('checkin-notes').value,
                    }),
                });
                showToast(json.message || 'Guest berhasil check in.');
                closeModal('checkin-modal');
                adjustStats('Confirmed', 'Checked In');
                loadReservations(state.page);
            } catch (err) {
                showToast(err.message || 'Gagal melakukan check in.');
            }
        }

        function populateCheckOutForm(rsv) {
            document.getElementById('checkout-id').value = rsv.id;
            document.getElementById('checkout-guest').textContent = rsv.guest_name;
            document.getElementById('checkout-room').textContent = `${rsv.room?.room_number || '-'} • ${rsv.room?.room_type?.name || '-'}`;
            document.getElementById('checkout-room-num').textContent = rsv.room?.room_number || '-';
            document.getElementById('checkout-original').textContent = formatDate(rsv.check_out);
            document.getElementById('checkout-avatar').textContent = getInitials(rsv.guest_name);
            document.getElementById('checkout-avatar').className = `w-12 h-12 rounded-full ${getAvatarColor(rsv.guest_name)} text-white flex items-center justify-center font-bold`;

            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            document.getElementById('checkout-actual').value = now.toISOString().slice(0, 16);
            document.getElementById('checkout-charges').value = 0;
            document.getElementById('checkout-notes').value = '';
        }

        async function confirmCheckOut(event) {
            event.preventDefault();
            const id = document.getElementById('checkout-id').value;
            try {
                const json = await apiFetch(`${cfg.resourceBaseUrl}/${id}/checkout`, {
                    method: 'PATCH',
                    body: JSON.stringify({
                        actual_check_out: document.getElementById('checkout-actual').value,
                        additional_charges: parseInt(document.getElementById('checkout-charges').value, 10) || 0,
                        notes: document.getElementById('checkout-notes').value,
                    }),
                });
                showToast(json.message || 'Guest berhasil check out.');
                closeModal('checkout-modal');
                adjustStats('Checked In', 'Checked Out');
                loadReservations(state.page);
            } catch (err) {
                showToast(err.message || 'Gagal melakukan check out.');
            }
        }

        async function confirmCancel(event) {
            event.preventDefault();
            const id = document.getElementById('cancel-id').value;
            const oldStatus = lastLoadedData.find(r => String(r.id) === String(id))?.reservation_status;
            try {
                const json = await apiFetch(`${cfg.resourceBaseUrl}/${id}/cancel`, {
                    method: 'PATCH',
                    body: JSON.stringify({ cancel_reason: document.getElementById('cancel-reason').value }),
                });
                showToast(json.message || 'Reservasi berhasil dibatalkan.');
                closeModal('cancel-modal');
                if (oldStatus) adjustStats(oldStatus, 'Cancelled');
                loadReservations(state.page);
            } catch (err) {
                showToast(err.message || 'Gagal membatalkan reservasi.');
            }
        }

        ['status-filter', 'payment-filter', 'room-type-filter', 'date-from', 'date-to'].forEach(id => {
            document.getElementById(id)?.addEventListener('change', () => loadReservations(1));
        });

        window.resetFilters = function () {
            ['status-filter', 'payment-filter', 'room-type-filter'].forEach(id => document.getElementById(id).value = '');
            ['date-from', 'date-to'].forEach(id => document.getElementById(id).value = '');
            deepLinkSearch = '';
            history.replaceState(null, '', window.location.pathname);
            loadReservations(1);
            showToast('Filters reset successfully');
        };

        window.sortBy = function (column) {
            if (state.sortColumn === column) {
                state.sortDirection = state.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                state.sortColumn = column;
                state.sortDirection = 'asc';
            }
            loadReservations(1);
        };

        window.exportData = async function () {
            try {
                const params = getFilterParams(1);
                params.set('per_page', 5000);
                const json = await apiFetch(`${cfg.dataUrl}?${params.toString()}`);
                const rows = json.data || [];

                const csv = [
                    ['Reservation ID', 'Guest', 'Email', 'Phone', 'Room', 'Check In', 'Check Out', 'Guests', 'Total Payment', 'Payment Status', 'Reservation Status'].join(','),
                    ...rows.map(r => [
                        r.reservation_code, r.guest_name, r.email || '', r.phone, r.room?.room_number || '',
                        r.check_in, r.check_out, r.guests, r.total_amount, r.payment_status, r.reservation_status,
                    ].join(',')),
                ].join('\n');

                const blob = new Blob([csv], { type: 'text/csv' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `hotel-pulang-yo-reservations-${new Date().toISOString().slice(0, 10)}.csv`;
                a.click();
                URL.revokeObjectURL(url);
                showToast('Reservations exported successfully');
            } catch (err) {
                showToast('Gagal export data: ' + err.message);
            }
        };

        document.getElementById('select-all')?.addEventListener('change', function () {
            document.querySelectorAll('.row-checkbox').forEach(cb => { cb.checked = this.checked; });
        });

        document.querySelectorAll('.modal-overlay').forEach(modal => {
            modal.addEventListener('click', function (e) { if (e.target === this) closeModal(this.id); });
        });

        document.getElementById('edit-form')?.addEventListener('submit', saveEdit);
        document.getElementById('checkin-form')?.addEventListener('submit', confirmCheckIn);
        document.getElementById('checkout-form')?.addEventListener('submit', confirmCheckOut);
        document.getElementById('cancel-form')?.addEventListener('submit', confirmCancel);

        window.ReservationTable = { toggleDropdown, openModal, closeModal };
        window.closeModal = closeModal;

        renderStats();
        loadReservations(1);
    }

    /* =========================================================================
       PAGE 2: Create Reservation (form + room lookup + summary popup)
       ========================================================================= */

    const roomTypeSelect = document.getElementById('roomType');

    if (roomTypeSelect) {
        let roomDataMap = {};
        let appliedPromotion = null;

        function calculateNights() {
            const checkIn = document.getElementById('checkIn').value;
            const checkOut = document.getElementById('checkOut').value;
            if (checkIn && checkOut) {
                const nights = getNights(checkIn, checkOut);
                document.getElementById('nights').value = nights;
                updateSummary();
            }
        }

        async function loadRoomsByType(keepSelection = false) {
            const roomType = roomTypeSelect.value;
            const checkIn = document.getElementById('checkIn').value;
            const checkOut = document.getElementById('checkOut').value;
            const select = document.getElementById('availableRoom');
            const previous = select.value;

            select.innerHTML = `<option value="">Loading...</option>`;

            try {
                const params = new URLSearchParams({ room_type: roomType, check_in: checkIn, check_out: checkOut });
                const rooms = await apiFetch(`${cfg.availableRoomsUrl}?${params.toString()}`);

                roomDataMap = {};
                rooms.forEach(r => { roomDataMap[r.room_number] = r; });

                if (!rooms.length) {
                    select.innerHTML = `<option value="">No rooms available</option>`;
                    resetRoomPreview();
                    return;
                }

                select.innerHTML = rooms.map(r => `<option value="${r.room_number}">${r.room_number}${r.room_name ? ' - ' + r.room_name : ''}</option>`).join('');

                if (keepSelection && roomDataMap[previous]) select.value = previous;

                updateRoomSelection();
            } catch (err) {
                select.innerHTML = `<option value="">Gagal memuat kamar</option>`;
                showToast('Gagal memuat daftar kamar: ' + err.message);
            }
        }

        function resetRoomPreview() {
            document.getElementById('pricePerNight').innerHTML = '-';
            document.getElementById('roomCapacity').innerHTML = '-';
            document.getElementById('roomName').innerHTML = 'Select a room';
            const img = document.getElementById('roomImage');
            img.classList.add('hidden');
            document.getElementById('roomImagePlaceholder')?.classList.remove('hidden');
            updateSummary();
        }

        function updateRoomSelection() {
            const roomNumber = document.getElementById('availableRoom').value;
            const room = roomDataMap[roomNumber];
            if (!room) { resetRoomPreview(); return; }

            document.getElementById('pricePerNight').innerHTML = 'Rp ' + Number(room.price).toLocaleString('id-ID');
            document.getElementById('roomCapacity').innerHTML = room.capacity + ' Guests';
            document.getElementById('roomName').innerHTML = room.room_name || room.room_number;

            const badge = document.getElementById('roomStatusBadge');
            if (badge) badge.textContent = room.status;

            const img = document.getElementById('roomImage');
            const placeholder = document.getElementById('roomImagePlaceholder');
            if (room.image) {
                img.src = room.image.startsWith('http') ? room.image : `/storage/${room.image}`;
                img.classList.remove('hidden');
                placeholder?.classList.add('hidden');
            } else {
                img.classList.add('hidden');
                placeholder?.classList.remove('hidden');
            }

            updateSummary();
        }

        function calculatePromoDiscount(subTotal) {
            if (!appliedPromotion) return 0;
            let discount = appliedPromotion.discount_type === 'Percentage'
                ? subTotal * (Number(appliedPromotion.discount_value) / 100)
                : Number(appliedPromotion.discount_value);

            if (appliedPromotion.maximum_discount) {
                discount = Math.min(discount, Number(appliedPromotion.maximum_discount));
            }
            return Math.round(Math.min(discount, subTotal));
        }

        function clearAppliedPromo() {
            appliedPromotion = null;
            document.getElementById('promoFeedback')?.classList.add('hidden');
            updateSummary();
        }

        async function applyPromoCode() {
            const codeInput = document.getElementById('promoCode');
            const feedback = document.getElementById('promoFeedback');
            const code = codeInput.value.trim();

            if (!code) { clearAppliedPromo(); return; }

            const btn = document.getElementById('btnApplyPromo');
            const originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Checking...';

            try {
                const params = new URLSearchParams({
                    promo_code: code,
                    room_type: roomTypeSelect.value,
                    check_in: document.getElementById('checkIn').value,
                    check_out: document.getElementById('checkOut').value,
                });
                const json = await apiFetch(`${cfg.validatePromoUrl}?${params.toString()}`);
                appliedPromotion = json.promotion;
                feedback.textContent = `Promo "${json.promotion.promo_name}" berhasil diterapkan.`;
                feedback.className = 'text-xs mt-1.5 text-emerald-600 font-medium';
                feedback.classList.remove('hidden');
            } catch (err) {
                appliedPromotion = null;
                feedback.textContent = err.message || 'Kode promo tidak valid.';
                feedback.className = 'text-xs mt-1.5 text-red-500 font-medium';
                feedback.classList.remove('hidden');
            } finally {
                btn.disabled = false;
                btn.textContent = originalText;
                updateSummary();
            }
        }

        function updateSummary() {
            const guestName = document.getElementById('guestName').value || '-';
            const phone = document.getElementById('phone').value || '-';

            document.getElementById('summaryGuestName').innerHTML = guestName;
            document.getElementById('summaryPhone').innerHTML = phone;
            const initialEl = document.getElementById('summaryGuestInitial');
            if (initialEl) initialEl.textContent = guestName !== '-' ? getInitials(guestName) : '-';

            document.getElementById('summaryRoomName').innerHTML = document.getElementById('roomName').innerHTML;
            document.getElementById('summaryRoomCapacity').innerHTML = document.getElementById('roomCapacity').innerHTML;

            const roomImg = document.getElementById('roomImage');
            const summaryImg = document.getElementById('summaryRoomImage');
            if (roomImg && summaryImg) {
                if (!roomImg.classList.contains('hidden') && roomImg.src) {
                    summaryImg.src = roomImg.src;
                    summaryImg.classList.remove('hidden');
                } else {
                    summaryImg.classList.add('hidden');
                }
            }

            const checkIn = document.getElementById('checkIn').value;
            const checkOut = document.getElementById('checkOut').value;
            const nights = parseInt(document.getElementById('nights').value, 10) || 1;
            const guests = document.getElementById('guests').value || 1;

            if (checkIn) document.getElementById('summaryCheckIn').innerHTML = formatDate(checkIn) + ' (14:00)';
            if (checkOut) document.getElementById('summaryCheckOut').innerHTML = formatDate(checkOut) + ' (12:00)';
            document.getElementById('summaryDuration').innerHTML = nights + ' Nights';
            document.getElementById('summaryGuests').innerHTML = guests + ' Guests';

            const roomNumber = document.getElementById('availableRoom').value;
            const room = roomDataMap[roomNumber];
            const price = room ? Number(room.price) : 0;
            const total = price * nights;
            const discount = calculatePromoDiscount(total);
            const tax = Math.round(total * 0.1);
            const grandTotal = total + tax - discount;

            document.getElementById('summaryPrice').innerHTML = formatRupiah(price);
            document.getElementById('summaryTotal').innerHTML = formatRupiah(total);
            document.getElementById('summaryTax').innerHTML = formatRupiah(tax);
            document.getElementById('summaryGrandTotal').innerHTML = formatRupiah(Math.max(0, grandTotal));

            const taxEl = document.getElementById('tax');
            if (taxEl) taxEl.innerHTML = formatRupiah(tax);

            const discountEl = document.getElementById('discount');
            if (discountEl) discountEl.innerHTML = discount > 0 ? '- ' + formatRupiah(discount) : formatRupiah(0);
        }

        const form = document.getElementById('reservationForm') || roomTypeSelect.closest('form');
        const summaryModal = document.getElementById('save-summary-modal');
        const summaryBody = document.getElementById('save-summary-body');
        const summaryActions = document.getElementById('save-summary-actions');

        let pendingAction = 'save';

        function clearFieldErrors() {
            form.querySelectorAll('.field-error').forEach(el => el.remove());
        }

        function showFieldErrors(errors) {
            clearFieldErrors();
            Object.entries(errors || {}).forEach(([field, messages]) => {
                const input = form.querySelector(`[name="${field}"]`);
                if (!input) return;
                const p = document.createElement('p');
                p.className = 'text-xs text-red-500 mt-1 field-error';
                p.textContent = messages[0];
                input.insertAdjacentElement('afterend', p);
            });
        }

        // Form submit -> hanya buka popup summary sebagai konfirmasi, belum kirim apapun
        form?.addEventListener('submit', function (e) {
            e.preventDefault();
            clearFieldErrors();
            pendingAction = e.submitter?.value || 'save';
            openSummaryModal(pendingAction);
        });

        function openSummaryModal(action) {
            if (!summaryModal) {
                doSubmit(action);
                return;
            }
            const actionLabel = action === 'save_checkin' ? 'Save & Check In' : 'Save Reservation';

            // summary values sudah ter-update terus lewat updateSummary(),
            // di sini kita cuma perlu pastikan tombolnya dalam kondisi "konfirmasi"
            if (summaryActions) {
                summaryActions.innerHTML = `
                    <button type="button" id="btn-back-edit" class="btn-outline px-6 py-3 rounded-2xl font-semibold text-sm">Back to Edit</button>
                    <button type="button" id="btn-confirm-save" class="btn-gradient-primary px-6 py-3 rounded-2xl font-semibold text-sm">Confirm &amp; Save</button>
                `;
                document.getElementById('btn-back-edit').onclick = closeSummaryModal;
                document.getElementById('btn-confirm-save').onclick = () => doSubmit(action);
            }

            summaryModal.classList.add('active');
        }

        function closeSummaryModal() {
            summaryModal?.classList.remove('active');
        }

        summaryModal?.addEventListener('click', function (e) {
            if (e.target === this) closeSummaryModal();
        });

        async function doSubmit(action) {
            const confirmBtn = document.getElementById('btn-confirm-save');
            const backBtn = document.getElementById('btn-back-edit');
            if (confirmBtn) { confirmBtn.disabled = true; confirmBtn.textContent = 'Saving...'; }
            if (backBtn) backBtn.disabled = true;

            const formData = new FormData(form);
            formData.set('action', action);

            try {
                const json = await apiFetch(cfg.storeUrl, { method: 'POST', body: formData });
                showSuccessSummary(json.reservation);
            } catch (err) {
                closeSummaryModal();
                if (confirmBtn) { confirmBtn.disabled = false; confirmBtn.textContent = 'Confirm & Save'; }
                if (backBtn) backBtn.disabled = false;

                if (err.status === 422 && err.errors) {
                    showFieldErrors(err.errors);
                    showToast('Periksa kembali form, ada data yang belum valid.');
                } else {
                    showToast(err.message || 'Gagal menyimpan reservasi.');
                }
            }
        }

        function showSuccessSummary(reservation) {
            const nights = reservation.nights || getNights(reservation.check_in, reservation.check_out);

            if (summaryBody) {
                summaryBody.innerHTML = `
                    <div class="text-center mb-4">
                        <div class="w-14 h-14 mx-auto rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-2xl mb-3">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <h3 class="font-bold text-lg text-text">Reservasi Berhasil Dibuat</h3>
                        <p class="text-sm text-secondary">${reservation.reservation_code}</p>
                    </div>
                    <div class="space-y-2 text-sm border-t border-border pt-4">
                        <div class="flex justify-between"><span class="text-secondary">Guest</span><span class="font-medium">${reservation.guest_name}</span></div>
                        <div class="flex justify-between"><span class="text-secondary">Room</span><span class="font-medium">${reservation.room?.room_number || '-'}</span></div>
                        <div class="flex justify-between"><span class="text-secondary">Check In</span><span class="font-medium">${formatDate(reservation.check_in)}</span></div>
                        <div class="flex justify-between"><span class="text-secondary">Check Out</span><span class="font-medium">${formatDate(reservation.check_out)}</span></div>
                        <div class="flex justify-between"><span class="text-secondary">Duration</span><span class="font-medium">${nights} night${nights > 1 ? 's' : ''}</span></div>
                        <div class="flex justify-between pt-2 border-t border-border"><span class="font-bold">Total</span><span class="font-bold text-primary">${formatRupiah(reservation.total_amount)}</span></div>
                    </div>
                `;
            }

            if (summaryActions) {
                summaryActions.innerHTML = `
                    <button type="button" id="btn-close-success" class="btn-gradient-primary px-6 py-3 rounded-2xl font-semibold text-sm w-full">Close & Add Another</button>
                `;
                document.getElementById('btn-close-success').onclick = () => {
                    // Kembali lagi ke halaman Create (form kosong), bukan ke All Reservations
                    window.location.href = cfg.createUrl || cfg.indexUrl;
                };
            }

            showToast('Reservasi berhasil disimpan.');
        }

        window.closeSaveSummaryModal = closeSummaryModal;

        ['checkIn', 'checkOut'].forEach(id => document.getElementById(id)?.addEventListener('change', () => { calculateNights(); loadRoomsByType(true); }));
        roomTypeSelect.addEventListener('change', () => loadRoomsByType(false));
        document.getElementById('availableRoom')?.addEventListener('change', updateRoomSelection);
        ['guestName', 'phone', 'guests', 'deposit'].forEach(id => document.getElementById(id)?.addEventListener('input', updateSummary));

        document.getElementById('btnApplyPromo')?.addEventListener('click', applyPromoCode);
        document.getElementById('promoCode')?.addEventListener('input', clearAppliedPromo);
        roomTypeSelect.addEventListener('change', clearAppliedPromo);
        ['checkIn', 'checkOut'].forEach(id => document.getElementById(id)?.addEventListener('change', clearAppliedPromo));

        calculateNights();
        loadRoomsByType(false);
    }
})();
