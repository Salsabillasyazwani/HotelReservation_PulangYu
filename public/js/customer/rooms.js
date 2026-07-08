const roomTypes = window.roomTypesData || [];
const roomsData = window.roomsData || [];

/*format currency*/
function formatCurrency(amount) {
    return 'Rp ' + Number(amount || 0).toLocaleString('id-ID');
}

/*get room type by id*/
function getRoomType(id) {
    return roomTypes.find(t => t.id === id) || { name: 'Unknown', description: '' };
}

/*status badge class*/
function getStatusBadgeClass(status) {
    if (status === 'Available') return 'badge-available';
    if (status === 'Occupied') return 'badge-occupied';
    return 'badge-maintenance';
}

/*status icon*/
function getStatusIcon(status) {
    if (status === 'Available') return 'fa-check-circle';
    if (status === 'Occupied') return 'fa-user-friends';
    return 'fa-tools';
}

/*room image with fallback*/
function getRoomImage(room) {
    return room.image_url || FALLBACK_IMAGE;
}

/*facilities live on RoomType (via facility_room_type pivot), not on Room*/
function getFacilities(room) {
    const type = getRoomType(room.room_type_id);
    return Array.isArray(type.facilities) ? type.facilities : [];
}

/*render recommended rooms*/
function renderRecommendedRooms() {
    const container = document.getElementById('recommendedRooms');
    if (!container) return;

    const availableRooms = roomsData.filter(r => r.status === 'Available').slice(0, 4);

    if (availableRooms.length === 0) {
        container.innerHTML = `<p class="text-sm text-gray-400 col-span-full text-center py-6">Belum ada kamar yang direkomendasikan.</p>`;
        return;
    }

    container.innerHTML = availableRooms.map(room => {
        const type = getRoomType(room.room_type_id);
        return `
            <div class="recommended-card bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 cursor-pointer group" data-id="${room.id}" onclick="openModal(${room.id})">
                <div class="relative h-48 overflow-hidden">
                    <img src="${getRoomImage(room)}" alt="${type.name}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                    <div class="absolute top-3 left-3">
                        <span class="px-3 py-1 bg-gold text-navy text-xs font-bold rounded-full">
                            <i class="fas fa-crown mr-1"></i> Recommended
                        </span>
                    </div>
                    <div class="absolute top-3 right-3">
                        <span class="px-3 py-1 ${getStatusBadgeClass(room.status)} text-white text-xs font-semibold rounded-full flex items-center gap-1">
                            <i class="fas ${getStatusIcon(room.status)}"></i> ${room.status}
                        </span>
                    </div>
                    <div class="absolute bottom-3 left-3">
                        <p class="text-white font-bold text-lg">${formatCurrency(room.price)}</p>
                        <p class="text-gray-300 text-xs">/night</p>
                    </div>
                </div>
                <div class="p-4">
                    <h4 class="font-bold text-navy text-sm truncate">${type.name} Room</h4>
                    <p class="text-xs text-gray-500 mt-1">Room ${room.room_number} • ${room.capacity} Guests</p>
                    <div class="flex items-center gap-1 mt-2">
                        <div class="star-rating text-xs">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                        <span class="text-xs text-gray-400 ml-1">${room.rating ?? '4.5'}</span>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

/*render room cards*/
function renderRooms(rooms) {
    const container = document.getElementById('roomGrid');
    const emptyState = document.getElementById('emptyState');
    const roomCount = document.getElementById('roomCount');

    roomCount.textContent = rooms.length;

    if (rooms.length === 0) {
        container.innerHTML = '';
        emptyState.classList.remove('hidden');
        document.getElementById('loadMoreSection').classList.add('hidden');
        return;
    }

    emptyState.classList.add('hidden');
    document.getElementById('loadMoreSection').classList.remove('hidden');

    container.innerHTML = rooms.map((room, index) => {
        const type = getRoomType(room.room_type_id);
        const isAvailable = room.status === 'Available';
        const isOccupied = room.status === 'Occupied';

        let buttonHTML = '';
        if (isAvailable) {
            buttonHTML = `
                <button onclick="openModal(${room.id}); event.stopPropagation();" class="btn-book w-full py-2.5 rounded-xl text-white text-sm font-semibold flex items-center justify-center gap-2">
                    <i class="fas fa-calendar-check"></i>
                    Book Now
                </button>
            `;
        } else if (isOccupied) {
            buttonHTML = `
                <button disabled class="w-full py-2.5 rounded-xl text-sm font-semibold bg-gray-100 text-gray-400 cursor-not-allowed flex items-center justify-center gap-2">
                    <i class="fas fa-ban"></i>
                    Not Available
                </button>
            `;
        } else {
            buttonHTML = `
                <button disabled class="w-full py-2.5 rounded-xl text-sm font-semibold bg-yellow-50 text-yellow-600 cursor-not-allowed flex items-center justify-center gap-2">
                    <i class="fas fa-wrench"></i>
                    Under Maintenance
                </button>
            `;
        }

        return `
            <div class="room-card bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 cursor-pointer animate-fade-in-up" style="animation-delay: ${index * 0.05}s" data-id="${room.id}" onclick="openModal(${room.id})">
                <div class="relative h-52 overflow-hidden">
                    <img src="${getRoomImage(room)}" alt="${type.name} ${room.room_number}" class="room-image w-full h-full object-cover" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent"></div>
                    <div class="absolute top-3 left-3">
                        <span class="px-2.5 py-1 bg-white bg-opacity-90 backdrop-blur-sm text-navy text-xs font-bold rounded-lg">
                            ${type.name}
                        </span>
                    </div>
                    <div class="absolute top-3 right-3">
                        <span class="px-2.5 py-1 ${getStatusBadgeClass(room.status)} text-white text-xs font-semibold rounded-lg flex items-center gap-1.5">
                            <i class="fas ${getStatusIcon(room.status)}"></i>
                            ${room.status}
                        </span>
                    </div>
                    <div class="absolute bottom-3 left-3">
                        <div class="px-3 py-1.5 bg-navy bg-opacity-80 backdrop-blur-sm rounded-lg">
                            <p class="text-gold font-bold text-lg">${formatCurrency(room.price)}</p>
                            <p class="text-gray-300 text-xs">per night</p>
                        </div>
                    </div>
                </div>
                <div class="p-5">
                    <div class="flex items-start justify-between mb-2">
                        <div>
                            <h3 class="font-bold text-navy text-base">${type.name} Room</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Room ${room.room_number}</p>
                        </div>
                        <div class="star-rating text-xs flex items-center gap-0.5">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 mt-3 text-xs text-gray-500">
                        <span class="flex items-center gap-1">
                            <i class="fas fa-users text-gold"></i>
                            ${room.capacity} Guests
                        </span>
                        <span class="flex items-center gap-1">
                            <i class="fas fa-bed text-gold"></i>
                            ${type.name}
                        </span>
                        <span class="flex items-center gap-1">
                            <i class="fas fa-wifi text-gold"></i>
                            Free WiFi
                        </span>
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-100">
                        ${buttonHTML}
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

/*filter rooms*/
function filterRooms() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const typeFilter = document.getElementById('typeFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;

    let filtered = roomsData.filter(room => {
        const type = getRoomType(room.room_type_id);
        const matchSearch = !search ||
            type.name.toLowerCase().includes(search) ||
            String(room.room_number).includes(search) ||
            room.status.toLowerCase().includes(search);
        const matchType = !typeFilter || type.name === typeFilter;
        const matchStatus = !statusFilter || room.status === statusFilter;

        return matchSearch && matchType && matchStatus;
    });

    renderRooms(filtered);
    updateActiveFilters(search, typeFilter, statusFilter);
}

/*active filter chips*/
function updateActiveFilters(search, type, status) {
    const container = document.getElementById('activeFilters');
    let chips = [];

    if (search) chips.push({ label: `Search: "${search}"`, type: 'search' });
    if (type) chips.push({ label: `Type: ${type}`, type: 'type' });
    if (status) chips.push({ label: `Status: ${status}`, type: 'status' });

    if (chips.length === 0) {
        container.innerHTML = '';
        return;
    }

    container.innerHTML = chips.map(chip => `
        <span class="filter-chip inline-flex items-center gap-2 px-3 py-1.5 bg-navy bg-opacity-5 rounded-full text-xs font-medium text-navy border border-navy border-opacity-10">
            ${chip.label}
            <button onclick="clearFilter('${chip.type}')" class="ml-1 text-navy hover:text-red-500 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </span>
    `).join('');
}

/*clear specific filter*/
function clearFilter(type) {
    if (type === 'search') document.getElementById('searchInput').value = '';
    if (type === 'type') document.getElementById('typeFilter').value = '';
    if (type === 'status') document.getElementById('statusFilter').value = '';
    filterRooms();
}

/*reset all filters*/
function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('typeFilter').value = '';
    document.getElementById('statusFilter').value = '';
    filterRooms();
}

/*open modal*/
function openModal(roomId) {
    const room = roomsData.find(r => r.id === roomId);
    if (!room) return;

    const type = getRoomType(room.room_type_id);
    const isAvailable = room.status === 'Available';

    let buttonHTML = '';
    if (isAvailable) {
        buttonHTML = `
            <button onclick="bookRoom(${room.id})" class="btn-book-gold px-8 py-3.5 rounded-xl text-white font-bold text-sm flex items-center justify-center gap-2 shadow-lg">
                <i class="fas fa-calendar-check"></i>
                Book Now - ${formatCurrency(room.price)}
            </button>
        `;
    } else if (room.status === 'Occupied') {
        buttonHTML = `
            <button disabled class="px-8 py-3.5 rounded-xl text-sm font-semibold bg-gray-100 text-gray-400 cursor-not-allowed flex items-center justify-center gap-2">
                <i class="fas fa-ban"></i>
                Currently Occupied
            </button>
        `;
    } else {
        buttonHTML = `
            <button disabled class="px-8 py-3.5 rounded-xl text-sm font-semibold bg-yellow-50 text-yellow-600 cursor-not-allowed flex items-center justify-center gap-2">
                <i class="fas fa-wrench"></i>
                Under Maintenance
            </button>
        `;
    }

    const facilities = getFacilities(room);
    const facilitiesHTML = facilities.length
        ? facilities.map(f => `
            <div class="flex items-center gap-2 px-3 py-2 bg-hotelGray rounded-lg">
                <i class="fas fa-check text-gold text-xs"></i>
                <span class="text-sm text-gray-700">${f}</span>
            </div>
        `).join('')
        : `<p class="text-sm text-gray-400 col-span-full">Belum ada data fasilitas.</p>`;

    document.getElementById('modalContent').innerHTML = `
        <div class="relative h-64 sm:h-80 lg:h-96 overflow-hidden rounded-t-2xl">
            <img src="${getRoomImage(room)}" alt="${type.name}" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
            <div class="absolute bottom-0 left-0 right-0 p-6">
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-3 py-1 ${getStatusBadgeClass(room.status)} text-white text-xs font-semibold rounded-full flex items-center gap-1.5">
                        <i class="fas ${getStatusIcon(room.status)}"></i>
                        ${room.status}
                    </span>
                    <span class="px-3 py-1 bg-white bg-opacity-20 backdrop-blur-sm text-white text-xs font-semibold rounded-full">
                        ${type.name}
                    </span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-display font-bold text-white">${type.name} Room</h2>
                <div class="flex items-center gap-4 mt-2">
                    <span class="text-gray-300 text-sm"><i class="fas fa-door-open mr-1"></i> Room ${room.room_number}</span>
                    <span class="text-gray-300 text-sm"><i class="fas fa-users mr-1"></i> ${room.capacity} Guests</span>
                </div>
            </div>
        </div>

        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <p class="text-3xl font-bold text-gold">${formatCurrency(room.price)}</p>
                    <p class="text-sm text-gray-500">per night</p>
                </div>
                <div class="star-rating">
                    <i class="fas fa-star text-lg"></i>
                    <i class="fas fa-star text-lg"></i>
                    <i class="fas fa-star text-lg"></i>
                    <i class="fas fa-star text-lg"></i>
                    <i class="fas fa-star-half-alt text-lg"></i>
                    <span class="text-navy font-semibold ml-1">${room.rating ?? '4.5'}</span>
                </div>
            </div>

            <div class="mb-6">
                <h3 class="font-bold text-navy text-lg mb-2">Description</h3>
                <p class="text-gray-600 text-sm leading-relaxed">${room.description ?? 'Belum ada deskripsi.'}</p>
            </div>

            <div class="mb-6">
                <h3 class="font-bold text-navy text-lg mb-3">Room Information</h3>
                <div class="grid grid-cols-2 gap-3">
                    <div class="flex items-center gap-3 p-3 bg-hotelGray rounded-xl">
                        <div class="w-8 h-8 bg-navy bg-opacity-5 rounded-lg flex items-center justify-center">
                            <i class="fas fa-door-open text-gold text-sm"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Room Number</p>
                            <p class="font-semibold text-navy text-sm">${room.room_number}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-3 bg-hotelGray rounded-xl">
                        <div class="w-8 h-8 bg-navy bg-opacity-5 rounded-lg flex items-center justify-center">
                            <i class="fas fa-tag text-gold text-sm"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Room Type</p>
                            <p class="font-semibold text-navy text-sm">${type.name}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-3 bg-hotelGray rounded-xl">
                        <div class="w-8 h-8 bg-navy bg-opacity-5 rounded-lg flex items-center justify-center">
                            <i class="fas fa-users text-gold text-sm"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Capacity</p>
                            <p class="font-semibold text-navy text-sm">${room.capacity} Guests</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-3 bg-hotelGray rounded-xl">
                        <div class="w-8 h-8 bg-navy bg-opacity-5 rounded-lg flex items-center justify-center">
                            <i class="fas fa-circle text-sm ${room.status === 'Available' ? 'text-green-500' : room.status === 'Occupied' ? 'text-red-500' : 'text-yellow-500'}"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Status</p>
                            <p class="font-semibold text-navy text-sm">${room.status}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-8">
                <h3 class="font-bold text-navy text-lg mb-3">Facilities & Amenities</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    ${facilitiesHTML}
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-gray-100">
                ${buttonHTML}
                <button class="flex-1 py-3.5 rounded-xl border-2 border-navy text-navy font-bold text-sm hover:bg-navy hover:text-white transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-phone-alt"></i>
                    Contact Front Desk
                </button>
            </div>
        </div>
    `;

    document.getElementById('roomModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

/*close modal*/
function closeModal() {
    document.getElementById('roomModal').classList.remove('active');
    document.body.style.overflow = '';
}

/*book room, redirect ke my reservations dengan room_id preselected*/
function bookRoom(roomId) {
    window.location.href = `${window.reservationsUrl}?room_id=${roomId}`;
}

/*set view (grid / list)*/
function setView(view) {
    const grid = document.getElementById('roomGrid');
    const gridBtn = document.getElementById('gridViewBtn');
    const listBtn = document.getElementById('listViewBtn');

    if (view === 'grid') {
        grid.className = 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mt-6';
        gridBtn.className = 'p-2 rounded-lg bg-navy text-white transition-all';
        listBtn.className = 'p-2 rounded-lg bg-white text-gray-400 hover:text-navy transition-all border border-gray-200';
    } else {
        grid.className = 'grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6';
        listBtn.className = 'p-2 rounded-lg bg-navy text-white transition-all';
        gridBtn.className = 'p-2 rounded-lg bg-white text-gray-400 hover:text-navy transition-all border border-gray-200';
    }
}

/*load more (belum terhubung ke backend)*/
function loadMore() {
    console.log('loadMore: hook this up to backend pagination');
}

/*highlight card dari hasil global search, dipanggil setelah card di-render*/
function highlightFromSearch() {
    const highlightId = new URLSearchParams(window.location.search).get('highlight');
    if (!highlightId) return;

    const target = document.querySelector(`[data-id="${highlightId}"]`);
    if (!target) return;

    setTimeout(() => {
        target.scrollIntoView({ behavior: 'smooth', block: 'center' });
        target.classList.add('search-highlight');
        setTimeout(() => target.classList.remove('search-highlight'), 2600);
    }, 300);
}

/*close modal on escape*/
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeModal();
});

/*initialize*/
document.addEventListener('DOMContentLoaded', () => {
    renderRecommendedRooms();
    renderRooms(roomsData);
    highlightFromSearch();
});
