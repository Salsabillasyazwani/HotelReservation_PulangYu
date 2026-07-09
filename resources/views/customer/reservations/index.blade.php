@extends('layouts.customer')

@section('title', 'My Reservation - Hotel Pulang Yo')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/customer/reservations.css') }}?v={{ time() }}">
@endpush

@section('content')
<div class="page-content">

    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-6 fade-in">
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold text-navy">My Reservation</h1>
            <p class="text-slate-500 mt-1">Manage and track all your bookings</p>
        </div>
        <button type="button" id="btnOpenBooking" class="bg-gold hover:bg-yellow-500 text-navy font-semibold px-5 py-3 rounded-xl shadow-md transition flex items-center gap-2 justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Book New Room
        </button>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-4 mb-6 flex flex-col md:flex-row gap-4 md:items-center md:justify-between fade-in">
        <div class="relative w-full md:max-w-xs">
            <svg class="w-5 h-5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input id="searchInput" type="text" placeholder="Search reservation code..." class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-gold focus:border-gold outline-none text-sm">
        </div>
        <div class="flex flex-wrap gap-2" id="filterButtons">
            <button data-status="all" class="filter-btn active px-4 py-2 rounded-xl text-sm font-medium transition">All</button>
            <button data-status="pending" class="filter-btn px-4 py-2 rounded-xl text-sm font-medium transition">Pending</button>
            <button data-status="confirmed" class="filter-btn px-4 py-2 rounded-xl text-sm font-medium transition">Confirmed</button>
            <button data-status="checked-in" class="filter-btn px-4 py-2 rounded-xl text-sm font-medium transition">Checked In</button>
            <button data-status="checked-out" class="filter-btn px-4 py-2 rounded-xl text-sm font-medium transition">Checked Out</button>
            <button data-status="cancelled" class="filter-btn px-4 py-2 rounded-xl text-sm font-medium transition">Cancelled</button>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm overflow-hidden fade-in">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-navy text-white">
                    <tr>
                        <th class="px-5 py-4 font-medium">Reservation Code</th>
                        <th class="px-5 py-4 font-medium">Room</th>
                        <th class="px-5 py-4 font-medium">Check In</th>
                        <th class="px-5 py-4 font-medium">Check Out</th>
                        <th class="px-5 py-4 font-medium">Total Amount</th>
                        <th class="px-5 py-4 font-medium">Promotion</th>
                        <th class="px-5 py-4 font-medium">Status</th>
                        <th class="px-5 py-4 font-medium text-center">Action</th>
                    </tr>
                </thead>
                <tbody id="reservationBody" class="divide-y divide-slate-100">
                    @forelse($reservations as $reservation)
                        @php
                            $statusSlug = \Illuminate\Support\Str::slug($reservation->reservation_status);
                        @endphp
                        <tr class="reservation-row hover:bg-slate-50 transition"
                            data-status="{{ $statusSlug }}"
                            data-code="{{ $reservation->reservation_code }}"
                            data-detail="{{ json_encode([
                                'code'                 => $reservation->reservation_code,
                                'room'                 => $reservation->room->room_name,
                                'type'                 => $reservation->room->roomType->name,
                                'number'               => $reservation->room->room_number,
                                'checkin'              => $reservation->check_in->format('Y-m-d'),
                                'checkout'             => $reservation->check_out->format('Y-m-d'),
                                'nights'               => $reservation->nights,
                                'guests'               => $reservation->guests,
                                'price_per_night'      => (float) $reservation->price_per_night,
                                'tax'                  => (float) $reservation->tax,
                                'promo'                => $reservation->promotion->promo_code ?? '-',
                                'discount'             => (float) $reservation->discount,
                                'total'                => (float) $reservation->total_amount,
                                'status'               => $statusSlug,
                                'status_label'         => $reservation->reservation_status,
                                'payment_status'       => $reservation->payment_status,
                                'special_request'      => $reservation->special_request,
                                'cancellation_reason'  => $reservation->cancellation_reason,
                            ]) }}">
                            <td class="px-5 py-4 font-semibold text-navy">#{{ $reservation->reservation_code }}</td>
                            <td class="px-5 py-4">
                                <div class="font-medium text-slate-800">{{ $reservation->room->room_name }}</div>
                                <div class="text-xs text-slate-400">{{ $reservation->room->roomType->name }} &middot; No.{{ $reservation->room->room_number }}</div>
                            </td>
                            <td class="px-5 py-4 text-slate-600">{{ $reservation->check_in->format('Y-m-d') }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $reservation->check_out->format('Y-m-d') }}</td>
                            <td class="px-5 py-4 font-semibold text-navy">Rp {{ number_format($reservation->total_amount, 0, ',', '.') }}</td>
                            <td class="px-5 py-4">
                                @if($reservation->promotion)
                                    <span class="promo-badge text-xs font-medium px-2 py-1 rounded-lg">{{ $reservation->promotion->promo_code }}</span>
                                @else
                                    <span class="text-slate-400">&mdash;</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <span class="status-badge status-{{ $statusSlug }} text-xs font-semibold px-3 py-1 rounded-full">{{ $reservation->reservation_status }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" class="btn-view-detail p-2 rounded-lg transition" title="View Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                    @if(in_array($reservation->reservation_status, ['Pending', 'Confirmed']))
                                        <form action="{{ route('customer.reservations.cancel', $reservation->id) }}" method="POST" class="form-cancel">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn-cancel p-2 rounded-lg transition" title="Cancel" data-code="{{ $reservation->reservation_code }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                    @endforelse
                </tbody>
            </table>
        </div>
        <div id="emptyState" class="p-10 text-center text-slate-400 {{ $reservations->count() ? 'hidden' : '' }}">
            <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            No reservations found.
        </div>
    </div>

</div>

<div id="detailModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/50">
    <div class="modal-scale bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="bg-navy text-white p-6 rounded-t-2xl flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold">Reservation Detail</h3>
                <p class="text-xs text-gold" id="detailCode">#RSV-0000</p>
            </div>
            <button type="button" id="btnCloseDetail" class="p-2 hover:bg-white/10 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-6 space-y-4" id="detailBody"></div>
        <div class="p-6 pt-0">
            <button type="button" id="btnCloseDetailFooter" class="w-full bg-slate-100 hover:bg-slate-200 text-navy font-semibold py-3 rounded-xl transition">Close</button>
        </div>
    </div>
</div>

@include('customer.reservations.reservations_create')

@endsection

@push('scripts')
<script>
    window.routes = {
        availableRooms:   "{{ route('customer.reservations.available-rooms') }}",
        validatePromo:    "{{ route('customer.reservations.validate-promo') }}",
        storeReservation: "{{ route('customer.reservations.store') }}",
    };
</script>
<script src="{{ asset('js/customer/reservation.js') }}?v={{ time() }}"></script>
<script src="{{ asset('js/customer/reservation_create.js') }}?v={{ time() }}"></script>
@endpush
