@extends('layouts.admin')

@section('title', 'All Reservations - Hotel Pulang Yo')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/reservation.css') }}">
@endpush

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        window.ReservationConfig = {
            dataUrl: "{{ route('admin.reservations.data') }}",
            storeUrl: "{{ route('admin.reservations.store') }}",
            indexUrl: "{{ route('admin.reservations.index') }}",
            availableRoomsUrl: "{{ route('admin.reservations.available-rooms') }}",
            resourceBaseUrl: "{{ url('admin/reservations') }}",
            stats: @json($stats ?? []),
        };
    </script>

    <!-- Toast Notification (fixed, di luar page-content, tidak masalah) -->
    <div id="toast" class="toast flex items-center gap-3">
        <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-success">
            <i class="fa-solid fa-check"></i>
        </div>
        <div>
            <p class="font-semibold text-sm text-text">Success</p>
            <p id="toast-message" class="text-xs text-secondary">Operation completed successfully</p>
        </div>
    </div>

    {{-- WAJIB: dibungkus .page-content supaya sinkron dengan admin.css --}}
    <div class="page-content">

        <!-- Breadcrumb & Header -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-8 animate-fade-in">
            <div class="min-w-0">
                <div class="flex items-center gap-2 text-xs text-secondary mb-2">
                    <span>Reservation</span>
                    <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    <span class="text-primary font-semibold">All Reservations</span>
                </div>
                <h1 class="font-display text-3xl font-bold text-text mb-1">All Reservations</h1>
                <p class="text-secondary text-sm">Manage all hotel reservations efficiently.</p>
            </div>
            <a href="{{ route('admin.reservations.create') }}"
               class="btn-gradient-primary shrink-0 self-start lg:self-auto px-6 py-3 rounded-xl font-semibold text-sm whitespace-nowrap">
                <i class="fa-solid fa-plus"></i>
                New Reservation
            </a>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid cols-5">
            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-icon blue">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                    <span class="stat-menu">⋮</span>
                </div>
                <div class="stat-label">Pending</div>
                <div class="stat-value" id="stat-pending">{{ $stats['pending'] ?? 0 }}</div>
                <div class="stat-trend">
                    @if (isset($stats['pending_percent']))
                        <span class="up">{{ $stats['pending_percent'] }}%</span> of total
                    @else
                        Awaiting confirmation
                    @endif
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-icon green">
                        <i class="fa-solid fa-check-circle"></i>
                    </div>
                    <span class="stat-menu">⋮</span>
                </div>
                <div class="stat-label">Confirmed</div>
                <div class="stat-value" id="stat-confirmed">{{ $stats['confirmed'] ?? 0 }}</div>
                <div class="stat-trend">
                    @if (isset($stats['confirmed_percent']))
                        <span class="up">{{ $stats['confirmed_percent'] }}%</span> of total
                    @else
                        Ready for check-in
                    @endif
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-icon orange">
                        <i class="fa-solid fa-door-open"></i>
                    </div>
                    <span class="stat-menu">⋮</span>
                </div>
                <div class="stat-label">Checked In</div>
                <div class="stat-value" id="stat-checked-in">{{ $stats['checked_in'] ?? 0 }}</div>
                <div class="stat-trend">
                    @if (isset($stats['checked_in_percent']))
                        <span class="up">{{ $stats['checked_in_percent'] }}%</span> of total
                    @else
                        Currently in-house
                    @endif
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-icon purple">
                        <i class="fa-solid fa-sign-out-alt"></i>
                    </div>
                    <span class="stat-menu">⋮</span>
                </div>
                <div class="stat-label">Checked Out</div>
                <div class="stat-value" id="stat-checked-out">{{ $stats['checked_out'] ?? 0 }}</div>
                <div class="stat-trend">
                    @if (isset($stats['checked_out_percent']))
                        <span class="up">{{ $stats['checked_out_percent'] }}%</span> of total
                    @else
                        Completed stays
                    @endif
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-icon red">
                        <i class="fa-solid fa-times-circle"></i>
                    </div>
                    <span class="stat-menu">⋮</span>
                </div>
                <div class="stat-label">Cancelled</div>
                <div class="stat-value" id="stat-cancelled">{{ $stats['cancelled'] ?? 0 }}</div>
                <div class="stat-trend">
                    @if (isset($stats['cancelled_percent']))
                        <span class="down">{{ $stats['cancelled_percent'] }}%</span> of total
                    @else
                        Cancelled bookings
                    @endif
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl p-5 shadow-soft border border-border/50 mb-6 animate-fade-in" style="animation-delay: 0.3s">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-4 items-end">
                <div class="lg:col-span-4">
                    <label class="block text-xs font-semibold text-secondary uppercase tracking-wider mb-2">Date Range</label>
                    <div class="flex items-center gap-2">
                        <input type="date" id="date-from" class="premium-input text-sm py-2.5 min-w-0">
                        <span class="text-secondary text-xs shrink-0">-</span>
                        <input type="date" id="date-to" class="premium-input text-sm py-2.5 min-w-0">
                    </div>
                </div>
                <div class="lg:col-span-3">
                    <label class="block text-xs font-semibold text-secondary uppercase tracking-wider mb-2">Reservation Status</label>
                    <div class="relative">
                        <select id="status-filter" class="premium-input appearance-none cursor-pointer pr-9">
                            <option value="">All Status</option>
                            <option value="Pending">Pending</option>
                            <option value="Confirmed">Confirmed</option>
                            <option value="Checked In">Checked In</option>
                            <option value="Checked Out">Checked Out</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                        <i class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-secondary text-xs pointer-events-none"></i>
                    </div>
                </div>
                <div class="lg:col-span-3">
                    <label class="block text-xs font-semibold text-secondary uppercase tracking-wider mb-2">Room Type</label>
                    <div class="relative">
                        <select id="room-type-filter" class="premium-input appearance-none cursor-pointer pr-9">
                            <option value="">All Room Type</option>
                            @foreach ($roomTypes ?? [] as $roomType)
                                <option value="{{ $roomType }}">{{ $roomType }}</option>
                            @endforeach
                        </select>
                        <i class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-secondary text-xs pointer-events-none"></i>
                    </div>
                </div>
                <div class="lg:col-span-1">
                    <label class="block text-xs font-semibold text-secondary uppercase tracking-wider mb-2">Payment</label>
                    <div class="relative">
                        <select id="payment-filter" class="premium-input appearance-none cursor-pointer pr-9">
                            <option value="">All</option>
                            <option value="Paid">Paid</option>
                            <option value="Unpaid">Unpaid</option>
                            <option value="Partial">Partial</option>
                            <option value="Refunded">Refunded</option>
                        </select>
                        <i class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-secondary text-xs pointer-events-none"></i>
                    </div>
                </div>
                <div class="lg:col-span-1 flex gap-2">
                    <button type="button" onclick="resetFilters()" title="Reset filter" class="btn-outline flex-1 px-3 py-3 rounded-xl text-sm font-medium">
                        <i class="fa-solid fa-rotate-right"></i>
                    </button>
                    <button type="button" onclick="exportData()" title="Export CSV" class="btn-gradient-primary flex-1 px-3 py-3 rounded-xl text-sm font-medium">
                        <i class="fa-solid fa-download"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-xl shadow-soft border border-border/50 overflow-hidden animate-fade-in" style="animation-delay: 0.35s">
            <div class="overflow-x-auto">
                <table class="premium-table w-full">
                    <thead>
                        <tr>
                            <th class="px-6 py-4 text-left">
                                <input type="checkbox" id="select-all" class="custom-checkbox">
                            </th>
                            <th class="px-4 py-4 text-left cursor-pointer hover:text-primary" onclick="sortBy('id')">
                                Reservation ID <i class="fa-solid fa-sort text-xs ml-1"></i>
                            </th>
                            <th class="px-4 py-4 text-left cursor-pointer hover:text-primary" onclick="sortBy('guest')">
                                Guest <i class="fa-solid fa-sort text-xs ml-1"></i>
                            </th>
                            <th class="px-4 py-4 text-left cursor-pointer hover:text-primary" onclick="sortBy('room')">
                                Room <i class="fa-solid fa-sort text-xs ml-1"></i>
                            </th>
                            <th class="px-4 py-4 text-left cursor-pointer hover:text-primary" onclick="sortBy('checkIn')">
                                Check In <i class="fa-solid fa-sort text-xs ml-1"></i>
                            </th>
                            <th class="px-4 py-4 text-left cursor-pointer hover:text-primary" onclick="sortBy('checkOut')">
                                Check Out <i class="fa-solid fa-sort text-xs ml-1"></i>
                            </th>
                            <th class="px-4 py-4 text-center cursor-pointer hover:text-primary" onclick="sortBy('guests')">
                                Guests <i class="fa-solid fa-sort text-xs ml-1"></i>
                            </th>
                            <th class="px-4 py-4 text-right cursor-pointer hover:text-primary" onclick="sortBy('total')">
                                Total Payment <i class="fa-solid fa-sort text-xs ml-1"></i>
                            </th>
                            <th class="px-4 py-4 text-center cursor-pointer hover:text-primary" onclick="sortBy('paymentStatus')">
                                Payment Status <i class="fa-solid fa-sort text-xs ml-1"></i>
                            </th>
                            <th class="px-4 py-4 text-center cursor-pointer hover:text-primary" onclick="sortBy('status')">
                                Reservation Status <i class="fa-solid fa-sort text-xs ml-1"></i>
                            </th>
                            <th class="px-6 py-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody id="reservation-table-body">
                        {{-- Populated by JS via GET {{ route('admin.reservations.data') }} --}}
                        <tr>
                            <td colspan="11" class="px-6 py-10 text-center text-secondary text-sm">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination (fully driven by JS, meta dari response paginator) -->
            <div class="px-6 py-4 border-t border-border flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-sm text-secondary" id="showing-text"></p>
                <div class="flex items-center gap-2" id="pagination">
                    {{-- Populated by JS --}}
                </div>
            </div>
        </div>

    </div>{{-- /.page-content --}}

    <!-- View Modal -->
    <div id="view-modal" class="modal-overlay">
        <div class="modal-content bg-white rounded-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto mx-auto mt-10 shadow-soft-lg">
            <div class="sticky top-0 bg-white z-10 px-8 py-6 border-b border-border flex items-center justify-between">
                <div>
                    <h2 class="font-display text-2xl font-bold text-text">Reservation Detail</h2>
                    <p class="text-sm text-secondary mt-1" id="view-subtitle">Complete reservation information</p>
                </div>
                <button type="button" onclick="closeModal('view-modal')" class="w-10 h-10 rounded-full hover:bg-gray-100 flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-xmark text-lg text-secondary"></i>
                </button>
            </div>
            <div class="p-8 space-y-8" id="view-content">
                {{-- Populated by JS --}}
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="edit-modal" class="modal-overlay">
        <div class="modal-content bg-white rounded-xl w-full max-w-3xl max-h-[90vh] overflow-y-auto mx-auto mt-10 shadow-soft-lg">
            <div class="sticky top-0 bg-white z-10 px-8 py-6 border-b border-border flex items-center justify-between">
                <div>
                    <h2 class="font-display text-2xl font-bold text-text">Edit Reservation</h2>
                    <p class="text-sm text-secondary mt-1" id="edit-subtitle">Modify reservation details</p>
                </div>
                <button type="button" onclick="closeModal('edit-modal')" class="w-10 h-10 rounded-full hover:bg-gray-100 flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-xmark text-lg text-secondary"></i>
                </button>
            </div>
            <form id="edit-form" class="p-8 space-y-6">
                <input type="hidden" id="edit-id">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-semibold text-secondary uppercase tracking-wider mb-2">Guest Name</label>
                        <input type="text" id="edit-guest" class="premium-input" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-secondary uppercase tracking-wider mb-2">Email</label>
                        <input type="email" id="edit-email" class="premium-input" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-secondary uppercase tracking-wider mb-2">Phone</label>
                        <input type="tel" id="edit-phone" class="premium-input" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-secondary uppercase tracking-wider mb-2">Nationality</label>
                        <input type="text" id="edit-nationality" class="premium-input">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-secondary uppercase tracking-wider mb-2">Check In</label>
                        <input type="date" id="edit-checkin" class="premium-input" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-secondary uppercase tracking-wider mb-2">Check Out</label>
                        <input type="date" id="edit-checkout" class="premium-input" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-secondary uppercase tracking-wider mb-2">Room Number</label>
                        <select id="edit-room" class="premium-input">
                            @foreach ($rooms ?? [] as $room)
                                <option value="{{ $room->room_number }}">{{ $room->room_number }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-secondary uppercase tracking-wider mb-2">Guests</label>
                        <input type="number" id="edit-guests" class="premium-input" min="1" max="10" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-secondary uppercase tracking-wider mb-2">Reservation Status</label>
                        <select id="edit-status" class="premium-input">
                            <option value="Pending">Pending</option>
                            <option value="Confirmed">Confirmed</option>
                            <option value="Checked In">Checked In</option>
                            <option value="Checked Out">Checked Out</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-secondary uppercase tracking-wider mb-2">Payment Status</label>
                        <select id="edit-payment" class="premium-input">
                            <option value="Paid">Paid</option>
                            <option value="Unpaid">Unpaid</option>
                            <option value="Partial">Partial</option>
                            <option value="Refunded">Refunded</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-secondary uppercase tracking-wider mb-2">Special Request</label>
                    <textarea id="edit-request" rows="3" class="premium-input resize-none"></textarea>
                </div>
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
                    <button type="button" onclick="closeModal('edit-modal')" class="btn-outline px-6 py-3 rounded-xl font-semibold text-sm">Cancel</button>
                    <button type="submit" class="btn-gradient-primary px-6 py-3 rounded-xl font-semibold text-sm">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Check In Modal -->
    <div id="checkin-modal" class="modal-overlay">
        <div class="modal-content bg-white rounded-xl w-full max-w-lg mx-auto mt-20 shadow-soft-lg">
            <div class="px-8 py-6 border-b border-border flex items-center justify-between">
                <div>
                    <h2 class="font-display text-2xl font-bold text-text">Check In Guest</h2>
                    <p class="text-sm text-secondary mt-1">Confirm guest arrival</p>
                </div>
                <button type="button" onclick="closeModal('checkin-modal')" class="w-10 h-10 rounded-full hover:bg-gray-100 flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-xmark text-lg text-secondary"></i>
                </button>
            </div>
            <form id="checkin-form" class="p-8 space-y-6">
                <input type="hidden" id="checkin-id">
                <div class="bg-blue-50 rounded-xl p-5 border border-blue-100">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-full bg-primary text-white flex items-center justify-center font-bold" id="checkin-avatar"></div>
                        <div>
                            <p class="font-bold text-text" id="checkin-guest"></p>
                            <p class="text-sm text-secondary" id="checkin-room"></p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-secondary text-xs uppercase tracking-wider">Check In</p>
                            <p class="font-semibold text-text" id="checkin-date"></p>
                        </div>
                        <div>
                            <p class="text-secondary text-xs uppercase tracking-wider">Reservation ID</p>
                            <p class="font-semibold text-text" id="checkin-rsv"></p>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-secondary uppercase tracking-wider mb-2">Current Date & Time</label>
                    <input type="datetime-local" id="checkin-actual" class="premium-input" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-secondary uppercase tracking-wider mb-2">Notes</label>
                    <textarea id="checkin-notes" rows="3" class="premium-input resize-none" placeholder="Add arrival notes..."></textarea>
                </div>
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
                    <button type="button" onclick="closeModal('checkin-modal')" class="btn-outline px-6 py-3 rounded-xl font-semibold text-sm">Cancel</button>
                    <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-warning to-orange-500 text-white font-semibold text-sm shadow-lg hover:shadow-xl transition-all">
                        <i class="fa-solid fa-door-open mr-2"></i>Confirm Check In
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Check Out Modal -->
    <div id="checkout-modal" class="modal-overlay">
        <div class="modal-content bg-white rounded-xl w-full max-w-lg mx-auto mt-20 shadow-soft-lg">
            <div class="px-8 py-6 border-b border-border flex items-center justify-between">
                <div>
                    <h2 class="font-display text-2xl font-bold text-text">Check Out Guest</h2>
                    <p class="text-sm text-secondary mt-1">Process guest departure</p>
                </div>
                <button type="button" onclick="closeModal('checkout-modal')" class="w-10 h-10 rounded-full hover:bg-gray-100 flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-xmark text-lg text-secondary"></i>
                </button>
            </div>
            <form id="checkout-form" class="p-8 space-y-6">
                <input type="hidden" id="checkout-id">
                <div class="bg-purple-50 rounded-xl p-5 border border-purple-100">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-full bg-purple-600 text-white flex items-center justify-center font-bold" id="checkout-avatar"></div>
                        <div>
                            <p class="font-bold text-text" id="checkout-guest"></p>
                            <p class="text-sm text-secondary" id="checkout-room"></p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-secondary text-xs uppercase tracking-wider">Room</p>
                            <p class="font-semibold text-text" id="checkout-room-num"></p>
                        </div>
                        <div>
                            <p class="text-secondary text-xs uppercase tracking-wider">Original Check Out</p>
                            <p class="font-semibold text-text" id="checkout-original"></p>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-secondary uppercase tracking-wider mb-2">Actual Check Out</label>
                    <input type="datetime-local" id="checkout-actual" class="premium-input" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-secondary uppercase tracking-wider mb-2">Additional Charges (Rp)</label>
                    <input type="number" id="checkout-charges" class="premium-input" value="0" min="0">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-secondary uppercase tracking-wider mb-2">Notes</label>
                    <textarea id="checkout-notes" rows="3" class="premium-input resize-none" placeholder="Add departure notes..."></textarea>
                </div>
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
                    <button type="button" onclick="closeModal('checkout-modal')" class="btn-outline px-6 py-3 rounded-xl font-semibold text-sm">Cancel</button>
                    <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-purple-600 to-purple-500 text-white font-semibold text-sm shadow-lg hover:shadow-xl transition-all">
                        <i class="fa-solid fa-sign-out-alt mr-2"></i>Confirm Check Out
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Cancel Modal -->
    <div id="cancel-modal" class="modal-overlay">
        <div class="modal-content bg-white rounded-xl w-full max-w-lg mx-auto mt-20 shadow-soft-lg">
            <div class="px-8 py-6 border-b border-border flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-danger">
                        <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                    </div>
                    <div>
                        <h2 class="font-display text-xl font-bold text-text">Cancel Reservation</h2>
                        <p class="text-sm text-secondary">This action cannot be undone</p>
                    </div>
                </div>
                <button type="button" onclick="closeModal('cancel-modal')" class="w-10 h-10 rounded-full hover:bg-gray-100 flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-xmark text-lg text-secondary"></i>
                </button>
            </div>
            <form id="cancel-form" class="p-8 space-y-6">
                <input type="hidden" id="cancel-id">
                <div class="bg-red-50 border border-red-100 rounded-xl p-4">
                    <p class="text-sm text-red-800 font-medium mb-1">Warning</p>
                    <p class="text-sm text-red-700">Cancelling this reservation will change its status to "Cancelled" and release the room. The reservation record will be preserved for reporting purposes.</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-secondary uppercase tracking-wider mb-2">Cancellation Reason</label>
                    <textarea id="cancel-reason" rows="4" class="premium-input resize-none" placeholder="Enter reason for cancellation..." required></textarea>
                </div>
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
                    <button type="button" onclick="closeModal('cancel-modal')" class="btn-outline px-6 py-3 rounded-xl font-semibold text-sm">Cancel</button>
                    <button type="submit" class="px-6 py-3 rounded-xl bg-danger hover:bg-red-700 text-white font-semibold text-sm shadow-lg hover:shadow-xl transition-all">
                        Confirm Cancel Reservation
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin/reservation.js') }}"></script>
@endpush
