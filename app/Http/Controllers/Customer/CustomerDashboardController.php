<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Promotion;
use App\Models\Room;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CustomerDashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $today  = Carbon::today();

        // STATS CARDS
        $totalReservation = Reservation::where('user_id', $userId)->count();

        $upcomingStay = Reservation::where('user_id', $userId)
            ->where('check_in', '>=', $today)
            ->whereNotIn('reservation_status', ['Cancelled', 'Checked Out'])
            ->count();

        $completedStay = Reservation::where('user_id', $userId)
            ->where('reservation_status', 'Checked Out')
            ->count();
        $startThisMonth = $today->copy()->startOfMonth();
        $startLastMonth = $today->copy()->subMonthNoOverflow()->startOfMonth();
        $endLastMonth   = $today->copy()->subMonthNoOverflow()->endOfMonth();

        $totalReservationLastMonth = Reservation::where('user_id', $userId)
            ->whereBetween('created_at', [$startLastMonth, $endLastMonth])
            ->count();

        $totalReservationThisMonth = Reservation::where('user_id', $userId)
            ->where('created_at', '>=', $startThisMonth)
            ->count();

        $totalReservationGrowth = $this->calculateGrowthPercent(
            $totalReservationThisMonth,
            $totalReservationLastMonth
        );

        // RECENT RESERVATION (table)

        $reservations = Reservation::with(['room.roomType'])
            ->where('user_id', $userId)
            ->orderByDesc('check_in')
            ->take(5)
            ->get()
            ->map(function (Reservation $res) {
                return [
                    'room_type'   => optional(optional($res->room)->roomType)->name ?? ($res->room->type ?? '-'),
                    'room_number' => optional($res->room)->room_number ?? '-',
                    'check_in'    => $res->check_in?->format('d M Y') ?? '-',
                    'check_out'   => $res->check_out?->format('d M Y') ?? '-',
                    'status'      => $res->reservation_status,
                ];
            });

        // 3. PROMOTIONS (aktif hari ini)
        $promotions = Promotion::where('status', 'Active')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->orderByDesc('created_at')
            ->take(2)
            ->get();

        // RECOMMENDED ROOMS (available)
        $rooms = Room::with('roomType')
            ->where('status', 'Available')
            ->orderByDesc('id')
            ->take(8)
            ->get();

        return view('customer.dashboard', compact(
            'totalReservation',
            'upcomingStay',
            'completedStay',
            'totalReservationGrowth',
            'reservations',
            'promotions',
            'rooms'
        ));
    }

    private function calculateGrowthPercent(int $current, int $previous): float
    {
        if ($previous === 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
