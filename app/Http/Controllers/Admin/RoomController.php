<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $query = Room::with('roomType')->latest();

        if ($request->filled('type')) {
            $query->where('room_type_id', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('floor')) {
            $query->where('floor', $request->floor);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('room_number', 'like', '%' . $request->search . '%')
                  ->orWhere('room_name', 'like', '%' . $request->search . '%');
            });
        }

        $rooms = $query->paginate(10)->withQueryString();

        $totalRoom = Room::count();
        $availableRoom = Room::where('status', 'Available')->count();
        $occupiedRoom = Room::where('status', 'Occupied')->count();
        $maintenanceRoom = Room::where('status', 'Maintenance')->count();

        $occupancyRate = $totalRoom > 0
            ? round(($occupiedRoom / $totalRoom) * 100)
            : 0;

        $roomTypes = RoomType::all();

        $floors = Room::select('floor')
            ->distinct()
            ->orderBy('floor')
            ->pluck('floor');

        $roomTypePercentage = [];

        foreach ($roomTypes as $type) {

            $count = Room::where('room_type_id', $type->id)->count();

            $roomTypePercentage[$type->name] = $totalRoom > 0
                ? round(($count / $totalRoom) * 100)
                : 0;
        }

        return view('admin.room.index', compact(
            'rooms',
            'roomTypes',
            'floors',
            'totalRoom',
            'availableRoom',
            'occupiedRoom',
            'maintenanceRoom',
            'occupancyRate',
            'roomTypePercentage'
        ));
    }

    public function create()
    {
        return response()->json(RoomType::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'room_number'  => 'required|string|max:50|unique:rooms,room_number',
            'room_name'    => 'nullable|string|max:255',
            'floor'        => 'required|integer|min:1',
            'capacity'     => 'required|integer|min:1',
            'price'        => 'required|numeric|min:0',
            'status'       => 'required|in:Available,Booked,Occupied,Maintenance',
            'description'  => 'nullable|string',
            'image'        => 'nullable|image|max:2048',
        ]);

        // Jika room_name kosong, isi otomatis
        if (empty($validated['room_name'])) {
            $validated['room_name'] = 'Room ' . $validated['room_number'];
        }

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('rooms', 'public');
        }

        Room::create($validated);

        $this->syncTotalRooms($validated['room_type_id']);

        return redirect()
            ->route('admin.room.index')
            ->with('success', 'Room berhasil ditambahkan.');
    }

    public function show(Room $room)
    {
        return response()->json(
            $room->load('roomType')
        );
    }

    public function edit(Room $room)
    {
        return response()->json([
            'room' => $room->load('roomType'),
            'roomTypes' => RoomType::all(),
        ]);
    }

    public function update(Request $request, Room $room)
    {
        $oldRoomTypeId = $room->room_type_id;

        $validated = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'room_number'  => 'required|string|max:50|unique:rooms,room_number,' . $room->id,
            'room_name'    => 'nullable|string|max:255',
            'floor'        => 'required|integer|min:1',
            'capacity'     => 'required|integer|min:1',
            'price'        => 'required|numeric|min:0',
            'status'       => 'required|in:Available,Booked,Occupied,Maintenance',
            'description'  => 'nullable|string',
            'image'        => 'nullable|image|max:2048',
        ]);

        // Jika room_name kosong, isi otomatis
        if (empty($validated['room_name'])) {
            $validated['room_name'] = 'Room ' . $validated['room_number'];
        }

        if ($request->hasFile('image')) {

            if ($room->image && Storage::disk('public')->exists($room->image)) {
                Storage::disk('public')->delete($room->image);
            }

            $validated['image'] = $request->file('image')->store('rooms', 'public');
        }

        $room->update($validated);

        $this->syncTotalRooms($validated['room_type_id']);

        if ($oldRoomTypeId != $validated['room_type_id']) {
            $this->syncTotalRooms($oldRoomTypeId);
        }

        return redirect()
            ->route('admin.room.index')
            ->with('success', 'Room berhasil diupdate.');
    }

    public function destroy(Room $room)
    {
        $roomTypeId = $room->room_type_id;

        if ($room->image && Storage::disk('public')->exists($room->image)) {
            Storage::disk('public')->delete($room->image);
        }

        $room->delete();

        $this->syncTotalRooms($roomTypeId);

        return redirect()
            ->route('admin.room.index')
            ->with('success', 'Room berhasil dihapus.');
    }

    /**
     * Sinkronisasi total_rooms pada Room Type
     */
    private function syncTotalRooms(int $roomTypeId): void
    {
        RoomType::where('id', $roomTypeId)->update([
            'total_rooms' => Room::where('room_type_id', $roomTypeId)->count(),
        ]);
    }
}
