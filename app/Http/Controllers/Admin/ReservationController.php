<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $roomTypes = RoomType::where('status', 'active')->pluck('name');

        $rooms = Room::orderBy('room_number')->get();

        $stats = $this->buildStats();

        $reservations = Reservation::with('room.roomType')
            ->status($request->get('status'))
            ->paymentStatus($request->get('payment_status'))
            ->roomType($request->get('room_type'))
            ->dateRange($request->get('date_from'), $request->get('date_to'))
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.reservations.index', compact('stats', 'roomTypes', 'rooms', 'reservations'));
    }

    /**
     * Endpoint AJAX untuk mengisi tabel (dipanggil dari reservations/script.js)
     */
    public function data(Request $request)
    {
        $query = Reservation::with('room.roomType')
            ->status($request->get('status'))
            ->paymentStatus($request->get('payment_status'))
            ->roomType($request->get('room_type'))
            ->dateRange($request->get('date_from'), $request->get('date_to'));

        $sort = $request->get('sort', 'id');
        $dir  = $request->get('dir', 'desc');

        $sortable = [
            'id'            => 'id',
            'guest'         => 'guest_name',
            'checkIn'       => 'check_in',
            'checkOut'      => 'check_out',
            'guests'        => 'guests',
            'total'         => 'total_amount',
            'paymentStatus' => 'payment_status',
            'status'        => 'reservation_status',
        ];

        $query->orderBy($sortable[$sort] ?? 'id', $dir === 'asc' ? 'asc' : 'desc');

        $reservations = $query->paginate($request->get('per_page', 10));

        return response()->json($reservations);
    }

    private function buildStats(): array
    {
        $counts = Reservation::select('reservation_status', DB::raw('count(*) as total'))
            ->groupBy('reservation_status')
            ->pluck('total', 'reservation_status');

        $total = max(1, $counts->sum());

        $map = [
            'pending'     => 'Pending',
            'confirmed'   => 'Confirmed',
            'checked_in'  => 'Checked In',
            'checked_out' => 'Checked Out',
            'cancelled'   => 'Cancelled',
        ];

        $stats = [];
        foreach ($map as $key => $label) {
            $value = (int) ($counts[$label] ?? 0);
            $stats[$key] = $value;
            $stats["{$key}_percent"] = round(($value / $total) * 100);
        }

        return $stats;
    }

    public function create()
    {
        $reservationId = Reservation::generateCode();

        $roomTypes = RoomType::where('status', 'active')->get();

        $roomTypeDefault = $roomTypes->first();

        $availableRooms = $roomTypeDefault
            ? $this->availableRoomNumbers($roomTypeDefault->name)
            : [];

        return view('admin.reservations.create', compact('reservationId', 'roomTypes', 'availableRooms'));
    }


    public function getAvailableRooms(Request $request)
    {
        $request->validate([
            'room_type' => 'required|string',
            'check_in'  => 'nullable|date',
            'check_out' => 'nullable|date|after_or_equal:check_in',
        ]);

        $rooms = Room::with('roomType')
            ->whereHas('roomType', fn ($q) => $q->where('name', $request->room_type))
            ->where('status', 'Available')
            ->get()
            ->filter(function (Room $room) use ($request) {
                if (!$request->check_in || !$request->check_out) return true;
                return !Reservation::hasOverlap($room->id, $request->check_in, $request->check_out);
            })
            ->values()
            ->map(fn (Room $room) => [
                'id'          => $room->id,
                'room_number' => $room->room_number,
                'room_name'   => $room->room_name,
                'price'       => $room->price,
                'capacity'    => $room->capacity,
                'image'       => $room->image,
                'status'      => $room->status,
            ]);

        return response()->json($rooms);
    }

    private function availableRoomNumbers(string $roomTypeName): array
    {
        return Room::with('roomType')
            ->whereHas('roomType', fn ($q) => $q->where('name', $roomTypeName))
            ->where('status', 'Available')
            ->pluck('room_number')
            ->toArray();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'guest_name'          => 'required|string|max:255',
            'phone'               => 'required|string|max:30',
            'email'               => 'nullable|email|max:255',
            'identity_number'     => 'required|string|max:50',
            'nationality'         => 'required|string|max:100',
            'check_in'            => 'required|date',
            'check_out'           => 'required|date|after:check_in',
            'guests'              => 'required|integer|min:1',
            'reservation_status'  => 'required|in:Pending,Confirmed,Cancelled',
            'room_type'           => 'required|string',
            'available_room'      => 'required|string',
            'payment_method'      => 'nullable|string',
            'payment_status'      => 'required|in:Paid,Unpaid',
            'deposit'             => 'nullable|numeric|min:0',
            'special_request'     => 'nullable|string',
            'notes'               => 'nullable|string',
        ]);

        $room = Room::with('roomType')
            ->where('room_number', $validated['available_room'])
            ->firstOrFail();

        if (Reservation::hasOverlap($room->id, $validated['check_in'], $validated['check_out'])) {
            return response()->json([
                'message' => 'Kamar ini sudah dipesan pada rentang tanggal tersebut.',
                'errors'  => [
                    'available_room' => ['Kamar ini sudah dipesan pada rentang tanggal tersebut.'],
                ],
            ], 422);
        }

        $reservation = new Reservation([
            'guest_name'         => $validated['guest_name'],
            'phone'              => $validated['phone'],
            'email'              => $validated['email'] ?? null,
            'identity_number'    => $validated['identity_number'],
            'nationality'        => $validated['nationality'],
            'room_id'            => $room->id,
            'check_in'           => $validated['check_in'],
            'check_out'          => $validated['check_out'],
            'guests'             => $validated['guests'],
            'reservation_status' => $validated['reservation_status'],
            'payment_method'     => $validated['payment_method'] ?? null,
            'payment_status'     => $validated['payment_status'],
            'price_per_night'    => $room->price,
            'deposit'            => $validated['deposit'] ?? 0,
            'special_request'    => $validated['special_request'] ?? null,
            'notes'              => $validated['notes'] ?? null,
        ]);

        $reservation->calculateTotals();

        DB::transaction(function () use ($reservation, $room, $request) {
            $reservation->save();

            if ($request->input('action') === 'save_checkin') {
                $reservation->update([
                    'reservation_status' => 'Checked In',
                    'actual_check_in'     => now(),
                ]);
                $room->update(['status' => 'Occupied']);
            } else {
                $room->update(['status' => 'Booked']);
            }
        });

        return response()->json([
            'message'     => "Reservasi {$reservation->reservation_code} berhasil dibuat.",
            'reservation' => $reservation->fresh('room.roomType'),
        ]);
    }

    public function show(Reservation $reservation)
    {
        return response()->json($reservation->load('room.roomType'));
    }

    public function update(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'guest_name'         => 'required|string|max:255',
            'email'              => 'required|email|max:255',
            'phone'              => 'required|string|max:30',
            'nationality'        => 'nullable|string|max:100',
            'check_in'           => 'required|date',
            'check_out'          => 'required|date|after:check_in',
            'room_number'        => 'required|string',
            'guests'             => 'required|integer|min:1|max:10',
            'reservation_status' => ['required', Rule::in(['Pending','Confirmed','Checked In','Checked Out','Cancelled'])],
            'payment_status'     => ['required', Rule::in(['Paid','Unpaid','Partial','Refunded'])],
            'special_request'    => 'nullable|string',
        ]);

        $room = Room::where('room_number', $validated['room_number'])->firstOrFail();

        if (Reservation::hasOverlap($room->id, $validated['check_in'], $validated['check_out'], $reservation->id)) {
            return response()->json(['message' => 'Kamar sudah dipesan pada rentang tanggal tersebut.'], 422);
        }

        $reservation->fill([
            'guest_name'         => $validated['guest_name'],
            'email'              => $validated['email'],
            'phone'              => $validated['phone'],
            'nationality'        => $validated['nationality'] ?? $reservation->nationality,
            'room_id'            => $room->id,
            'check_in'           => $validated['check_in'],
            'check_out'          => $validated['check_out'],
            'guests'             => $validated['guests'],
            'reservation_status' => $validated['reservation_status'],
            'payment_status'     => $validated['payment_status'],
            'special_request'    => $validated['special_request'] ?? null,
            'price_per_night'    => $room->price,
        ]);

        $reservation->calculateTotals();
        $reservation->save();

        return response()->json([
            'message'     => 'Reservasi berhasil diperbarui.',
            'reservation' => $reservation->load('room.roomType'),
        ]);
    }

    public function checkin(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'actual_check_in' => 'required|date',
            'notes'           => 'nullable|string',
        ]);

        $reservation->update([
            'reservation_status' => 'Checked In',
            'actual_check_in'    => $validated['actual_check_in'],
            'notes'              => $validated['notes'] ?? $reservation->notes,
        ]);

        $reservation->room()->update(['status' => 'Occupied']);

        return response()->json([
            'message'     => 'Guest berhasil check in.',
            'reservation' => $reservation->fresh('room.roomType'),
        ]);
    }
    public function checkout(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'actual_check_out' => 'required|date',
            'additional_charges' => 'nullable|numeric|min:0',
            'notes'             => 'nullable|string',
        ]);

        $reservation->additional_charges = $validated['additional_charges'] ?? 0;
        $reservation->calculateTotals();
        $reservation->reservation_status = 'Checked Out';
        $reservation->actual_check_out = $validated['actual_check_out'];
        $reservation->notes = $validated['notes'] ?? $reservation->notes;
        $reservation->save();

        $reservation->room()->update(['status' => 'Available']);

        return response()->json([
            'message'     => 'Guest berhasil check out.',
            'reservation' => $reservation->fresh('room.roomType'),
        ]);
    }

    /**
     *  konfirmasi Cancel
     */
    public function cancel(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'cancel_reason' => 'required|string',
        ]);

        $reservation->update([
            'reservation_status'   => 'Cancelled',
            'cancellation_reason'  => $validated['cancel_reason'],
        ]);

        $reservation->room()->update(['status' => 'Available']);

        return response()->json([
            'message'     => 'Reservasi berhasil dibatalkan.',
            'reservation' => $reservation->fresh('room.roomType'),
        ]);
    }

    public function destroy(Reservation $reservation)
    {
        $reservation->delete();
        return response()->json(['message' => 'Reservasi berhasil dihapus.']);
    }
}
