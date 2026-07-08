@extends('layouts.admin')

@section('title', 'Room Management')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/room.css') }}">
@endpush

@section('content')
<div class="page-wrap">

    {{-- flash fallback (dipakai room.js kalau Swal ada, boleh dihapus kalau sudah dihandle di layout) --}}
    <div id="flashSuccess" class="hidden" data-message="{{ session('success') }}"></div>

    {{-- ================= HEADER ================= --}}
    <div class="page-header">
        <div class="title-block">
            <h1>Room Management</h1>
            <p>Kelola data kamar fisik, nomor, lantai, dan status.</p>
        </div>

            <button id="openAddRoom" class="btn-add btn-navy">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M12 3.75a.75.75 0 01.75.75v6.75h6.75a.75.75 0 010 1.5h-6.75v6.75a.75.75 0 01-1.5 0v-6.75H4.5a.75.75 0 010-1.5h6.75V4.5a.75.75 0 01.75-.75z" clip-rule="evenodd"/></svg>
                Add Room
            </button>
        </div>
    </div>

    {{-- ================= STAT CARDS ================= --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-top">
                <div class="stat-icon blue">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="7" width="18" height="12" rx="2"/><path d="M3 11h18"/></svg>
                </div>
                <span class="stat-menu">⋮</span>
            </div>
            <div class="stat-label">Total Room</div>
            <div class="stat-value">{{ $totalRoom ?? 0 }}</div>
            <div class="stat-trend">All rooms in hotel</div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <div class="stat-icon green">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="stat-menu">⋮</span>
            </div>
            <div class="stat-label">Available</div>
            <div class="stat-value">{{ $availableRoom ?? 0 }}</div>
            <div class="stat-trend">Rooms available</div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <div class="stat-icon purple">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75"/></svg>
                </div>
                <span class="stat-menu">⋮</span>
            </div>
            <div class="stat-label">Occupied</div>
            <div class="stat-value">{{ $occupiedRoom ?? 0 }}</div>
            <div class="stat-trend">Rooms occupied</div>
        </div>
        <div class="stat-card">
            <div class="stat-top">
                <div class="stat-icon red">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26"/></svg>
                </div>
                <span class="stat-menu">⋮</span>
            </div>
            <div class="stat-label">Maintenance</div>
            <div class="stat-value">{{ $maintenanceRoom ?? 0 }}</div>
            <div class="stat-trend">Under maintenance</div>
        </div>
    </div>

    {{-- ================= FILTER (server-side, submit biasa) ================= --}}
    <form method="GET" class="toolbar card-shadow">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search room number or name..." class="toolbar-search">

        <select name="type">
            <option value="">All Room Type</option>
            @foreach($roomTypes as $roomType)
                <option value="{{ $roomType->id }}" {{ (string) request('type') === (string) $roomType->id ? 'selected' : '' }}>
                    {{ $roomType->name }}
                </option>
            @endforeach
        </select>

        <select name="status">
            <option value="">All Status</option>
            <option value="Available"    {{ request('status') === 'Available' ? 'selected' : '' }}>Available</option>
            <option value="Booked"       {{ request('status') === 'Booked' ? 'selected' : '' }}>Booked</option>
            <option value="Occupied"     {{ request('status') === 'Occupied' ? 'selected' : '' }}>Occupied</option>
            <option value="Maintenance"  {{ request('status') === 'Maintenance' ? 'selected' : '' }}>Maintenance</option>
        </select>

        <select name="floor">
            <option value="">All Floor</option>
            @foreach($floors ?? [] as $floorOption)
                <option value="{{ $floorOption }}" {{ (string) request('floor') === (string) $floorOption ? 'selected' : '' }}>
                    Floor {{ $floorOption }}
                </option>
            @endforeach
        </select>

        <button type="submit" class="btn-filter">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M3.792 2.938A49.069 49.069 0 0112 2.25c2.797 0 5.54.236 8.209.688a1.857 1.857 0 011.541 1.836v1.044a3 3 0 01-.879 2.121l-6.182 6.182a1.5 1.5 0 00-.439 1.061v2.927a3 3 0 01-1.658 2.684l-1.757.878A.75.75 0 019.75 21v-6.616a1.5 1.5 0 00-.44-1.06L3.13 6.995A3 3 0 012.25 4.874V3.83a1.857 1.857 0 011.542-1.836z" clip-rule="evenodd"/></svg>
            Filter
        </button>
        <a href="{{ route('admin.room.index') }}" class="btn-reset">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
            Reset
        </a>
    </form>

    {{-- ================= TABLE ROOMS ================= --}}
    <div class="table-card card-shadow">
        <table class="rooms-table">
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Room Number</th>
                    <th>Room Type</th>
                    <th>Floor</th>
                    <th>Capacity</th>
                    <th>Price / Night</th>
                    <th>Status</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rooms as $room)
                @php
                    $statusClass = match($room->status) {
                        'Available' => 'badge-active',
                        'Occupied' => 'badge-info',
                        'Booked' => 'badge-warning',
                        'Maintenance' => 'badge-inactive',
                        default => 'badge-warning',
                    };
                    $statusLabel = $room->status;
                @endphp
                    <tr class="fade-in">
                        <td>
                        <img class="row-thumb"
                            src="{{ $room->image ? Storage::url($room->image) : asset('images/room-placeholder.jpg') }}"
                            alt="{{ $room->room_number }}">
                        </td>
                        <td class="cell-strong">{{ $room->room_number }}</td>
                        <td class="cell-link">{{ $room->roomType->name ?? $room->room_name ?? '-' }}</td>
                        <td>{{ $room->floor }}</td>
                        <td>{{ $room->capacity }} Person</td>
                        <td>Rp{{ number_format($room->price, 0, ',', '.') }}</td>
                        <td><span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                        <td>
                            <div class="row-actions">
                                <button type="button" class="btn-view row-action-btn" data-room='@json($room)'>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </button>
                                <button type="button" class="btn-edit row-action-btn" data-room='@json($room)'>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z"/></svg>
                                </button>
                                <form action="{{ route('admin.room.destroy', $room->id) }}" method="POST" class="form-delete-room" data-room-number="{{ $room->room_number }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="row-action-btn btn-delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                @endforelse
            </tbody>
        </table>

        @if($rooms->isEmpty())
            <div id="emptyState">
                <p>No rooms found</p>
                <p>Coba ubah filter status / tipe kamar</p>
            </div>
        @endif
    </div>

    <div class="pagination-bar">
        <span>Showing {{ $rooms->firstItem() ?? 0 }} to {{ $rooms->lastItem() ?? 0 }} of {{ $rooms->total() ?? 0 }} rooms</span>
        {{ $rooms->appends(request()->query())->links() ?? '' }}
    </div>
</div>

{{-- ================= MODAL ADD / EDIT (gabung 1) ================= --}}
<div id="addRoomModal" class="modal-overlay hidden">
    <div class="modal-backdrop-layer"></div>
    <div class="modal-panel scrollbar-thin">
        <div class="modal-header">
            <h3 id="roomModalTitle">Add Room</h3>
            <button type="button" id="closeModal" class="modal-close">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M5.47 5.47a.75.75 0 011.06 0L12 10.94l5.47-5.47a.75.75 0 111.06 1.06L13.06 12l5.47 5.47a.75.75 0 11-1.06 1.06L12 13.06l-5.47 5.47a.75.75 0 01-1.06-1.06L10.94 12 5.47 6.53a.75.75 0 010-1.06z" clip-rule="evenodd"/></svg>
            </button>
        </div>

        <form id="roomForm" action="{{ route('admin.room.store') }}" method="POST" enctype="multipart/form-data" class="modal-body">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <input type="hidden" name="id" id="roomId">

            <div class="form-group">
                <label>Room Photo</label>
                <div class="drop-zone">
                    <img id="imagePreview" src="" class="hidden">
                    <input type="file" name="image" id="imageInput" accept="image/*">
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Room Number</label>
                    <input type="text" id="r_room_number" name="room_number" value="{{ old('room_number') }}" required class="input-focus" placeholder="101">
                    @error('room_number') <p class="error-text">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label>Room Name (opsional)</label>
                    <input type="text" id="r_room_name" name="room_name" value="{{ old('room_name') }}" class="input-focus" placeholder="Sunset View">
                    @error('room_name') <p class="error-text">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label>Room Type</label>
                    <select id="r_room_type_id" name="room_type_id" required class="input-focus">
                        <option value="">-- Pilih Room Type --</option>
                        @foreach($roomTypes as $type)
                            <option value="{{ $type->id }}" {{ (string) old('room_type_id') === (string) $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                        @endforeach
                    </select>
                    @error('room_type_id') <p class="error-text">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label>Floor</label>
                    <input type="number" id="r_floor" name="floor" value="{{ old('floor') }}" required class="input-focus" placeholder="2">
                    @error('floor') <p class="error-text">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label>Capacity (Guests)</label>
                    <input type="number" id="r_capacity" name="capacity" value="{{ old('capacity') }}" required class="input-focus" placeholder="2">
                    @error('capacity') <p class="error-text">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label>Price / Night (Rp)</label>
                    <input type="number" id="r_price" name="price" value="{{ old('price') }}" required class="input-focus" placeholder="750000">
                    @error('price') <p class="error-text">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select id="r_status" name="status" required class="input-focus">
                    <option value="Available"   {{ old('status') === 'Available' ? 'selected' : '' }}>Available</option>
                    <option value="Booked"      {{ old('status') === 'Booked' ? 'selected' : '' }}>Booked</option>
                    <option value="Occupied"    {{ old('status') === 'Occupied' ? 'selected' : '' }}>Occupied</option>
                    <option value="Maintenance" {{ old('status') === 'Maintenance' ? 'selected' : '' }}>Maintenance</option>
                </select>
                @error('status') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea id="r_description" name="description" rows="3" class="input-focus">{{ old('description') }}</textarea>
                @error('description') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            <div class="form-actions">
                <button type="button" id="cancelModal" class="btn-cancel">Cancel</button>
                <button type="submit" id="roomSubmitBtn" class="btn-save">Save Room</button>
            </div>
        </form>
    </div>
</div>

{{-- ================= MODAL VIEW DETAIL ================= --}}
<div id="viewRoomModal" class="modal-overlay hidden">
    <div class="modal-backdrop-layer"></div>
    <div class="modal-panel modal-panel-sm scrollbar-thin">
        <div class="modal-header">
            <h3>Room Details</h3>
            <button type="button" id="closeViewModal" class="modal-close">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M5.47 5.47a.75.75 0 011.06 0L12 10.94l5.47-5.47a.75.75 0 111.06 1.06L13.06 12l5.47 5.47a.75.75 0 11-1.06 1.06L12 13.06l-5.47 5.47a.75.75 0 01-1.06-1.06L10.94 12 5.47 6.53a.75.75 0 010-1.06z" clip-rule="evenodd"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <img id="v_img" src="" class="view-image">
            <div class="view-title-row">
                <h4 id="v_room_number"></h4>
                <span id="v_status" class="status-badge"></span>
            </div>
            <p id="v_room_type" class="view-subtitle"></p>
            <p id="v_price" class="view-price"></p>

            <div class="view-stats">
                <div class="stat-box">
                    <p>Floor</p>
                    <p id="v_floor"></p>
                </div>
                <div class="stat-box">
                    <p>Capacity</p>
                    <p id="v_capacity"></p>
                </div>
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
    window.roomRoutes = {
        store: "{{ route('admin.room.store') }}",
        updateBase: "{{ url('admin/rooms') }}"
    };
    window.roomChartData = @json($roomChartData ?? ['labels' => [], 'values' => []]);

    @if ($errors->any())
        window.roomFormErrors = true;
    @endif
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/admin/room.js') }}"></script>
@endpush
