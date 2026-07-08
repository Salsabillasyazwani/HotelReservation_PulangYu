<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Models\Reservation;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustomerReservationController extends Controller
{
    /**
     * List semua reservasi milik customer yang sedang login.
     */
    public function index()
    {
        $reservations = Reservation::with(['room.roomType', 'promotion'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('customer.reservations.index', compact('reservations'));
    }

    /**
     * AJAX: ambil daftar kamar yang benar-benar available untuk rentang
     * tanggal yang dipilih di modal Book Reservation.
     * Dipanggil saat user mengubah check_in / check_out di modal, sebelum
     * mereka memilih kamar mana yang mau dipesan.
     */
    public function availableRooms(Request $request)
    {
        $validated = $request->validate([
            'check_in'  => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'guests'    => ['nullable', 'integer', 'min:1'],
        ]);

        $rooms = Room::with('roomType.facilityLinks')
            ->where('status', 'Available')
            ->when($validated['guests'] ?? null, function ($query, $guests) {
                $query->where('capacity', '>=', $guests);
            })
            ->get()
            ->reject(function (Room $room) use ($validated) {
                return Reservation::hasOverlap($room->id, $validated['check_in'], $validated['check_out']);
            })
            ->values()
            ->map(function (Room $room) {
                return [
                    'id'            => $room->id,
                    'room_type_id'  => $room->room_type_id,
                    'room_number'   => $room->room_number,
                    'room_name'     => $room->room_name,
                    'room_type'     => $room->roomType->name,
                    'floor'         => $room->floor,
                    'price'         => (float) $room->price,
                    'capacity'      => $room->capacity,
                    'image_url'     => $room->image_url,
                    'facilities'    => $room->roomType->facilities,
                ];
            });

        return response()->json([
            'success' => true,
            'rooms'   => $rooms,
        ]);
    }

    /**
     * Simpan reservasi baru dari modal Book Reservation.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id'          => ['required', 'exists:rooms,id'],
            'check_in'         => ['required', 'date', 'after_or_equal:today'],
            'check_out'        => ['required', 'date', 'after:check_in'],
            'guests'           => ['required', 'integer', 'min:1'],
            'phone'            => ['required', 'string', 'max:20'],
            'identity_number'  => ['required', 'string', 'max:50'],
            'nationality'      => ['nullable', 'string', 'max:100'],
            'promo_code'       => ['nullable', 'string', 'max:50'],
            'special_request'  => ['nullable', 'string', 'max:1000'],
        ]);

        $room = Room::with('roomType')->findOrFail($validated['room_id']);

        if ($room->status !== 'Available') {
            return response()->json([
                'success' => false,
                'message' => 'Room ini sedang tidak tersedia.',
            ], 422);
        }

        if ($validated['guests'] > $room->capacity) {
            return response()->json([
                'success' => false,
                'message' => "Jumlah tamu melebihi kapasitas room ini (maks {$room->capacity} orang).",
            ], 422);
        }

        if (Reservation::hasOverlap($room->id, $validated['check_in'], $validated['check_out'])) {
            return response()->json([
                'success' => false,
                'message' => 'Room sudah dipesan pada rentang tanggal tersebut. Silakan pilih kamar atau tanggal lain.',
            ], 422);
        }

        $nights   = max(1, Carbon::parse($validated['check_in'])->diffInDays($validated['check_out']));
        $subtotal = $room->price * $nights;

        $promotion = null;

        if (!empty($validated['promo_code'])) {
            $promotion = Promotion::where('promo_code', $validated['promo_code'])->first();

            if (!$promotion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode promo tidak ditemukan.',
                ], 422);
            }

            [$isValid, $reason] = $promotion->isApplicableTo($room->room_type_id, $subtotal);

            if (!$isValid) {
                return response()->json([
                    'success' => false,
                    'message' => $reason,
                ], 422);
            }
        }

        $reservation = DB::transaction(function () use ($validated, $room, $promotion) {
            $reservation = new Reservation([
                'user_id'             => Auth::id(),
                'room_id'             => $room->id,
                'promotion_id'        => $promotion?->id,
                'guest_name'          => Auth::user()->name,
                'phone'               => $validated['phone'],
                'email'               => Auth::user()->email,
                'identity_number'     => $validated['identity_number'],
                'nationality'         => $validated['nationality'] ?? 'Indonesia',
                'check_in'            => $validated['check_in'],
                'check_out'           => $validated['check_out'],
                'guests'              => $validated['guests'],
                'reservation_status'  => 'Pending',
                'payment_status'      => 'Unpaid',
                'price_per_night'     => $room->price,
                'special_request'     => $validated['special_request'] ?? null,
            ]);

            $reservation->calculateTotals();
            $reservation->save();

            if ($promotion && !is_null($promotion->quota)) {
                $promotion->decrement('quota');
            }

            return $reservation;
        });

        return response()->json([
            'success' => true,
            'message' => 'Reservasi berhasil dibuat, menunggu konfirmasi.',
            'code'    => $reservation->reservation_code,
        ]);
    }

    /**
     * Batalkan reservasi milik sendiri.
     */
    public function cancel(Request $request, Reservation $reservation)
    {
        abort_if($reservation->user_id !== Auth::id(), 403);

        if (!in_array($reservation->reservation_status, ['Pending', 'Confirmed'])) {
            return response()->json([
                'success' => false,
                'message' => 'Reservasi dengan status ini tidak bisa dibatalkan.',
            ], 422);
        }

        $reservation->update([
            'reservation_status'  => 'Cancelled',
            'cancellation_reason' => $request->input('reason', 'Dibatalkan oleh customer'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reservasi berhasil dibatalkan.',
        ]);
    }

    /**
     * AJAX: cek validitas kode promo untuk room type & subtotal tertentu.
     * Dipanggil dari modal Book Reservation saat user ketik kode promo.
     */
    public function validatePromo(Request $request)
    {
        $validated = $request->validate([
            'promo_code'   => ['required', 'string', 'max:50'],
            'room_type_id' => ['required', 'exists:room_types,id'],
            'subtotal'     => ['required', 'numeric', 'min:0'],
        ]);

        $promotion = Promotion::where('promo_code', $validated['promo_code'])->first();

        if (!$promotion) {
            return response()->json([
                'valid'   => false,
                'message' => 'Kode promo tidak ditemukan.',
            ]);
        }

        [$isValid, $reason] = $promotion->isApplicableTo($validated['room_type_id'], $validated['subtotal']);

        if (!$isValid) {
            return response()->json([
                'valid'   => false,
                'message' => $reason,
            ]);
        }

        $discount = $promotion->calculateDiscount($validated['subtotal']);

        return response()->json([
            'valid'      => true,
            'discount'   => $discount,
            'promo_code' => $promotion->promo_code,
            'promo_name' => $promotion->promo_name,
        ]);
    }
}
