<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Reservation;
use App\Models\Promotion;
use App\Models\Facility;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $q = trim($request->input('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $results = [];

        /* -------------------- Room Type -------------------- */
        // NB: RoomTypeController tidak punya method show(), jadi
        // diarahkan ke index dengan query highlight.
        $roomTypes = RoomType::where('name', 'like', "%{$q}%")
            ->limit(5)
            ->get(['id', 'name']);

        foreach ($roomTypes as $type) {
            $results[] = [
                'type'     => 'Room Type',
                'title'    => $type->name,
                'subtitle' => '',
                'url'      => route('admin.room-types.index', ['highlight' => $type->id]),
            ];
        }

        /* -------------------- Room -------------------- */
        // NB: sementara diarahkan ke index+highlight juga, karena belum
        // dikonfirmasi apakah RoomController punya method show().
        // Jika RoomController->show() memang ada, ganti ke:
        // route('admin.room.show', $room->id)
        $rooms = Room::where('room_name', 'like', "%{$q}%")
            ->orWhere('room_number', 'like', "%{$q}%")
            ->limit(5)
            ->get(['id', 'room_name', 'room_number']);

        foreach ($rooms as $room) {
            $results[] = [
                'type'     => 'Room',
                'title'    => $room->room_name,
                'subtitle' => 'No. ' . $room->room_number,
                'url'      => route('admin.room.index', ['highlight' => $room->id]),
            ];
        }

        /* -------------------- Reservation -------------------- */
        // NB: sementara diarahkan ke index+highlight juga, karena belum
        // dikonfirmasi apakah ReservationController punya method show().
        // Jika ReservationController->show() memang ada, ganti ke:
        // route('admin.reservations.show', $res->id)
        $reservations = Reservation::search($q)
            ->limit(5)
            ->get(['id', 'guest_name', 'reservation_code']);

        foreach ($reservations as $res) {
            $results[] = [
                'type'     => 'Reservation',
                'title'    => $res->guest_name,
                'subtitle' => $res->reservation_code,
                'url'      => route('admin.reservations.index', ['highlight' => $res->id]),
            ];
        }

        /* -------------------- Promotion -------------------- */
        // Route 'show' memang tidak ada (resource except show),
        // jadi diarahkan ke index dengan query highlight.
        $promotions = Promotion::search($q)
            ->limit(5)
            ->get(['id', 'promo_name', 'promo_code']);

        foreach ($promotions as $promo) {
            $results[] = [
                'type'     => 'Promotion',
                'title'    => $promo->promo_name,
                'subtitle' => $promo->promo_code,
                'url'      => route('admin.promotions.index', ['highlight' => $promo->id]),
            ];
        }

        /* -------------------- Facility -------------------- */
        // NB: sementara diarahkan ke index+highlight juga, karena belum
        // dikonfirmasi apakah FacilityController punya method show().
        // Jika FacilityController->show() memang ada, ganti ke:
        // route('admin.facilities.show', $facility->id)
        $facilities = Facility::where('name', 'like', "%{$q}%")
            ->where('status', 'Active')
            ->limit(5)
            ->get(['id', 'name']);

        foreach ($facilities as $facility) {
            $results[] = [
                'type'     => 'Facility',
                'title'    => $facility->name,
                'subtitle' => '',
                'url'      => route('admin.facilities.index', ['highlight' => $facility->id]),
            ];
        }

        return response()->json(['results' => $results]);
    }
}
