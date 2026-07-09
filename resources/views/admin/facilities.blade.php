@extends('layouts.admin')
@section('title', 'Facilities')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/admin/facilities.css') }}">
@endpush

@section('content')
<div class="facilities-page px-6 py-6 max-w-[1600px] mx-auto">

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-navy mb-1">Facilities</h1>
            <p class="text-sm text-slate-500">Manage hotel facilities available for each room type.</p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <button id="btnExportExcel" type="button" class="btn-outline">
                <i class="bi bi-file-earmark-excel text-emerald-600"></i> Export Excel
            </button>
            <button id="btnExportPdf" type="button" class="btn-outline">
                <i class="bi bi-file-earmark-pdf text-rose-600"></i> Export PDF
            </button>
            <button id="btnAddFacility" type="button" class="btn-primary">
                <i class="bi bi-plus-lg"></i> Add Facility
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm px-4 py-3">
            {{ session('success') }}
        </div>
    @endif

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-top">
                <div class="stat-icon blue">
                    <i class="bi bi-building"></i>
                </div>
                <span class="stat-menu">⋮</span>
            </div>
            <div class="stat-label">Total Facilities</div>
            <div class="stat-value">{{ $totalFacilities }}</div>
            <div class="stat-trend">All facilities</div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <div class="stat-icon green">
                    <i class="bi bi-check-circle"></i>
                </div>
                <span class="stat-menu">⋮</span>
            </div>
            <div class="stat-label">Active Facilities</div>
            <div class="stat-value">{{ $activeFacilities }}</div>
            <div class="stat-trend">Currently active</div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <div class="stat-icon orange">
                    <i class="bi bi-pause-circle"></i>
                </div>
                <span class="stat-menu">⋮</span>
            </div>
            <div class="stat-label">Inactive Facilities</div>
            <div class="stat-value">{{ $inactiveFacilities }}</div>
            <div class="stat-trend">Currently inactive</div>
        </div>

        <div class="stat-card">
            <div class="stat-top">
                <div class="stat-icon purple">
                    <i class="bi bi-star-fill"></i>
                </div>
                <span class="stat-menu">⋮</span>
            </div>
            <div class="stat-label">Most Used Facility</div>
            <div class="stat-value">{{ $mostUsedFacility ?: '-' }}</div>
            <div class="stat-trend">Used in {{ $mostUsedFacilityCount ?? 0 }} Room Types</div>
        </div>
    </div>

    <div class="filter-bar">
        <label class="text-sm font-medium text-slate-600">Status:</label>

        <select id="filterStatus" class="filter-select">
            <option value="">All</option>
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
        </select>

        <button id="btnFilter" type="button" class="btn-dark">
            <i class="bi bi-funnel"></i> Filter
        </button>

        <button id="btnReset" type="button" class="btn-outline">
            <i class="bi bi-arrow-clockwise"></i> Reset
        </button>
    </div>

    <div class="table-card">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="table-head-row">
                        <th class="table-th">No</th>
                        <th class="table-th">Facility Name</th>
                        <th class="table-th">Description</th>
                        <th class="table-th">Used by Room Types</th>
                        <th class="table-th">Status</th>
                        <th class="table-th text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($facilities as $index => $facility)
                    <tr class="facility-row table-row" data-facility-id="{{ $facility->id }}" data-status="{{ $facility->status }}">
                        <td class="table-td text-slate-500">{{ $index + 1 }}</td>
                        <td class="table-td font-semibold text-slate-800">{{ $facility->name }}</td>
                        <td class="table-td text-slate-500">{{ $facility->description ?: '-' }}</td>
                        <td class="table-td">
                            <div class="flex flex-wrap gap-1">
                                @forelse ($facility->roomTypes->take(3) as $roomType)
                                    <span class="pill pill-blue">{{ $roomType->name }}</span>
                                @empty
                                    <span class="text-slate-400 text-xs">-</span>
                                @endforelse
                                @if($facility->roomTypes->count() > 3)
                                    <span class="pill pill-gray">+{{ $facility->roomTypes->count() - 3 }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="table-td">
                            @if($facility->status === 'Active')
                                <span class="pill pill-green">Active</span>
                            @else
                                <span class="pill pill-red">Inactive</span>
                            @endif
                        </td>
                        <td class="table-td">
                            <div class="flex justify-center gap-1">
                                <button type="button" onclick="openDrawer({{ $facility->id }})" class="action-btn text-royal" title="View Detail">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button type="button" onclick="openEditModal({{ $facility->id }})" class="action-btn text-amber-500" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button type="button" onclick="confirmDelete({{ $facility->id }})" class="action-btn text-rose-500" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-slate-400 py-8">No facilities found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<div id="detailModalFacility" class="modal-overlay hidden">
    <div class="modal-panel">
        <div class="modal-header">
            <h5 class="font-bold text-lg">Facility Detail</h5>
            <button type="button" onclick="closeModal('detailModalFacility')" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <p class="label-uppercase">Facility Name</p>
            <h4 id="drawerTitle" class="text-xl font-bold mb-4"></h4>

            <p class="label-uppercase">Description</p>
            <p id="drawerDescription" class="bg-slate-50 border rounded-lg p-3 mb-4 text-sm"></p>

            <p class="label-uppercase mb-2">Status</p>
            <div id="drawerStatus" class="mb-4"></div>

            <p class="label-uppercase mb-2">Used by Room Types</p>
            <div id="drawerRoomTypes" class="grid grid-cols-2 gap-2"></div>
        </div>
    </div>
</div>

<div id="addModal" class="modal-overlay hidden">
    <div class="modal-panel">
        <form id="addFacilityForm" action="{{ route('admin.facilities.store') }}" method="POST">
            @csrf
            <div class="modal-header">
                <h5 class="font-bold text-lg">Add Facility</h5>
                <button type="button" onclick="closeModal('addModal')" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Facility Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" class="form-input" placeholder="Enter facility name">
                    @error('name') <div class="text-rose-500 text-xs mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="3" class="form-input" placeholder="Enter facility description..."></textarea>
                </div>

                <div>
                    <label class="form-label block mb-2">Status <span class="text-rose-500">*</span></label>
                    <div class="flex gap-6">
                        <label class="radio-label">
                            <input type="radio" name="status" value="Active" checked> Active
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="status" value="Inactive"> Inactive
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('addModal')" class="btn-outline">Cancel</button>
                <button type="submit" class="btn-primary">Save Facility</button>
            </div>
        </form>
    </div>
</div>

<div id="editModal" class="modal-overlay hidden">
    <div class="modal-panel">
        <form id="editFacilityForm" action="{{ url('admin/facilities') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="font-bold text-lg">Edit Facility</h5>
                <button type="button" onclick="closeModal('editModal')" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Facility Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" id="editName" class="form-input">
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="editDescription" rows="3" class="form-input"></textarea>
                </div>

                <div>
                    <label class="form-label block mb-2">Status <span class="text-rose-500">*</span></label>
                    <div class="flex gap-6">
                        <label class="radio-label">
                            <input type="radio" name="status" value="Active" id="editStatusActive"> Active
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="status" value="Inactive" id="editStatusInactive"> Inactive
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('editModal')" class="btn-outline">Cancel</button>
                <button type="submit" class="btn-primary">Update Facility</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
    @php
        $facilitiesData = $facilities->map(function ($f) {
            return [
                'id'          => $f->id,
                'name'        => $f->name,
                'description' => $f->description,
                'status'      => $f->status,
                'room_types'  => $f->roomTypes->map(function ($rt) {
                    return ['id' => $rt->id, 'name' => $rt->name];
                }),
            ];
        });
    @endphp
    <script>
        window.FacilitiesConfig = {
            storeUrl: "{{ route('admin.facilities.store') }}",
            updateBaseUrl: "{{ url('admin/facilities') }}",
        };
        window.facilities = @json($facilitiesData);
    </script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.8.2/dist/jspdf.plugin.autotable.min.js"></script>
    <script src="{{ asset('js/admin/facilities.js') }}"></script>
@endpush
