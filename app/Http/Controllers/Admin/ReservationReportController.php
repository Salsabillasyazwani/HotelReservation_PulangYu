<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReservationsExport;

class ReservationReportController extends Controller
{
    public function index(Request $request)
    {
        $reservations = Reservation::with('room.roomType')
            ->search($request->get('search'))
            ->status($request->get('status'))
            ->paymentStatus($request->get('payment_status'))
            ->roomType($request->get('room_type'))
            ->dateRange($request->get('date_from'), $request->get('date_to'))
            ->orderByDesc('check_in')
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total'       => Reservation::count(),
            'revenue'     => (float) Reservation::where('payment_status', 'Paid')->sum('total_amount'),
            'checked_in'  => Reservation::where('reservation_status', 'Checked In')->count(),
            'checked_out' => Reservation::where('reservation_status', 'Checked Out')->count(),
        ];

        $roomTypes = RoomType::where('status', 'active')->pluck('name');

        return view('admin.report', compact('reservations', 'stats', 'roomTypes'));
    }

    public function detail(Reservation $reservation)
    {
        return response()->json($reservation->load('room.roomType'));
    }

    public function export(Request $request)
    {
        $type = $request->query('type', 'excel');

        $reservations = Reservation::with('room.roomType')
            ->search($request->get('search'))
            ->status($request->get('status'))
            ->paymentStatus($request->get('payment_status'))
            ->roomType($request->get('room_type'))
            ->dateRange($request->get('date_from'), $request->get('date_to'))
            ->orderByDesc('check_in')
            ->get();

        if ($type == 'pdf') {
            $pdf = Pdf::loadView('admin.report-export-pdf', compact('reservations'));
            $pdf->setPaper('A4', 'landscape');

            return $pdf->download('Reservation-Report.pdf');
        }

        if ($type == 'excel') {
            return Excel::download(new ReservationsExport($reservations), 'Reservation-Report.xlsx');
        }

        abort(404, 'Format export tidak didukung.');
    }
}
