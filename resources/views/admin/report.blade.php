@extends('layouts.admin')

@section('title', 'Reservation Report')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/report.css') }}">
@endpush

@section('content')
<div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-6">

  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="text-2xl sm:text-3xl font-semibold text-slate-900 tracking-tight">Reservation Report</h1>
      <p class="text-slate-500 text-sm mt-1">View, filter, and export reservation reports for {{ $hotelName ?? 'Hotel' }}.</p>
    </div>
    <div class="flex items-center gap-3">
      <a data-export
         href="{{ route('admin.reports.export', array_merge(['type' => 'excel'], request()->query())) }}"
         class="flex items-center gap-2 bg-white border border-emerald-200 text-emerald-600 font-semibold px-4 py-2.5 rounded-xl shadow-sm hover:bg-emerald-50 active:scale-95 transition-all text-sm">
        <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
        Export Excel
      </a>
      <a data-export
         href="{{ route('admin.reports.export', array_merge(['type' => 'pdf'], request()->query())) }}"
         class="flex items-center gap-2 bg-white border border-red-200 text-red-600 font-semibold px-4 py-2.5 rounded-xl shadow-sm hover:bg-red-50 active:scale-95 transition-all text-sm">
        <i data-lucide="file-text" class="w-4 h-4"></i>
        Export PDF
      </a>
    </div>
  </div>

  <div class="stats-grid mt-6">
    <div class="stat-card">
      <div class="stat-top">
        <div class="stat-icon blue">
          <i data-lucide="clipboard-list"></i>
        </div>
        <span class="stat-menu">⋮</span>
      </div>
      <div class="stat-label">Total Reservation</div>
      <div class="stat-value">{{ number_format($stats['total'] ?? 0) }}</div>
      <div class="stat-trend">All reservation</div>
    </div>

    <div class="stat-card">
      <div class="stat-top">
        <div class="stat-icon green">
          <i data-lucide="wallet"></i>
        </div>
        <span class="stat-menu">⋮</span>
      </div>
      <div class="stat-label">Total Revenue</div>
      <div class="stat-value">Rp{{ number_format($stats['revenue'] ?? 0, 0, ',', '.') }}</div>
      <div class="stat-trend">Paid reservations</div>
    </div>

    <div class="stat-card">
      <div class="stat-top">
        <div class="stat-icon purple">
          <i data-lucide="log-in"></i>
        </div>
        <span class="stat-menu">⋮</span>
      </div>
      <div class="stat-label">Checked In</div>
      <div class="stat-value">{{ number_format($stats['checked_in'] ?? 0) }}</div>
      <div class="stat-trend">Guest checked in</div>
    </div>

    <div class="stat-card">
      <div class="stat-top">
        <div class="stat-icon orange">
          <i data-lucide="log-out"></i>
        </div>
        <span class="stat-menu">⋮</span>
      </div>
      <div class="stat-label">Checked Out</div>
      <div class="stat-value">{{ number_format($stats['checked_out'] ?? 0) }}</div>
      <div class="stat-trend">Guest checked out</div>
    </div>
  </div>

  <form method="GET" action="{{ route('admin.reports') }}" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 mt-5">
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
      <input type="hidden" name="search" value="{{ request('search') }}">
      <div>
        <label class="text-xs font-semibold text-slate-600 mb-1.5 block">Date From</label>
        <div class="relative">
          <i data-lucide="calendar" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
          <input type="date" name="date_from" value="{{ request('date_from') }}"
                 class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-300 focus:border-slate-400 transition">
        </div>
      </div>
      <div>
        <label class="text-xs font-semibold text-slate-600 mb-1.5 block">Date To</label>
        <div class="relative">
          <i data-lucide="calendar" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
          <input type="date" name="date_to" value="{{ request('date_to') }}"
                 class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-300 focus:border-slate-400 transition">
        </div>
      </div>
      <div>
        <label class="text-xs font-semibold text-slate-600 mb-1.5 block">Reservation Status</label>
        <select name="status" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-300 focus:border-slate-400 transition bg-white">
          <option value="">All Status</option>
          @foreach(['Pending', 'Confirmed', 'Checked In', 'Checked Out', 'Cancelled'] as $s)
            <option value="{{ $s }}" @selected(request('status') == $s)>{{ $s }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="text-xs font-semibold text-slate-600 mb-1.5 block">Room Type</label>
        <select name="room_type" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-300 focus:border-slate-400 transition bg-white">
          <option value="">All Room Type</option>
          @foreach($roomTypes ?? [] as $rt)
            <option value="{{ $rt }}" @selected(request('room_type') == $rt)>{{ $rt }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="text-xs font-semibold text-slate-600 mb-1.5 block">Payment Status</label>
        <select name="payment_status" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-300 focus:border-slate-400 transition bg-white">
          <option value="">All Payment Status</option>
          @foreach(['Paid', 'Unpaid', 'Partial', 'Refunded'] as $p)
            <option value="{{ $p }}" @selected(request('payment_status') == $p)>{{ $p }}</option>
          @endforeach
        </select>
      </div>
    </div>
    <div class="flex items-center gap-3 mt-4">
      <button type="submit" class="flex items-center gap-2 bg-slate-900 text-white font-semibold px-5 py-2.5 rounded-xl shadow-sm hover:bg-slate-800 active:scale-95 transition-all text-sm">
        <i data-lucide="filter" class="w-4 h-4"></i>
        Filter
      </button>
      <a href="{{ route('admin.reports') }}" class="flex items-center gap-2 bg-white border border-slate-200 text-slate-600 font-semibold px-5 py-2.5 rounded-xl shadow-sm hover:bg-slate-50 active:scale-95 transition-all text-sm">
        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
        Reset
      </a>
    </div>
  </form>

  <div class="bg-white rounded-2xl shadow-sm border border-slate-100 mt-4 overflow-hidden">
    <div class="px-5 pt-5 pb-3">
      <h2 class="text-base font-semibold text-slate-900">Reservation Report List</h2>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm" id="reportTable">
        <thead>
          <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
            <th class="px-5 py-3 text-left font-semibold">No</th>
            <th class="px-4 py-3 text-left font-semibold">Reservation Code</th>
            <th class="px-4 py-3 text-left font-semibold">Guest Name</th>
            <th class="px-4 py-3 text-left font-semibold">Room</th>
            <th class="px-4 py-3 text-left font-semibold">Room Type</th>
            <th class="px-4 py-3 text-left font-semibold">Check In</th>
            <th class="px-4 py-3 text-left font-semibold">Check Out</th>
            <th class="px-4 py-3 text-left font-semibold">Status</th>
            <th class="px-4 py-3 text-left font-semibold">Payment</th>
            <th class="px-4 py-3 text-left font-semibold">Total Payment</th>
            <th class="px-5 py-3 text-center font-semibold">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100" id="tableBody">
          @forelse($reservations as $r)
            <tr class="hover:bg-slate-50 transition">
              <td class="px-5 py-3.5 text-slate-500">
                {{ $loop->iteration + ($reservations->currentPage() - 1) * $reservations->perPage() }}
              </td>
              <td class="px-4 py-3.5 font-semibold text-slate-800">{{ $r->reservation_code }}</td>
              <td class="px-4 py-3.5 text-slate-700">{{ $r->guest_name }}</td>
              <td class="px-4 py-3.5 text-slate-600">{{ $r->room->room_number ?? '-' }}</td>
              <td class="px-4 py-3.5 text-slate-600">{{ $r->room->roomType->name ?? '-' }}</td>
              <td class="px-4 py-3.5 text-slate-600">{{ optional($r->check_in)->format('d M Y') }}</td>
              <td class="px-4 py-3.5 text-slate-600">{{ optional($r->check_out)->format('d M Y') }}</td>
              <td class="px-4 py-3.5"><span class="badge status-{{ Str::slug($r->reservation_status) }}">{{ $r->reservation_status }}</span></td>
              <td class="px-4 py-3.5"><span class="badge payment-{{ Str::slug($r->payment_status) }}">{{ $r->payment_status }}</span></td>
              <td class="px-4 py-3.5 font-semibold text-slate-800">Rp{{ number_format((float) $r->total_amount, 0, ',', '.') }}</td>
              <td class="px-5 py-3.5 text-center">
                <button type="button"
                        class="btn-detail w-9 h-9 rounded-lg bg-slate-100 hover:bg-slate-200 inline-flex items-center justify-center transition active:scale-90"
                        data-detail-url="{{ route('admin.reports.detail', $r->id) }}">
                  <i data-lucide="eye" class="w-4 h-4 text-slate-600"></i>
                </button>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="11" class="px-5 py-10 text-center text-slate-400">No reservation data found.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 px-5 py-4 border-t border-slate-100">
      <p class="text-xs text-slate-500">
        Showing {{ $reservations->firstItem() ?? 0 }} to {{ $reservations->lastItem() ?? 0 }} of {{ $reservations->total() }} results
      </p>
      <div class="flex items-center gap-1.5">
        {{ $reservations->onEachSide(1)->links() }}
      </div>
    </div>
  </div>
</div>

<div id="drawerOverlay" class="drawer-overlay fixed inset-0 bg-slate-900/40 backdrop-blur-[2px] z-40 opacity-0 pointer-events-none"></div>

<div id="drawerPanel" class="drawer-panel fixed top-0 right-0 h-full w-full sm:w-[420px] bg-white z-50 shadow-2xl translate-x-full flex flex-col">
  <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">
    <h2 class="text-lg font-semibold text-slate-900">Reservation Detail</h2>
    <button id="drawerCloseBtn" class="w-9 h-9 rounded-full hover:bg-slate-100 flex items-center justify-center transition">
      <i data-lucide="x" class="w-5 h-5 text-slate-500"></i>
    </button>
  </div>

  <div class="flex-1 overflow-y-auto px-6 py-5 space-y-6" id="drawerContent">
    <div>
      <div class="flex items-center gap-2 mb-3">
        <i data-lucide="user" class="w-4 h-4 text-slate-500"></i>
        <h3 class="text-sm font-semibold text-slate-800">Guest Information</h3>
      </div>
      <div class="space-y-2.5 text-sm" id="guestInfo"></div>
    </div>

    <div class="h-px bg-slate-100"></div>

    <div>
      <div class="flex items-center gap-2 mb-3">
        <i data-lucide="calendar-days" class="w-4 h-4 text-slate-500"></i>
        <h3 class="text-sm font-semibold text-slate-800">Reservation Information</h3>
      </div>
      <div class="space-y-2.5 text-sm" id="reservationInfo"></div>
    </div>

    <div class="h-px bg-slate-100"></div>

    <div>
      <div class="flex items-center gap-2 mb-3">
        <i data-lucide="credit-card" class="w-4 h-4 text-slate-500"></i>
        <h3 class="text-sm font-semibold text-slate-800">Payment Information</h3>
      </div>
      <div class="space-y-2.5 text-sm" id="paymentInfo"></div>
    </div>

    <div class="h-px bg-slate-100"></div>

    <div>
      <div class="flex items-center gap-2 mb-3">
        <i data-lucide="info" class="w-4 h-4 text-slate-500"></i>
        <h3 class="text-sm font-semibold text-slate-800">Other Information</h3>
      </div>
      <div class="space-y-2.5 text-sm" id="otherInfo"></div>
    </div>
  </div>

  <div class="px-6 py-4 border-t border-slate-100">
    <button id="drawerCloseBtnBottom" class="w-full btn-gradient-blue" style="justify-content: center;">
      <i data-lucide="x" class="w-4 h-4"></i>
      Close
    </button>
  </div>
</div>

<div id="toastBox" class="fixed bottom-6 left-1/2 -translate-x-1/2 bg-slate-900 text-white text-sm font-medium px-5 py-3 rounded-xl shadow-xl opacity-0 pointer-events-none transition-all z-50 flex items-center gap-2">
  <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400"></i>
  <span id="toastMsg">Done</span>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/lucide@latest"></script>
<script src="{{ asset('js/admin/report.js') }}"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (window.lucide) {
      lucide.createIcons();
    }
  });
</script>
@endpush
