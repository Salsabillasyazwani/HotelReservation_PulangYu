<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\User;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->filled('date_from')
            ? Carbon::parse($request->date_from)->startOfDay()
            : now()->startOfMonth();

        $dateTo = $request->filled('date_to')
            ? Carbon::parse($request->date_to)->endOfDay()
            : now()->endOfMonth();

        $rangeDays = $dateFrom->diffInDays($dateTo) + 1;

        $prevDateFrom = (clone $dateFrom)->subDays($rangeDays);
        $prevDateTo = (clone $dateFrom)->subDay()->endOfDay();

        $totalRoom = Room::count();
        $roomLastPeriod = Room::where('created_at', '<', $dateFrom)->count();
        $roomGrowth = $this->percentChange($roomLastPeriod, $totalRoom);

        $totalGuest = User::whereHas('role', function ($q) {
            $q->where('name', 'customer');
        })->count();

        $guestThisPeriod = User::whereHas('role', function ($q) {
            $q->where('name', 'customer');
        })->whereBetween('created_at', [$dateFrom, $dateTo])->count();

        $guestPrevPeriod = User::whereHas('role', function ($q) {
            $q->where('name', 'customer');
        })->whereBetween('created_at', [$prevDateFrom, $prevDateTo])->count();

        $guestGrowth = $this->percentChange($guestPrevPeriod, $guestThisPeriod);

        $totalReservation = Reservation::whereBetween('created_at', [$dateFrom, $dateTo])->count();
        $reservationPrevPeriod = Reservation::whereBetween('created_at', [$prevDateFrom, $prevDateTo])->count();
        $reservationGrowth = $this->percentChange($reservationPrevPeriod, $totalReservation);

        $totalRevenue = Reservation::where('payment_status', 'Paid')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->sum('total_amount');

        $revenuePrevPeriod = Reservation::where('payment_status', 'Paid')
            ->whereBetween('created_at', [$prevDateFrom, $prevDateTo])
            ->sum('total_amount');

        $revenueGrowth = $this->percentChange($revenuePrevPeriod, $totalRevenue);

        $occupiedRoom = Room::where('status', 'Occupied')->count();

        $occupancyRate = $totalRoom > 0
            ? round(($occupiedRoom / $totalRoom) * 100)
            : 0;

        $reservations = Reservation::with(['room.roomType'])
            ->latest()
            ->take(5)
            ->get();

        $today = Carbon::today();

        $checkedInToday = Reservation::whereDate('check_in', $today)
            ->where('reservation_status', 'Checked In')
            ->count();

        $checkInToday = $checkedInToday;

        $newReservationToday = Reservation::whereDate('created_at', $today)->count();

        $paymentToday = Reservation::where('payment_status', 'Paid')
            ->whereDate('updated_at', $today)
            ->sum('total_amount');

        $reservationRaw = Reservation::selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->whereYear('created_at', $today->year)
            ->groupBy('month')
            ->pluck('total', 'month');

        $revenueRaw = Reservation::selectRaw('MONTH(created_at) as month, SUM(total_amount) as total')
            ->where('payment_status', 'Paid')
            ->whereYear('created_at', $today->year)
            ->groupBy('month')
            ->pluck('total', 'month');

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        $reservationChart = [
            'labels' => $months,
            'data' => collect(range(1, 12))->map(fn ($m) => (int) ($reservationRaw[$m] ?? 0))->toArray(),
        ];

        $revenueChart = [
            'labels' => $months,
            'data' => collect(range(1, 12))->map(fn ($m) => (float) ($revenueRaw[$m] ?? 0))->toArray(),
        ];

        return view('admin.dashboard', compact(
            'totalRoom',
            'totalGuest',
            'totalReservation',
            'totalRevenue',
            'occupiedRoom',
            'occupancyRate',
            'reservations',
            'reservationChart',
            'revenueChart',
            'checkedInToday',
            'checkInToday',
            'newReservationToday',
            'paymentToday',
            'roomGrowth',
            'guestGrowth',
            'reservationGrowth',
            'revenueGrowth',
            'dateFrom',
            'dateTo'
        ));
    }

    private function percentChange($old, $new)
    {
        if ($old == 0) {
            return $new > 0 ? 100 : 0;
        }

        return round((($new - $old) / $old) * 100, 1);
    }
}
