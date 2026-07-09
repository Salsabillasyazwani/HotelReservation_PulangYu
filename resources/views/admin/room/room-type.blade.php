@extends('layouts.admin')

@section('title', 'Room Type Management - Hotel Pulang Yo')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/room-type.css') }}">
@endpush

@section('content')
<div class="page-wrap">

    <div class="page-header">
        <div class="title-block">
            <h1>Room Type Management</h1>
            <p>Manage all hotel room categories.</p>
        </div>
        <button id="openAddModalBtn" class="btn-add btn-navy">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M12 3.75a.75.75 0 01.75.75v6.75h6.75a.75.75 0 010 1.5h-6.75v6.75a.75.75 0 01-1.5 0v-6.75H4.5a.75.75 0 010-1.5h6.75V4.5a.75.75 0 01.75-.75z" clip-rule="evenodd"/></svg>
            Add Room Type
        </button>
    </div>

    <div class="toolbar card-shadow">
        <select id="statusSelect">
            <option value="all">Status: All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>

        <select id="sortSelect">
            <option value="newest">Sort By: Newest</option>
            <option value="oldest">Sort By: Oldest</option>
            <option value="price-asc">Price: Low to High</option>
            <option value="price-desc">Price: High to Low</option>
            <option value="name-asc">Name: A-Z</option>
        </select>

        <button id="resetBtn" class="btn-reset">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
            Reset
        </button>
    </div>

    <div class="table-wrap card-shadow">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Bed Type</th>
                    <th>Size</th>
                    <th>Total Rooms</th>
                    <th>Price / Night</th>
                    <th>Capacity</th>
                    <th>Facilities</th>
                    <th>Status</th>
                    <th class="th-actions">Actions</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                @forelse($roomTypes as $roomType)
                    @php
                        $statusClass = $roomType->status === 'active' ? 'badge-active' : 'badge-inactive';
                        $statusLabel = $roomType->status === 'active' ? 'Active' : 'Inactive';
                        $roomFacilityNames = is_array($roomType->facilities) ? $roomType->facilities : [];
                    @endphp
                    <tr class="data-row"
                        data-id="{{ $roomType->id }}"
                        data-name="{{ strtolower($roomType->name) }}"
                        data-price="{{ $roomType->price }}"
                        data-status="{{ $roomType->status }}"
                        data-created="{{ $roomType->created_at->timestamp }}">

                        <td class="cell-name">{{ $roomType->name }}</td>
                        <td>{{ $roomType->bed_type }}</td>
                        <td>{{ $roomType->room_size }} m²</td>
                        <td>{{ $roomType->total_rooms }}</td>
                        <td>Rp{{ number_format($roomType->price, 0, ',', '.') }}</td>
                        <td>{{ $roomType->capacity }} Guest</td>
                        <td>
                            <div class="facility-tags">
                                @forelse(array_slice($roomFacilityNames, 0, 2) as $f)
                                    <span class="tag">{{ $f }}</span>
                                @empty
                                    <span class="tag-empty">-</span>
                                @endforelse
                                @if(count($roomFacilityNames) > 2)
                                    <span class="tag-more">+{{ count($roomFacilityNames) - 2 }}</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="status-badge {{ $statusClass }}">
                                <span class="dot {{ $roomType->status === 'active' ? 'active' : 'inactive' }}"></span>{{ $statusLabel }}
                            </span>
                        </td>
                        <td class="cell-actions">
                            <button type="button" class="btn-view icon-btn" data-room='@json($roomType)' title="View">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </button>
                            <button type="button" class="btn-edit icon-btn" data-room='@json($roomType)' title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z"/></svg>
                            </button>
                            <form action="{{ route('admin.room-types.destroy', $roomType->id) }}" method="POST" class="form-delete-room-type" data-name="{{ $roomType->name }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="icon-btn" title="Delete">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                @endforelse
            </tbody>
        </table>

        <div id="emptyState" class="hidden">
            <p>No room types found</p>
            <p>Try adjusting your search or filter</p>
        </div>
    </div>

    <div class="pagination-bar">
        <span id="showingInfo">
            Showing {{ $roomTypes->firstItem() ?? 0 }} to {{ $roomTypes->lastItem() ?? 0 }} of {{ $roomTypes->total() ?? 0 }} room types
        </span>
        {{ $roomTypes->links() ?? '' }}
    </div>
</div>

<div id="formModal" class="modal-overlay hidden">
    <div class="modal-backdrop-layer"></div>
    <div class="modal-panel scrollbar-thin">
        <div class="modal-header">
            <h3 id="formModalTitle">Add Room Type</h3>
            <button type="button" onclick="closeFormModal()" class="modal-close">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M5.47 5.47a.75.75 0 011.06 0L12 10.94l5.47-5.47a.75.75 0 111.06 1.06L13.06 12l5.47 5.47a.75.75 0 11-1.06 1.06L12 13.06l-5.47 5.47a.75.75 0 01-1.06-1.06L10.94 12 5.47 6.53a.75.75 0 010-1.06z" clip-rule="evenodd"/></svg>
            </button>
        </div>

        <form id="roomForm" action="{{ route('admin.room-types.store') }}" method="POST" class="modal-body">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <input type="hidden" name="id" id="roomId">

            <div class="form-grid">
                <div class="form-group">
                    <label>Room Type Name</label>
                    <input name="name" id="f_name" type="text" required class="input-focus" placeholder="e.g. Deluxe Room">
                    @error('name') <p class="error-text">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label>Bed Type</label>
                    <input name="bed_type" id="f_bed" type="text" required class="input-focus" placeholder="King Bed">
                    @error('bed_type') <p class="error-text">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label>Room Size (m²)</label>
                    <input name="room_size" id="f_size" type="number" required class="input-focus" placeholder="32">
                    @error('room_size') <p class="error-text">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="form-group">
                <label>Facilities</label>
                <div class="facility-picker" id="facilityPicker">
                    <button type="button" class="facility-picker-trigger" id="facilityTrigger">
                        <span id="facilitySelectedText">Pilih fasilitas...</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="chevron">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
                        </svg>
                    </button>

                    <div class="facility-picker-panel hidden" id="facilityPanel">
                        <input type="text" id="facilitySearchInput" class="facility-search-input" placeholder="Cari fasilitas...">

                        <div class="facility-option-list" id="facilityOptionList">
                            @forelse($facilities as $facility)
                                <label class="facility-option">
                                    <input type="checkbox" name="facilities[]" value="{{ $facility->id }}" data-name="{{ strtolower($facility->name) }}">
                                    <span>{{ $facility->name }}</span>
                                </label>
                            @empty
                                <p class="facility-empty-note">Belum ada fasilitas. Tambahkan di bawah.</p>
                            @endforelse
                        </div>

                        <div class="facility-add-row">
                            <input type="text" id="newFacilityInput" class="facility-add-input" placeholder="Tambah fasilitas baru...">
                            <button type="button" id="addFacilityBtn" class="facility-add-btn">+ Tambah</button>
                        </div>
                    </div>
                </div>

                <div class="facility-tags-preview" id="facilitySelectedTags"></div>
                @error('facilities') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="f_desc" rows="3" class="input-focus" placeholder="Room description..."></textarea>
                @error('description') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            <div class="toggle-row">
                <label>Status</label>
                <label class="toggle-switch">
                    <input type="checkbox" id="f_status" checked>
                    <input type="hidden" name="status" id="f_status_hidden" value="active">
                    <span class="toggle-track"></span>
                    <span class="toggle-thumb"></span>
                    <span id="f_status_label" class="toggle-label">Active</span>
                </label>
            </div>

            <p class="form-note">
                Price, Capacity, and Total Rooms are calculated automatically from rooms
                under this type. Manage them in <strong>Room Management</strong>.
            </p>

            <div class="form-actions">
                <button type="button" onclick="closeFormModal()" class="btn-cancel">Cancel</button>
                <button type="submit" class="btn-save">Save Room Type</button>
            </div>
        </form>
    </div>
</div>

<div id="viewModal" class="modal-overlay hidden">
    <div class="modal-backdrop-layer"></div>
    <div class="modal-panel modal-panel-sm scrollbar-thin">
        <div class="modal-header">
            <h3>Room Type Details</h3>
            <button type="button" onclick="closeViewModal()" class="modal-close">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M5.47 5.47a.75.75 0 011.06 0L12 10.94l5.47-5.47a.75.75 0 111.06 1.06L13.06 12l5.47 5.47a.75.75 0 11-1.06 1.06L12 13.06l-5.47 5.47a.75.75 0 01-1.06-1.06L10.94 12 5.47 6.53a.75.75 0 010-1.06z" clip-rule="evenodd"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <img id="v_img" src="" class="view-image">
            <div class="view-title-row">
                <h4 id="v_name"></h4>
                <span id="v_status" class="text-xs font-medium"></span>
            </div>
            <p id="v_price" class="view-price"></p>

            <div class="view-stats">
                <div class="stat-box">
                    <p>Capacity</p>
                    <p id="v_capacity"></p>
                </div>
                <div class="stat-box">
                    <p>Bed Type</p>
                    <p id="v_bed"></p>
                </div>
                <div class="stat-box">
                    <p>Room Size</p>
                    <p id="v_size"></p>
                </div>
                <div class="stat-box">
                    <p>Total Rooms</p>
                    <p id="v_total"></p>
                </div>
            </div>

            <div class="view-section">
                <p class="section-label">Facilities</p>
                <div id="v_facilities" class="view-facilities"></div>
            </div>

            <div class="view-section">
                <p class="section-label">Description</p>
                <p id="v_desc" class="view-desc"></p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.roomTypeRoutes = {
        store: "{{ route('admin.room-types.store') }}",
        updateBase: "{{ url('admin/room-types') }}"
    };
    window.facilityStoreRoute = "{{ route('admin.facilities.quick-store') }}";
    window.roomTypeChartData = @json($chartData ?? ['labels' => [], 'values' => [], 'colors' => []]);
</script>
<script src="{{ asset('js/admin/room-type.js') }}"></script>
@endpush
