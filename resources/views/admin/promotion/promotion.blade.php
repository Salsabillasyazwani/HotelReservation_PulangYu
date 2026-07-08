@extends('layouts.admin')

@section('title', 'Promotion Management')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/promotion.css') }}">
@endpush

@section('content')

@if (session('success'))
<div class="toast show" id="serverToast"><i class="bi bi-check2-circle"></i><span>{{ session('success') }}</span></div>
@endif

@if ($errors->any() && old('form_source') === 'add')
<script>window.reopenAddModalOnLoad = true;</script>
@endif

<div class="page-header">
    <div>
        <h2>Promotion Management</h2>
        <p>Manage hotel promotions, vouchers, seasonal discounts, flash sales and special offers.</p>
    </div>

    <div class="head-actions">
        <div class="dropdown">
            <button class="btn ghost" id="exportBtn" type="button" aria-haspopup="true" aria-expanded="false">
                <i class="bi bi-box-arrow-up"></i>Export <i class="bi bi-chevron-down caret"></i>
            </button>
            <div class="menu" id="exportMenu" role="menu" aria-label="Export menu">
                <a class="mi excel" role="menuitem" href="{{ route('admin.promotions.export', ['type' => 'excel']) }}">
                    <i class="bi bi-file-earmark-excel"></i>Export Excel
                </a>
                <a class="mi pdf" role="menuitem" href="{{ route('admin.promotions.export', ['type' => 'pdf']) }}">
                    <i class="bi bi-file-earmark-pdf"></i>Export PDF
                </a>
                <a class="mi csv" role="menuitem" href="{{ route('admin.promotions.export', ['type' => 'csv']) }}">
                    <i class="bi bi-filetype-csv"></i>Export CSV
                </a>
            </div>
        </div>

        <button class="btn primary" id="addBtn" type="button">
            <i class="bi bi-plus-lg"></i>+ Add Promotion
        </button>
    </div>
</div>

<div class="stats-grid" aria-label="Statistics">

    <div class="stat-card">
        <div class="stat-top">
            <div class="stat-icon blue">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
                    <rect x="3" y="8" width="18" height="13" rx="2"/>
                    <path d="M3 8h18"/>
                    <path d="M12 8v13"/>
                    <path d="M12 8c-2-4-8-3-6 0s6 0 6 0zM12 8c2-4 8-3 6 0s-6 0-6 0z"/>
                </svg>
            </div>
            <span class="stat-menu">⋮</span>
        </div>
        <div class="stat-label">Total Promotions</div>
        <div class="stat-value">{{ $totalPromotion ?? 0 }}</div>
        <div class="stat-trend">All promotions</div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <div class="stat-icon success">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="M8 12.5l2.5 2.5L16 9"/>
                </svg>
            </div>
            <span class="stat-menu">⋮</span>
        </div>
        <div class="stat-label">Active Promotions</div>
        <div class="stat-value">{{ $activePromotion ?? 0 }}</div>
        <div class="stat-trend">Currently active</div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <div class="stat-icon warn">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="M12 7v5l4 2"/>
                </svg>
            </div>
            <span class="stat-menu">⋮</span>
        </div>
        <div class="stat-label">Ending Soon</div>
        <div class="stat-value">{{ $endingPromotion ?? 0 }}</div>
        <div class="stat-trend">Will end soon</div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <div class="stat-icon danger">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
                    <circle cx="12" cy="12" r="9"/>
                    <path d="M9 9l6 6M15 9l-6 6"/>
                </svg>
            </div>
            <span class="stat-menu">⋮</span>
        </div>
        <div class="stat-label">Expired Promotions</div>
        <div class="stat-value">{{ $expiredPromotion ?? 0 }}</div>
        <div class="stat-trend">Already expired</div>
    </div>

</div>

<form method="GET" action="{{ route('admin.promotions.index') }}" class="filters" id="filterForm" aria-label="Filters">
    <input type="hidden" name="q" value="{{ request('q') }}">

    <div class="field">
        <select name="type">
            <option value="">Promotion Type</option>
            <option value="All" @selected(request('type') == 'All')>All Type</option>
            <option value="Percentage" @selected(request('type') == 'Percentage')>Percentage</option>
            <option value="Voucher" @selected(request('type') == 'Voucher')>Voucher</option>
            <option value="Fixed Amount" @selected(request('type') == 'Fixed Amount')>Fixed Amount</option>
        </select>
    </div>

    <div class="field">
        <select name="status">
            <option value="">Status</option>
            <option value="All" @selected(request('status') == 'All')>All Status</option>
            <option value="Active" @selected(request('status') == 'Active')>Active</option>
            <option value="Upcoming" @selected(request('status') == 'Upcoming')>Upcoming</option>
            <option value="Inactive" @selected(request('status') == 'Inactive')>Inactive</option>
            <option value="Expired" @selected(request('status') == 'Expired')>Expired</option>
        </select>
    </div>

    <div class="daterange">
        <div class="field">
            <input type="date" name="date_from" value="{{ request('date_from') }}">
        </div>
        <div class="dash">—</div>
        <div class="field">
            <input type="date" name="date_to" value="{{ request('date_to') }}">
        </div>
    </div>

    <button class="btn primary filter" type="submit"><i class="bi bi-funnel"></i>Filter</button>
    <a class="btn reset" href="{{ route('admin.promotions.index') }}"><i class="bi bi-arrow-counterclockwise"></i>Reset</a>
</form>

<div class="table-wrap" id="tableWrap" @if($promotions->isEmpty()) style="display:none" @endif>

    {{-- FIX TABEL KEPOTONG: sebelumnya .table-wrap pakai overflow:hidden,
         jadi begitu tabel lebih lebar dari container, kolom-kolom di
         kanan (mis. Action) ke-crop dan tidak bisa di-scroll sama sekali.
         Sekarang <table> dibungkus .table-scroll (overflow-x:auto),
         sementara .table-wrap tetap overflow:hidden hanya untuk menjaga
         border-radius di bagian luar tetap rapi. Table-foot (pagination)
         sengaja diletakkan DI LUAR .table-scroll supaya dia tidak ikut
         ke-scroll horizontal dan selalu full-width. --}}
    <div class="table-scroll">
    <table aria-label="Promotion Table">
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th>Promotion Name</th>
                <th>Promo Code</th>
                <th>Promotion Type</th>
                <th>Discount</th>
                <th>Minimum Purchase</th>
                <th>Maximum Discount</th>
                <th>Valid Period</th>
                <th>Status</th>
                <th style="text-align:left">Action</th>
            </tr>
        </thead>
        <tbody id="tbody">
            @forelse ($promotions as $promotion)
                @php
                    $statusValue = $promotion->computed_status;
                    $statusClass = match($statusValue) {
                        'Active'   => 'success',
                        'Upcoming' => 'warn',
                        'Expired'  => 'danger',
                        default    => 'inactive',
                    };

                    // room_type_options: hasil resolve id room type -> [{id, name}, ...]
                    // lewat accessor di Model Promotion (query ke tabel room_types).
                    $roomTypeOptions = $promotion->room_type_options;
                    $roomNames = collect($roomTypeOptions)->pluck('name')->all();
                    $roomIds   = collect($roomTypeOptions)->pluck('id')->all();

                    $validPeriod = $promotion->start_date && $promotion->end_date
                        ? $promotion->start_date->format('d M') . '–' . $promotion->end_date->format('d M Y')
                        : '-';
                    // $bannerUrl tetap dihitung (dipakai lewat data-banner di bawah)
                    // supaya gambar tetap bisa ditampilkan di modal View/Edit,
                    // hanya saja tidak lagi dirender sebagai kolom <img> di tabel.
                    $bannerUrl = $promotion->banner ? asset('storage/' . $promotion->banner) : asset('images/no-banner.png');
                @endphp
                <tr data-id="{{ $promotion->id }}">
                    <td class="col-no">{{ $promotions->firstItem() + $loop->index }}</td>
                    <td>{{ $promotion->promo_name }}</td>
                    <td class="muted">{{ $promotion->promo_code }}</td>
                    <td>{{ $promotion->discount_type }}</td>
                    <td>{{ $promotion->discount_value }}</td>
                    <td>{{ $promotion->minimum_booking }}</td>
                    <td>{{ $promotion->maximum_discount ?? '-' }}</td>
                    <td>{{ $validPeriod }}</td>
                    <td><span class="chip {{ $statusClass }}">{{ $statusValue }}</span></td>
                    <td>
                        <div class="actions">
                            <button type="button" class="act eye" title="View" aria-label="View"
                                data-action="view"
                                data-id="{{ $promotion->id }}"
                                data-name="{{ $promotion->promo_name }}"
                                data-code="{{ $promotion->promo_code }}"
                                data-type="{{ $promotion->discount_type }}"
                                data-discount="{{ $promotion->discount_value }}"
                                data-min="{{ $promotion->minimum_booking }}"
                                data-max="{{ $promotion->maximum_discount }}"
                                data-rooms="{{ implode(',', $roomNames) }}"
                                data-limit="{{ $promotion->quota }}"
                                data-period="{{ $validPeriod }}"
                                data-status="{{ $statusValue }}"
                                data-desc="{{ $promotion->description }}"
                                data-banner="{{ $bannerUrl }}">
                                <i class="bi bi-eye"></i>
                            </button>

                            <button type="button" class="act edit" title="Edit" aria-label="Edit"
                                data-action="edit"
                                data-id="{{ $promotion->id }}"
                                data-name="{{ $promotion->promo_name }}"
                                data-code="{{ $promotion->promo_code }}"
                                data-type="{{ $promotion->discount_type }}"
                                data-discount="{{ $promotion->discount_value }}"
                                data-min="{{ $promotion->minimum_booking }}"
                                data-max="{{ $promotion->maximum_discount }}"
                                data-rooms="{{ implode(',', $roomNames) }}"
                                data-room-ids="{{ implode(',', $roomIds) }}"
                                data-limit="{{ $promotion->quota }}"
                                data-start="{{ optional($promotion->start_date)->format('Y-m-d') }}"
                                data-end="{{ optional($promotion->end_date)->format('Y-m-d') }}"
                                data-status="{{ $promotion->status }}"
                                data-desc="{{ $promotion->description }}"
                                data-banner="{{ $bannerUrl }}"
                                data-action-url="{{ route('admin.promotions.update', $promotion->id) }}">
                                <i class="bi bi-pencil"></i>
                            </button>

                            <button type="button" class="act del" title="Delete" aria-label="Delete"
                                data-action="delete"
                                data-id="{{ $promotion->id }}"
                                data-name="{{ $promotion->promo_name }} ({{ $promotion->promo_code }})"
                                data-action-url="{{ route('admin.promotions.destroy', $promotion->id) }}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
            @endforelse
        </tbody>
    </table>
    </div>

    <div class="table-foot">
        <div id="resultMeta">
            Showing {{ $promotions->firstItem() ?? 0 }} to {{ $promotions->lastItem() ?? 0 }} of {{ $promotions->total() }} results
        </div>
        <div class="pagination" aria-label="Pagination">
            {{ $promotions->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<div class="empty" id="emptyState" @if($promotions->isNotEmpty()) style="display:none" @endif>
    <div class="empty-card">
        <div class="illus"><i class="bi bi-gift" style="font-size:34px"></i></div>
        <h3>No Promotions Found</h3>
        <p>There are currently no promotional campaigns.</p>
        <button class="btn primary" id="createFromEmpty" type="button"><i class="bi bi-plus-lg"></i>Create Promotion</button>
    </div>
</div>

<div class="toast" id="toast"><i class="bi bi-check2-circle"></i><span id="toastText">Done</span></div>

@endsection
@push('modals')
<div class="overlay" id="promoOverlay" aria-hidden="true">
    <div class="modal-row">

        <div class="promo-modal" id="modalAdd" role="dialog" aria-modal="true" aria-label="Add Promotion">
            <form id="formAdd" method="POST" action="{{ route('admin.promotions.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="form_source" value="add">
                <div class="mh">
                    <div class="ttl">Add Promotion</div>
                    <button class="closex" type="button" data-close="true" aria-label="Close"><i class="bi bi-x-lg"></i></button>
                </div>
                <div class="mb">
                    <div class="grid2">
                        <div>
                            <div class="label">Promotion Banner</div>
                            <div class="banner-box" id="dropzoneAdd">
                                <div class="mini"><i class="bi bi-image"></i></div>
                                <div>Drag & drop image here<br/>or <b>click to upload</b></div>
                                <div style="font-weight:800;color:#94A3B8">JPG, PNG up to 2MB</div>
                                <input id="bannerAdd" name="banner" type="file" accept="image/*" hidden />
                            </div>
                            @error('banner') <small style="color:#EF4444;font-weight:700">{{ $message }}</small> @enderror
                        </div>

                        <div style="display:flex;flex-direction:column;gap:10px">
                            <div class="control">
                                <div class="label">Promotion Name</div>
                                <input name="promo_name" type="text" value="{{ old('promo_name') }}" placeholder="Enter promotion name">
                                @error('promo_name') <small style="color:#EF4444;font-weight:700">{{ $message }}</small> @enderror
                            </div>
                            <div class="control">
                                <div class="label">Promo Code</div>
                                <input name="promo_code" type="text" value="{{ old('promo_code') }}" placeholder="Enter promo code">
                                @error('promo_code') <small style="color:#EF4444;font-weight:700">{{ $message }}</small> @enderror
                            </div>
                            <div class="control">
                                <div class="label">Promotion Type</div>
                                <select name="discount_type" id="typeAdd">
                                    <option value="">Select type</option>
                                    <option value="Percentage" @selected(old('discount_type')=='Percentage')>Percentage</option>
                                    <option value="Voucher" @selected(old('discount_type')=='Voucher')>Voucher</option>
                                    <option value="Fixed Amount" @selected(old('discount_type')=='Fixed Amount')>Fixed Amount</option>
                                </select>
                                @error('discount_type') <small style="color:#EF4444;font-weight:700">{{ $message }}</small> @enderror
                            </div>
                            <div class="control">
                                <div class="label">Discount Value</div>
                                <input name="discount_value" type="text" value="{{ old('discount_value') }}" placeholder="Enter discount">
                                @error('discount_value') <small style="color:#EF4444;font-weight:700">{{ $message }}</small> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="grid2" style="margin-top:12px">
                        <div class="control">
                            <div class="label">Minimum Purchase</div>
                            <input name="minimum_booking" type="text" value="{{ old('minimum_booking') }}" placeholder="Enter minimum purchase">
                        </div>
                        <div class="control">
                            <div class="label">Maximum Discount</div>
                            <input name="maximum_discount" type="text" value="{{ old('maximum_discount') }}" placeholder="Enter maximum discount">
                        </div>
                    </div>

                    <div class="grid2" style="margin-top:12px">
                        <div>
                            <div class="label">Applicable Room Type</div>
                            <div class="control">
                                <select id="roomTypeAdd">
                                    <option value="">Select room types</option>
                                    @foreach($roomTypes as $rt)
                                        <option value="{{ $rt->id }}">{{ $rt->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div style="height:8px"></div>
                            <div class="tags" id="roomTagsAdd" aria-label="Selected room types"></div>
                            <div id="roomHiddenAdd"></div>
                        </div>
                        <div class="control">
                            <div class="label">Usage Limit</div>
                            <input name="quota" type="number" min="0" value="{{ old('quota') }}" placeholder="Enter usage limit (0 = unlimited)">
                        </div>
                    </div>

                    <div class="grid2" style="margin-top:12px">
                        <div class="control">
                            <div class="label">Start Date</div>
                            <input name="start_date" type="date" value="{{ old('start_date') }}">
                            @error('start_date') <small style="color:#EF4444;font-weight:700">{{ $message }}</small> @enderror
                        </div>
                        <div class="control">
                            <div class="label">End Date</div>
                            <input name="end_date" type="date" value="{{ old('end_date') }}">
                            @error('end_date') <small style="color:#EF4444;font-weight:700">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <div class="grid2" style="margin-top:12px">
                        <div>
                            <div class="label">Status</div>
                            <div class="radio-row">
                                <label><input type="radio" name="status" value="Active" checked> Active</label>
                                <label><input type="radio" name="status" value="Inactive"> Inactive</label>
                            </div>
                        </div>
                        <div></div>
                    </div>

                    <div class="control" style="margin-top:12px">
                        <div class="label">Description</div>
                        <textarea name="description" placeholder="Enter description">{{ old('description') }}</textarea>
                    </div>
                </div>
                <div class="mf">
                    <button class="btn" type="button" data-close="true">Cancel</button>
                    <button class="btn primary" type="submit">Save Promotion</button>
                </div>
            </form>
        </div>

        <div class="promo-modal" id="modalEdit" role="dialog" aria-modal="true" aria-label="Edit Promotion">
            <form id="formEdit" method="POST" action="" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="mh">
                    <div class="ttl">Edit Promotion</div>
                    <button class="closex" type="button" data-close="true" aria-label="Close"><i class="bi bi-x-lg"></i></button>
                </div>
                <div class="mb">
                    <div class="grid2">
                        <div>
                            <div class="label">Promotion Banner</div>
                            <div class="thumb-edit">
                                <img id="editThumb" alt="Banner preview" src="">
                                <div class="chg">
                                    <button class="btn small" type="button" id="changeImgBtn"><i class="bi bi-image"></i>Change Image</button>
                                    <input id="bannerEdit" name="banner" type="file" accept="image/*" hidden />
                                </div>
                            </div>
                        </div>

                        <div style="display:flex;flex-direction:column;gap:10px">
                            <div class="control">
                                <div class="label">Promotion Name</div>
                                <input name="promo_name" id="nameEdit" type="text">
                            </div>
                            <div class="control">
                                <div class="label">Promo Code</div>
                                <input name="promo_code" id="codeEdit" type="text">
                            </div>
                            <div class="control">
                                <div class="label">Promotion Type</div>
                                <select name="discount_type" id="typeEdit">
                                    <option value="Percentage">Percentage</option>
                                    <option value="Voucher">Voucher</option>
                                    <option value="Fixed Amount">Fixed Amount</option>
                                </select>
                            </div>
                            <div class="control">
                                <div class="label">Discount Value</div>
                                <input name="discount_value" id="discountEdit" type="text">
                            </div>
                        </div>
                    </div>

                    <div class="grid2" style="margin-top:12px">
                        <div class="control">
                            <div class="label">Minimum Purchase</div>
                            <input name="minimum_booking" id="minEdit" type="text">
                        </div>
                        <div class="control">
                            <div class="label">Maximum Discount</div>
                            <input name="maximum_discount" id="maxEdit" type="text">
                        </div>
                    </div>

                    <div class="grid2" style="margin-top:12px">
                        <div>
                            <div class="label">Applicable Room Type</div>
                            <div class="control">
                                <select id="roomTypeEdit">
                                    <option value="">Select room types</option>
                                    @foreach($roomTypes as $rt)
                                        <option value="{{ $rt->id }}">{{ $rt->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div style="height:8px"></div>
                            <div class="tags" id="roomTagsEdit"></div>
                            <div id="roomHiddenEdit"></div>
                        </div>
                        <div class="control">
                            <div class="label">Usage Limit</div>
                            <input name="quota" id="limitEdit" type="number" min="0">
                        </div>
                    </div>

                    <div class="grid2" style="margin-top:12px">
                        <div class="control">
                            <div class="label">Start Date</div>
                            <input name="start_date" id="startEdit" type="date">
                        </div>
                        <div class="control">
                            <div class="label">End Date</div>
                            <input name="end_date" id="endEdit" type="date">
                        </div>
                    </div>

                    <div class="grid2" style="margin-top:12px">
                        <div>
                            <div class="label">Status</div>
                            <div class="radio-row">
                                <label><input type="radio" name="status" id="statusEditActive" value="Active"> Active</label>
                                <label><input type="radio" name="status" id="statusEditInactive" value="Inactive"> Inactive</label>
                            </div>
                        </div>
                        <div></div>
                    </div>

                    <div class="control" style="margin-top:12px">
                        <div class="label">Description</div>
                        <textarea name="description" id="descEdit"></textarea>
                    </div>
                </div>
                <div class="mf">
                    <button class="btn" type="button" data-close="true">Cancel</button>
                    <button class="btn primary" type="submit">Update Promotion</button>
                </div>
            </form>
        </div>

        <div class="promo-modal" id="modalView" role="dialog" aria-modal="true" aria-label="Promotion Detail">
            <div class="mh">
                <div class="ttl">Promotion Detail</div>
                <button class="closex" type="button" data-close="true" aria-label="Close"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="mb">
                <div class="detail">
                    <div class="hero"><img id="viewThumb" alt="Promotion banner" src=""></div>
                    <div class="kv">
                        <div class="k">Promotion Name</div><div class="v" id="vName">—</div>
                        <div class="k">Promo Code</div><div class="v" id="vCode">—</div>
                        <div class="k">Promotion Type</div><div class="v" id="vType">—</div>
                        <div class="k">Discount</div><div class="v" id="vDiscount">—</div>
                    </div>
                </div>

                <div class="detail-bottom">
                    <div class="kv">
                        <div class="k">Minimum Purchase</div><div class="v" id="vMin">—</div>
                        <div class="k">Maximum Discount</div><div class="v" id="vMax">—</div>
                        <div class="k">Applicable Room Type</div><div class="v" id="vRooms">—</div>
                        <div class="k">Usage Limit</div><div class="v" id="vLimit">—</div>
                        <div class="k">Valid Period</div><div class="v" id="vPeriod">—</div>
                        <div class="k">Status</div><div class="v" id="vStatus">—</div>
                        <div class="k">Description</div><div class="v" id="vDesc">—</div>
                    </div>
                </div>
            </div>
            <div class="mf">
                <button class="btn" type="button" data-close="true">Close</button>
            </div>
        </div>

        <div class="promo-modal small" id="modalDelete" role="dialog" aria-modal="true" aria-label="Delete Promotion">
            <form id="formDelete" method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="mh">
                    <div class="danger-head">
                        <div class="warn-ic"><i class="bi bi-exclamation-circle"></i></div>
                        <div class="ttl">Delete Promotion</div>
                    </div>
                    <button class="closex" type="button" data-close="true" aria-label="Close"><i class="bi bi-x-lg"></i></button>
                </div>
                <div class="mb">
                    <div class="confirm">
                        <div class="q">Are you sure you want to delete <span id="delName" style="color:var(--btn-navy)">—</span>?</div>
                        <p class="sub">This action cannot be undone.</p>
                    </div>
                </div>
                <div class="mf center-actions">
                    <button class="btn" type="button" data-close="true">Cancel</button>
                    <button class="btn danger" type="submit">Delete</button>
                </div>
            </form>
        </div>

    </div>
</div>
@endpush

@push('scripts')
<script src="{{ asset('js/admin/promotion.js') }}"></script>
@endpush
