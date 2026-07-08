(function () {
    'use strict';

    const rupiah = (n) => 'Rp ' + Number(Math.round(n)).toLocaleString('id-ID');
    const nightsBetween = (a, b) => {
        if (!a || !b) return 0;
        const diff = Math.round((new Date(b) - new Date(a)) / 86400000);
        return diff > 0 ? diff : 0;
    };

    let selectedRoom = null;
    let promoState = { valid: false, discount: 0, code: null };
    let promoDebounceTimer = null;

    /* ---------- modal open/close ---------- */

    function showModal() {
        const m = document.getElementById('bookingModal');
        m.classList.remove('hidden');
        m.classList.add('flex');
        document.body.style.overflow = 'hidden';
        goToStep(1);
    }

    function hideModal() {
        const m = document.getElementById('bookingModal');
        m.classList.add('hidden');
        m.classList.remove('flex');
        document.body.style.overflow = '';
    }

    function goToStep(step) {
        document.getElementById('bookingStep1').classList.toggle('hidden', step !== 1);
        document.getElementById('bookingStep2').classList.toggle('hidden', step !== 2);
        document.getElementById('bookingStep3').classList.toggle('hidden', step !== 3);

        const labels = {
            1: 'Step 1 of 3 &middot; Choose your dates',
            2: 'Step 2 of 3 &middot; Pick a room',
            3: 'Step 3 of 3 &middot; Guest details & payment',
        };
        document.getElementById('bookingStepLabel').innerHTML = labels[step];
    }

    /* ---------- helpers ---------- */

    function clearFieldErrors() {
        document.querySelectorAll('.error-text').forEach((el) => {
            el.textContent = '';
            el.classList.add('hidden');
        });
    }

    function showFieldErrors(errors) {
        clearFieldErrors();
        Object.keys(errors || {}).forEach((field) => {
            const el = document.querySelector(`[data-error-for="${field}"]`);
            if (el) {
                el.textContent = errors[field][0];
                el.classList.remove('hidden');
            }
        });
    }

    /* ---------- STEP 1 -> STEP 2 : search available rooms ---------- */

    function searchRooms() {
        clearFieldErrors();

        const checkIn = document.getElementById('searchCheckIn').value;
        const checkOut = document.getElementById('searchCheckOut').value;
        const guests = document.getElementById('searchGuests').value || 1;

        if (!checkIn || !checkOut) {
            Swal.fire({ title: 'Incomplete', text: 'Please select check-in and check-out dates.', icon: 'warning', confirmButtonColor: '#D4AF37' });
            return;
        }
        if (nightsBetween(checkIn, checkOut) <= 0) {
            Swal.fire({ title: 'Invalid Dates', text: 'Check-out must be after check-in.', icon: 'error', confirmButtonColor: '#D4AF37' });
            return;
        }

        goToStep(2);
        document.getElementById('roomLoading').classList.remove('hidden');
        document.getElementById('noRoomsFound').classList.add('hidden');
        document.getElementById('roomResultsGrid').innerHTML = '';

        const params = new URLSearchParams({ check_in: checkIn, check_out: checkOut, guests });

        fetch(`${window.routes.availableRooms}?${params.toString()}`, {
            headers: { 'Accept': 'application/json' },
        })
            .then((r) => r.json())
            .then((data) => {
                document.getElementById('roomLoading').classList.add('hidden');

                if (!data.success || !data.rooms.length) {
                    document.getElementById('noRoomsFound').classList.remove('hidden');
                    return;
                }

                renderRoomResults(data.rooms, checkIn, checkOut, guests);
            })
            .catch(() => {
                document.getElementById('roomLoading').classList.add('hidden');
                Swal.fire('Error', 'Failed to load available rooms. Please try again.', 'error');
                goToStep(1);
            });
    }

    function renderRoomResults(rooms, checkIn, checkOut, guests) {
        const grid = document.getElementById('roomResultsGrid');

        grid.innerHTML = rooms.map((room, i) => `
            <div class="room-result-card rounded-2xl overflow-hidden border border-slate-100 shadow-sm hover:shadow-md transition cursor-pointer" data-index="${i}">
                <img src="${room.image_url}" class="w-full h-32 object-cover" alt="${room.room_name}">
                <div class="p-4">
                    <span class="inline-block promo-badge text-xs font-semibold px-2 py-1 rounded-lg mb-2">${room.room_type}</span>
                    <h4 class="font-semibold text-navy text-sm">${room.room_name}</h4>
                    <p class="text-xs text-slate-500 mb-2">No.${room.room_number} &middot; Floor ${room.floor} &middot; Max ${room.capacity} guests</p>
                    <div class="flex items-baseline gap-1">
                        <span class="text-base font-bold text-navy">${rupiah(room.price)}</span>
                        <span class="text-xs text-slate-400">/ night</span>
                    </div>
                </div>
            </div>
        `).join('');

        grid.querySelectorAll('.room-result-card').forEach((card) => {
            card.addEventListener('click', () => {
                const room = rooms[parseInt(card.dataset.index, 10)];
                selectRoom(room, checkIn, checkOut, guests);
            });
        });
    }

    /* ---------- STEP 2 -> STEP 3 : select room ---------- */

    function selectRoom(room, checkIn, checkOut, guests) {
        selectedRoom = room;
        promoState = { valid: false, discount: 0, code: null };

        document.getElementById('selectedRoomId').value = room.id;
        document.getElementById('selectedRoomTypeId').value = room.room_type_id;
        document.getElementById('selectedRoomPrice').value = room.price;
        document.getElementById('finalCheckIn').value = checkIn;
        document.getElementById('finalCheckOut').value = checkOut;
        document.getElementById('finalGuests').value = guests;

        const facilitiesHtml = (room.facilities && room.facilities.length)
            ? `<div class="flex flex-wrap gap-1.5 mt-2">${room.facilities.map((f) => `<span class="facility-badge text-xs px-2 py-1 rounded-lg">${f}</span>`).join('')}</div>`
            : '';

        document.getElementById('selectedRoomCard').innerHTML = `
            <img src="${room.image_url}" class="w-full h-40 object-cover" alt="${room.room_name}">
            <div class="p-4">
                <span class="inline-block promo-badge text-xs font-semibold px-2 py-1 rounded-lg mb-2">${room.room_type}</span>
                <h4 class="font-semibold text-navy">${room.room_name}</h4>
                <p class="text-xs text-slate-500 mb-2">Room No. ${room.room_number} &middot; Floor ${room.floor}</p>
                <div class="flex items-baseline gap-1">
                    <span class="text-lg font-bold text-navy">${rupiah(room.price)}</span>
                    <span class="text-xs text-slate-400">/ night</span>
                </div>
                ${facilitiesHtml}
            </div>
        `;

        document.getElementById('promoCode').value = '';
        document.getElementById('promoMessage').textContent = '';
        clearFieldErrors();

        calcSummary();
        goToStep(3);
    }

    /* ---------- summary & promo ---------- */

    function calcSummary() {
        if (!selectedRoom) return { nights: 0, subtotal: 0, discount: 0, tax: 0, total: 0 };

        const checkIn = document.getElementById('finalCheckIn').value;
        const checkOut = document.getElementById('finalCheckOut').value;
        const n = nightsBetween(checkIn, checkOut);
        const subtotal = selectedRoom.price * n;
        const discount = promoState.valid ? promoState.discount : 0;
        const afterDiscount = Math.max(0, subtotal - discount);
        const tax = Math.round(afterDiscount * 0.10);
        const total = afterDiscount + tax;

        document.getElementById('sumPrice').textContent = rupiah(selectedRoom.price);
        document.getElementById('sumNights').textContent = n + ' night' + (n !== 1 ? 's' : '');
        document.getElementById('sumSubtotal').textContent = rupiah(subtotal);
        document.getElementById('sumDiscount').textContent = '- ' + rupiah(discount);
        document.getElementById('sumTax').textContent = rupiah(tax);
        document.getElementById('sumTotal').textContent = rupiah(total);

        return { nights: n, subtotal, discount, tax, total };
    }

    function checkPromo() {
        const promoInput = document.getElementById('promoCode');
        const promoMessage = document.getElementById('promoMessage');
        const code = promoInput.value.trim();

        if (!code) {
            promoState = { valid: false, discount: 0, code: null };
            promoMessage.textContent = '';
            promoMessage.className = 'text-xs mt-1';
            calcSummary();
            return;
        }

        if (!selectedRoom) return;

        const { subtotal, nights } = calcSummary();

        if (nights <= 0) return;

        fetch(window.routes.validatePromo, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('#bookingForm input[name="_token"]').value,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                promo_code: code,
                room_type_id: selectedRoom.room_type_id,
                subtotal,
            }),
        })
            .then((r) => r.json())
            .then((data) => {
                if (data.valid) {
                    promoState = { valid: true, discount: data.discount, code: data.promo_code };
                    promoMessage.textContent = `Promo "${data.promo_name}" applied.`;
                    promoMessage.className = 'text-xs mt-1 text-green-600';
                } else {
                    promoState = { valid: false, discount: 0, code: null };
                    promoMessage.textContent = data.message || 'Invalid promo code.';
                    promoMessage.className = 'text-xs mt-1 text-red-500';
                }
                calcSummary();
            })
            .catch(() => {
                promoState = { valid: false, discount: 0, code: null };
                promoMessage.textContent = 'Failed to check promo code. Please try again.';
                promoMessage.className = 'text-xs mt-1 text-red-500';
                calcSummary();
            });
    }

    /* ---------- submit ---------- */

    function submitReservation(e) {
        e.preventDefault();
        clearFieldErrors();

        const form = document.getElementById('bookingForm');
        const btn = document.getElementById('btnConfirmReservation');
        const formData = new FormData(form);

        btn.disabled = true;
        const originalLabel = btn.textContent;
        btn.textContent = 'Processing...';

        fetch(window.routes.storeReservation, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': formData.get('_token'),
                'Accept': 'application/json',
            },
            body: formData,
        })
            .then((r) => r.json().then((data) => ({ ok: r.ok, status: r.status, data })))
            .then(({ ok, status, data }) => {
                if (ok && data.success) {
                    Swal.fire({
                        title: 'Reservation created successfully',
                        text: `Code: ${data.code}. Your reservation is pending confirmation.`,
                        icon: 'success',
                        confirmButtonColor: '#D4AF37',
                    }).then(() => {
                        window.location.reload();
                    });
                } else if (status === 422 && data.errors) {
                    showFieldErrors(data.errors);
                } else {
                    Swal.fire('Failed', data.message || 'Something went wrong.', 'error');
                }
            })
            .catch(() => {
                Swal.fire('Error', 'Failed to create reservation. Please try again.', 'error');
            })
            .finally(() => {
                btn.disabled = false;
                btn.textContent = originalLabel;
            });
    }

    /* ---------- init ---------- */

    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('btnOpenBooking')?.addEventListener('click', showModal);
        document.getElementById('btnCloseBooking')?.addEventListener('click', hideModal);
        document.getElementById('btnCancelBooking')?.addEventListener('click', hideModal);

        document.getElementById('btnSearchRooms')?.addEventListener('click', searchRooms);
        document.getElementById('btnBackToStep1')?.addEventListener('click', () => goToStep(1));
        document.getElementById('btnBackToStep2')?.addEventListener('click', () => goToStep(2));

        document.getElementById('promoCode')?.addEventListener('keyup', () => {
            clearTimeout(promoDebounceTimer);
            promoDebounceTimer = setTimeout(checkPromo, 500);
        });

        document.getElementById('bookingForm')?.addEventListener('submit', submitReservation);
    });
})();
