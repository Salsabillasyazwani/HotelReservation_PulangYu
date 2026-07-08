<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Promotion;
use Illuminate\Http\Request;

class CustomerSearchController extends Controller
{
    public function search(Request $request)
    {
        $q = $request->input('q');

        if (!$q || strlen($q) < 2) {
            return response()->json([]);
        }

        $results = [];

        /*cari berdasarkan nama tipe kamar, misal "Family Room"*/
        $roomTypes = RoomType::where('name', 'like', "%{$q}%")
            ->limit(5)
            ->get();

        foreach ($roomTypes as $type) {
            $sampleRoom = Room::where('room_type_id', $type->id)->first();

            $results[] = [
                'type'  => 'Room Type',
                'label' => $type->name,
                'url'   => route('customer.rooms.index', [
                    'highlight' => $sampleRoom?->id ?? '',
                ]),
            ];
        }

        /*cari berdasarkan nomor kamar atau nama kamar*/
        $rooms = Room::where('room_number', 'like', "%{$q}%")
            ->orWhere('room_name', 'like', "%{$q}%")
            ->limit(5)
            ->get(['id', 'room_number', 'room_name']);

        foreach ($rooms as $room) {
            $results[] = [
                'type'  => 'Room',
                'label' => ($room->room_name ?? 'Kamar') . ' (No. ' . $room->room_number . ')',
                'url'   => route('customer.rooms.index', ['highlight' => $room->id]),
            ];
        }

        /*cari promo*/
        $promotions = Promotion::where('promo_name', 'like', "%{$q}%")
            ->orWhere('promo_code', 'like', "%{$q}%")
            ->limit(5)
            ->get(['id', 'promo_name']);

        foreach ($promotions as $promo) {
            $results[] = [
                'type'  => 'Promotion',
                'label' => $promo->promo_name,
                'url'   => route('customer.rooms.index', ['highlight' => $promo->id]),
            ];
        }

        return response()->json($results);
    }
}
