<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\RoomType;
use Illuminate\Http\Request;

class RoomTypeController extends Controller
{
    public function index()
    {
        $roomTypes = RoomType::with(['rooms', 'facilityLinks'])
            ->latest()
            ->paginate(8)
            ->withQueryString();


        $facilities = Facility::where('status', 'Active')
            ->orderBy('name')
            ->get();

        $summary = [
            'total_types' => RoomType::count(),
            'total_rooms' => RoomType::sum('total_rooms'),
            'avg_price'   => RoomType::all()->avg(fn ($rt) => $rt->price),
        ];

        $chartData = $this->buildChartData();

        return view('admin.room.room-type', compact('roomTypes', 'summary', 'chartData', 'facilities'));
    }

    private function buildChartData(): array
    {
        $roomTypes = RoomType::select('name', 'total_rooms')->get();
        $palette = ['#3B82F6', '#8B5CF6', '#F59E0B', '#10B981', '#EF4444', '#06B6D4', '#EC4899'];

        return [
            'labels' => $roomTypes->pluck('name'),
            'values' => $roomTypes->pluck('total_rooms'),
            'colors' => collect($palette)->take(max($roomTypes->count(), 1))->values(),
        ];
    }
    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name'         => 'required|string|max:255',
            'bed_type'     => 'required|string|max:100',
            'room_size'    => 'required|integer|min:1',
            'status'       => 'required|in:active,inactive',
            'description'  => 'nullable|string',
            'facilities'   => 'nullable|array',
            'facilities.*' => 'integer|exists:facilities,id',
            // field lama dari Blade, diterima tapi tidak disimpan ke room_types
            'price'        => 'nullable|numeric',
            'capacity'     => 'nullable|integer',
            'image'        => 'nullable|image|max:2048',
        ]);
    }


    private function syncFacilities(RoomType $roomType, ?array $facilityIds): void
    {
        $roomType->facilityLinks()->sync($facilityIds ?? []);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $roomType = RoomType::create(
            collect($data)->only(['name', 'bed_type', 'room_size', 'status', 'description'])->toArray()
        );

        $this->syncFacilities($roomType, $data['facilities'] ?? []);

        return redirect()->route('admin.room-types.index')
            ->with('success', 'Room Type berhasil ditambahkan.');
    }

    public function update(Request $request, RoomType $roomType)
    {
        $data = $this->validatedData($request);
        $roomType->update(
            collect($data)->only(['name', 'bed_type', 'room_size', 'status', 'description'])->toArray()
        );

        $this->syncFacilities($roomType, $data['facilities'] ?? []);

        return redirect()->route('admin.room-types.index')
            ->with('success', 'Room Type berhasil diupdate.');
    }

    public function destroy(RoomType $roomType)
    {
        $roomType->delete();

        return redirect()->route('admin.room-types.index')
            ->with('success', 'Room Type berhasil dihapus.');
    }

    public function export()
    {
        $roomTypes = RoomType::with(['rooms', 'facilityLinks'])->get();

        $filename = 'room-types-' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($roomTypes) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Name', 'Bed Type', 'Room Size (m2)', 'Total Rooms', 'Status', 'Price (from)', 'Capacity (max)', 'Facilities']);

            foreach ($roomTypes as $rt) {
                fputcsv($handle, [
                    $rt->name, $rt->bed_type, $rt->room_size, $rt->total_rooms,
                    $rt->status, $rt->price, $rt->capacity, implode(', ', $rt->facilities),
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
