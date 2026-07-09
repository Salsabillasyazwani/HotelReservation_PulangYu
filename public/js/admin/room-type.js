document.addEventListener('DOMContentLoaded', function () {

    const rupiah = n => "Rp" + Number(n || 0).toLocaleString("id-ID");

    const searchInput = document.getElementById('searchInput');
    const statusSelect = document.getElementById('statusSelect');
    const sortSelect = document.getElementById('sortSelect');
    const resetBtn = document.getElementById('resetBtn');
    const tableBody = document.getElementById('tableBody');
    const emptyState = document.getElementById('emptyState');
    const showingInfo = document.getElementById('showingInfo');

    function applyFilters() {
        const search = (searchInput?.value || '').toLowerCase();
        const status = statusSelect?.value || 'all';
        const rows = Array.from(tableBody.querySelectorAll('.data-row'));

        let visibleCount = 0;
        rows.forEach(function (row) {
            const matchSearch = row.dataset.name.includes(search);
            const matchStatus = status === 'all' || row.dataset.status === status;
            const show = matchSearch && matchStatus;
            row.style.display = show ? '' : 'none';
            if (show) visibleCount++;
        });

        emptyState.classList.toggle('hidden', visibleCount > 0);
        if (showingInfo) {
            showingInfo.textContent = `Showing 1 to ${visibleCount} of ${rows.length} room types`;
        }

        applySort();
    }

    function applySort() {
        const sortBy = sortSelect?.value || 'newest';
        const rows = Array.from(tableBody.querySelectorAll('.data-row'));

        rows.sort(function (a, b) {
            switch (sortBy) {
                case 'newest': return Number(b.dataset.created) - Number(a.dataset.created);
                case 'oldest': return Number(a.dataset.created) - Number(b.dataset.created);
                case 'price-asc': return Number(a.dataset.price) - Number(b.dataset.price);
                case 'price-desc': return Number(b.dataset.price) - Number(a.dataset.price);
                case 'name-asc': return a.dataset.name.localeCompare(b.dataset.name);
                default: return 0;
            }
        });

        rows.forEach(row => tableBody.appendChild(row));
    }

    searchInput?.addEventListener('input', applyFilters);
    statusSelect?.addEventListener('change', applyFilters);
    sortSelect?.addEventListener('change', applyFilters);

    resetBtn?.addEventListener('click', function () {
        if (searchInput) searchInput.value = '';
        if (statusSelect) statusSelect.value = 'all';
        if (sortSelect) sortSelect.value = 'newest';
        applyFilters();
    });

    const viewModal = document.getElementById('viewModal');

    document.querySelectorAll('.btn-view').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const room = JSON.parse(this.dataset.room);
            const facilities = Array.isArray(room.facilities) ? room.facilities : [];

            document.getElementById('v_img').src = room.image ? '/storage/' + room.image : '/images/room-placeholder.jpg';
            document.getElementById('v_name').textContent = room.name;

            const statusEl = document.getElementById('v_status');
            statusEl.textContent = room.status === 'active' ? 'Active' : 'Inactive';
            statusEl.className = "text-xs font-medium px-3 py-1 rounded-full " + (room.status === 'active' ? 'badge-active' : 'badge-inactive');

            document.getElementById('v_price').textContent = rupiah(room.price) + " / Night";
            document.getElementById('v_capacity').textContent = room.capacity + " Guest";
            document.getElementById('v_bed').textContent = room.bed_type;
            document.getElementById('v_size').textContent = room.room_size + " m²";
            document.getElementById('v_total').textContent = room.total_rooms + " Rooms";
            document.getElementById('v_facilities').innerHTML = facilities.length
                ? facilities.map(f => `<span class="text-xs bg-[var(--navy-soft)] text-[var(--navy)] px-2.5 py-1 rounded-full">${f}</span>`).join('')
                : '<span class="text-xs text-gray-400">Belum ada fasilitas</span>';
            document.getElementById('v_desc').textContent = room.description || '-';

            viewModal.classList.remove('hidden');
            viewModal.classList.add('flex');
        });
    });

    function closeViewModal() {
        viewModal.classList.add('hidden');
        viewModal.classList.remove('flex');
    }
    window.closeViewModal = closeViewModal;

    const formModal = document.getElementById('formModal');
    const roomForm = document.getElementById('roomForm');

    function openAddModal() {
        document.getElementById('formModalTitle').textContent = "Add Room Type";
        roomForm.reset();
        roomForm.action = window.roomTypeRoutes.store;
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('roomId').value = '';
        document.getElementById('f_status').checked = true;
        document.getElementById('f_status_hidden').value = 'active';
        document.getElementById('f_status_label').textContent = 'Active';

        setCheckedFacilities([]);

        formModal.classList.remove('hidden');
        formModal.classList.add('flex');
    }

    document.getElementById('openAddModalBtn')?.addEventListener('click', openAddModal);

    document.querySelectorAll('.btn-edit').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const room = JSON.parse(this.dataset.room);

            document.getElementById('formModalTitle').textContent = "Edit Room Type";
            document.getElementById('roomId').value = room.id;
            document.getElementById('f_name').value = room.name;
            document.getElementById('f_bed').value = room.bed_type;
            document.getElementById('f_size').value = room.room_size;
            document.getElementById('f_desc').value = room.description;

            setCheckedFacilities(room.facility_ids || []);

            const isActive = room.status === 'active';
            document.getElementById('f_status').checked = isActive;
            document.getElementById('f_status_hidden').value = isActive ? 'active' : 'inactive';
            document.getElementById('f_status_label').textContent = isActive ? 'Active' : 'Inactive';

            roomForm.action = window.roomTypeRoutes.updateBase + '/' + room.id;
            document.getElementById('formMethod').value = 'PUT';

            formModal.classList.remove('hidden');
            formModal.classList.add('flex');
        });
    });

    function closeFormModal() {
        formModal.classList.add('hidden');
        formModal.classList.remove('flex');
    }
    window.closeFormModal = closeFormModal;

    document.getElementById('f_status')?.addEventListener('change', function (e) {
        document.getElementById('f_status_hidden').value = e.target.checked ? 'active' : 'inactive';
        document.getElementById('f_status_label').textContent = e.target.checked ? 'Active' : 'Inactive';
    });

    const facilityTrigger    = document.getElementById('facilityTrigger');
    const facilityPanel      = document.getElementById('facilityPanel');
    const facilitySelText    = document.getElementById('facilitySelectedText');
    const facilityTagsWrap   = document.getElementById('facilitySelectedTags');
    const facilitySearch     = document.getElementById('facilitySearchInput');
    const facilityOptionList = document.getElementById('facilityOptionList');
    const newFacilityInput   = document.getElementById('newFacilityInput');
    const addFacilityBtn     = document.getElementById('addFacilityBtn');

    function getCheckedFacilities() {
        return Array.from(facilityOptionList.querySelectorAll('input[type="checkbox"]:checked'));
    }

    function renderFacilitySelection() {
        const checked = getCheckedFacilities();

        facilitySelText.textContent = checked.length
            ? `${checked.length} fasilitas dipilih`
            : 'Pilih fasilitas...';

        facilityTagsWrap.innerHTML = checked.map(function (cb) {
            const label = cb.parentElement.querySelector('span').textContent;
            return `<span class="tag-chip" data-id="${cb.value}">${label}<button type="button" data-remove="${cb.value}">&times;</button></span>`;
        }).join('');
    }

    facilityTrigger?.addEventListener('click', function (e) {
        e.stopPropagation();
        facilityPanel.classList.toggle('hidden');
        facilityTrigger.classList.toggle('open');
    });

    document.addEventListener('click', function (e) {
        if (facilityPanel && !facilityPanel.classList.contains('hidden') &&
            !facilityPanel.contains(e.target) && e.target !== facilityTrigger) {
            facilityPanel.classList.add('hidden');
            facilityTrigger.classList.remove('open');
        }
    });

    facilityOptionList?.addEventListener('change', renderFacilitySelection);

    facilityTagsWrap?.addEventListener('click', function (e) {
        const removeId = e.target.dataset.remove;
        if (!removeId) return;
        const cb = facilityOptionList.querySelector(`input[value="${removeId}"]`);
        if (cb) cb.checked = false;
        renderFacilitySelection();
    });

    facilitySearch?.addEventListener('input', function () {
        const q = this.value.toLowerCase();
        facilityOptionList.querySelectorAll('.facility-option').forEach(function (opt) {
            const name = opt.querySelector('input').dataset.name;
            opt.classList.toggle('option-hidden', !name.includes(q));
        });
    });

    addFacilityBtn?.addEventListener('click', function () {
        const name = newFacilityInput.value.trim();
        if (!name) return;

        fetch(window.facilityStoreRoute, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
            },
            body: JSON.stringify({ name })
        })
        .then(res => {
            if (!res.ok) throw new Error('Request failed');
            return res.json();
        })
        .then(function (facility) {
            const emptyNote = facilityOptionList.querySelector('.facility-empty-note');
            if (emptyNote) emptyNote.remove();

            const label = document.createElement('label');
            label.className = 'facility-option';
            label.innerHTML = `<input type="checkbox" name="facilities[]" value="${facility.id}" data-name="${facility.name.toLowerCase()}" checked><span>${facility.name}</span>`;
            facilityOptionList.appendChild(label);
            newFacilityInput.value = '';
            renderFacilitySelection();
        })
        .catch(function () {
            Swal.fire('Gagal', 'Fasilitas gagal ditambahkan (mungkin nama sudah ada).', 'error');
        });
    });

    function setCheckedFacilities(ids) {
        const numericIds = (ids || []).map(Number);
        facilityOptionList.querySelectorAll('input[type="checkbox"]').forEach(function (cb) {
            cb.checked = numericIds.includes(Number(cb.value));
        });
        renderFacilitySelection();
    }
    window.setCheckedFacilities = setCheckedFacilities;

    document.querySelectorAll('.form-delete-room-type').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const name = this.dataset.name;
            const currentForm = this;

            Swal.fire({
                title: `Delete "${name}"?`,
                text: "This action cannot be undone.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#DC2626',
                cancelButtonColor: '#9CA3AF',
                confirmButtonText: 'Yes, delete it'
            }).then(function (result) {
                if (result.isConfirmed) {
                    currentForm.submit();
                }
            });
        });
    });

    [formModal, viewModal].forEach(function (modal) {
        modal?.addEventListener('click', function (e) {
            if (e.target === modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        });
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeFormModal();
            closeViewModal();
        }
    });

    const chartEl = document.getElementById('distributionChart');
    const chartLegend = document.getElementById('chartLegend');

    if (chartEl && window.roomTypeChartData && window.roomTypeChartData.labels) {
        const { labels, values, colors } = window.roomTypeChartData;
        const total = values.reduce((a, b) => a + b, 0) || 1;

        new Chart(chartEl, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{ data: values, backgroundColor: colors, borderWidth: 2, borderColor: '#fff' }]
            },
            options: {
                cutout: '68%',
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${Math.round(ctx.parsed / total * 100)}%` } }
                }
            }
        });

        chartLegend.innerHTML = labels.map((l, i) => `
            <div class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full" style="background:${colors[i]}"></span>
                <span class="text-gray-500">${l}</span>
                <span class="font-semibold text-[var(--navy)]">${Math.round(values[i] / total * 100)}%</span>
            </div>
        `).join('');
    }

});
