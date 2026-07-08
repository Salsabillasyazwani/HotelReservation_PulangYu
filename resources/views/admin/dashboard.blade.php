@extends('layouts.admin')

@section('title', 'Hotel Pulang Yo - Dashboard Admin')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
@endpush

@section('content')

<div class="page-header">
    <div>
        <h2>Dashboard</h2>
        <p>
            Welcome back,
            <strong>{{ auth()->user()->name }}</strong>
            👋
        </p>
    </div>

    <div class="date-picker" id="datePickerToggle">
        <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2"/>
            <path d="M16 2v4"/>
            <path d="M8 2v4"/>
            <path d="M3 10h18"/>
        </svg>

        <span id="datePickerLabel">
            {{ $dateFrom->format('M d, Y') }} - {{ $dateTo->format('M d, Y') }}
        </span>

        <span class="chev">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
                <path d="M6 9l6 6 6-6"/>
            </svg>
        </span>

        <div class="date-picker-panel" id="datePickerPanel">
            <form method="GET" action="{{ route('admin.dashboard') }}" onclick="event.stopPropagation()">
                <label>Dari</label>
                <input type="date" name="date_from" value="{{ $dateFrom->format('Y-m-d') }}">

                <label>Sampai</label>
                <input type="date" name="date_to" value="{{ $dateTo->format('Y-m-d') }}">

                <button type="submit">Terapkan</button>
            </form>
        </div>
    </div>
</div>

<div class="stats-grid">

    <div class="stat-card">
        <div class="stat-top">
            <div class="stat-icon blue">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
                    <path d="M3 18v-6a2 2 0 012-2h14a2 2 0 012 2v6"/>
                    <path d="M3 18h18"/>
                    <path d="M3 18v2"/>
                    <path d="M21 18v2"/>
                    <path d="M5 10V7a2 2 0 012-2h3a2 2 0 012 2v3"/>
                </svg>
            </div>
            <span class="stat-menu">⋮</span>
        </div>
        <div class="stat-label">Total Room</div>
        <div class="stat-value">{{ $totalRoom }}</div>
        <div class="stat-trend">
            <span class="{{ $roomGrowth >= 0 ? 'up' : 'down' }}">
                {{ $roomGrowth >= 0 ? '↑' : '↓' }} {{ abs($roomGrowth) }}%
            </span>
            from last month
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <div class="stat-icon purple">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
                    <circle cx="9" cy="8" r="3"/>
                    <path d="M2 20c0-3.5 3-6 7-6s7 2.5 7 6"/>
                    <circle cx="17" cy="9" r="2.5"/>
                    <path d="M22 20c0-2.5-2-4.5-4.5-5"/>
                </svg>
            </div>
            <span class="stat-menu">⋮</span>
        </div>
        <div class="stat-label">Total Guest</div>
        <div class="stat-value">{{ $totalGuest }}</div>
        <div class="stat-trend">
            <span class="{{ $guestGrowth >= 0 ? 'up' : 'down' }}">
                {{ $guestGrowth >= 0 ? '↑' : '↓' }} {{ abs($guestGrowth) }}%
            </span>
            from last month
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <div class="stat-icon orange">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2"/>
                    <path d="M16 2v4"/>
                    <path d="M8 2v4"/>
                    <path d="M3 10h18"/>
                    <path d="M8 14h2"/>
                    <path d="M8 17h2"/>
                    <path d="M14 14h2"/>
                    <path d="M14 17h2"/>
                </svg>
            </div>
            <span class="stat-menu">⋮</span>
        </div>
        <div class="stat-label">Reservation</div>
        <div class="stat-value">{{ $totalReservation }}</div>
        <div class="stat-trend">
            <span class="{{ $reservationGrowth >= 0 ? 'up' : 'down' }}">
                {{ $reservationGrowth >= 0 ? '↑' : '↓' }} {{ abs($reservationGrowth) }}%
            </span>
            from last month
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <div class="stat-icon green">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
                    <rect x="2" y="6" width="20" height="12" rx="2"/>
                    <circle cx="12" cy="12" r="2.5"/>
                </svg>
            </div>
            <span class="stat-menu">⋮</span>
        </div>
        <div class="stat-label">Revenue</div>
        <div class="stat-value">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
        <div class="stat-trend">
            <span class="{{ $revenueGrowth >= 0 ? 'up' : 'down' }}">
                {{ $revenueGrowth >= 0 ? '↑' : '↓' }} {{ abs($revenueGrowth) }}%
            </span>
            from last month
        </div>
    </div>

</div>

<div class="charts-grid">

    <div class="chart-card">
        <div class="chart-head">
            <h3>Reservation Overview</h3>
            <div class="period-select">
                This Year
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
                    <path d="M6 9l6 6 6-6"/>
                </svg>
            </div>
        </div>

        <div class="legend">
            <span>
                <span class="dot solid"></span>
                Reservation
            </span>
        </div>

        <div class="chart-wrap">
            <canvas id="reservationChart"></canvas>
        </div>
    </div>

    <div class="chart-card">
        <div class="chart-head">
            <h3>Revenue Overview</h3>
            <div class="period-select">
                This Year
                <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
                    <path d="M6 9l6 6 6-6"/>
                </svg>
            </div>
        </div>

        <div style="height:20px"></div>

        <div class="chart-wrap">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

</div>

<div class="bottom-grid">

    <div class="table-card">
        <div class="table-head">
            <h3>Recent Reservation</h3>
            <a href="{{ route('admin.reservations.index') }}" class="view-all">View All</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Reservation ID</th>
                    <th>Guest</th>
                    <th>Room</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reservations as $reservation)
                    <tr>
                        <td class="res-id">{{ $reservation->reservation_code }}</td>
                        <td>
                            <div class="guest-cell">
                                <div class="guest-avatar"
                                    style="background-image:url('https://ui-avatars.com/api/?name={{ urlencode($reservation->guest_name) }}')">
                                </div>
                                <span class="guest-name">{{ $reservation->guest_name }}</span>
                            </div>
                        </td>
                        <td>
                            {{ $reservation->room->room_number ?? '-' }}
                            @if($reservation->room && $reservation->room->roomType)
                                - {{ $reservation->room->roomType->name }}
                            @endif
                        </td>
                        <td>{{ \Carbon\Carbon::parse($reservation->check_in)->format('d M Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($reservation->check_out)->format('d M Y') }}</td>
                        <td>
                            <span class="status-pill status-{{ strtolower(str_replace(' ', '', $reservation->reservation_status)) }}">
                                {{ $reservation->reservation_status }}
                            </span>
                        </td>
                        <td>
                            <div class="action-cell">
                                <a href="{{ route('admin.reservations.show', $reservation->id) }}">👁</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:40px;">No reservation data available.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="side-col">

        <div class="activity-card">
            <h3>Today's Activity</h3>

            <div class="activity-item">
                <div class="act-icon blue">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2"/>
                        <path d="M16 2v4"/>
                        <path d="M8 2v4"/>
                        <path d="M3 10h18"/>
                    </svg>
                </div>
                <div class="act-body">
                    <div class="act-title">New Reservation</div>
                    <div class="act-sub">{{ $newReservationToday }} New Reservations</div>
                </div>
                <div class="act-right">
                    <span class="act-time">{{ now()->format('H:i') }}</span>
                    <span class="act-dot blue"></span>
                </div>
            </div>

            <div class="activity-item">
                <div class="act-icon orange">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
                        <circle cx="12" cy="12" r="9"/>
                        <path d="M9 12l2 2 4-4"/>
                    </svg>
                </div>
                <div class="act-body">
                    <div class="act-title">Check In</div>
                    <div class="act-sub">{{ $checkInToday }} Guests Checked In</div>
                </div>
                <div class="act-right">
                    <span class="act-time">{{ now()->format('H:i') }}</span>
                    <span class="act-dot orange"></span>
                </div>
            </div>

            <div class="activity-item">
                <div class="act-icon green">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2">
                        <rect x="2" y="6" width="20" height="12" rx="2"/>
                        <circle cx="12" cy="12" r="2.5"/>
                    </svg>
                </div>
                <div class="act-body">
                    <div class="act-title">Today's Revenue</div>
                    <div class="act-sub">Rp {{ number_format($paymentToday, 0, ',', '.') }}</div>
                </div>
                <div class="act-right">
                    <span class="act-time">{{ now()->format('H:i') }}</span>
                    <span class="act-dot green"></span>
                </div>
            </div>
        </div>

        <div class="occupancy-card">
            <h3>Occupancy Rate</h3>

            <div class="occupancy-body">
                <div class="donut-wrap">
                    <canvas id="occupancyChart"></canvas>
                    <div class="donut-center">{{ $occupancyRate }}%</div>
                </div>

                <div>
                    <div class="occ-info-label">Occupied Room</div>
                    <div class="occ-info-value">{{ $occupiedRoom }} / {{ $totalRoom }}</div>
                    <div class="occ-info-trend">
                        <span class="up">{{ $occupancyRate }}%</span>
                        Occupancy
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

@endsection

@push('scripts')
<script>
window.dashboardData = {
    reservationChart: @json($reservationChart),
    revenueChart: @json($revenueChart),
    occupancyRate: {{ $occupancyRate }},
    occupiedRoom: {{ $occupiedRoom }},
    totalRoom: {{ $totalRoom }}
};
</script>
<script src="{{ asset('js/admin/dashboard.js') }}"></script>
@endpush
