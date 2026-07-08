<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;

class CustomerRoomController extends Controller
{
    public function index(Request $request)
    {
       $rooms = Room::with('roomType')
        ->orderBy('room_number')
        ->get();

        $roomTypes = RoomType::where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('customer.rooms.index', compact('rooms', 'roomTypes'));
    }
}
