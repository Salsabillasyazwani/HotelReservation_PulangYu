<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\RoomType;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    public function index()
    {
        $facilities = Facility::with('roomTypes')->latest()->get();

        $roomTypes = RoomType::where('status', 'Active')->get();

        $totalFacilities    = $facilities->count();
        $activeFacilities   = $facilities->where('status', 'Active')->count();
        $inactiveFacilities = $facilities->where('status', 'Inactive')->count();

        $mostUsed = $facilities->sortByDesc(fn ($f) => $f->roomTypes->count())->first();
        $mostUsedFacility      = $mostUsed?->name;
        $mostUsedFacilityCount = $mostUsed?->roomTypes->count() ?? 0;

        return view('admin.facilities', compact(
            'facilities',
            'roomTypes',
            'totalFacilities',
            'activeFacilities',
            'inactiveFacilities',
            'mostUsedFacility',
            'mostUsedFacilityCount'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|max:100',
            'description'  => 'nullable',
            'status'       => 'required|in:Active,Inactive',
            'room_types'   => 'nullable|array',
            'room_types.*' => 'exists:room_types,id',
        ]);

        $facility = Facility::create([
            'name'        => $request->name,
            'description' => $request->description,
            'status'      => $request->status,
        ]);

        $facility->roomTypes()->sync($request->room_types ?? []);

        return back()->with('success', 'Facility created.');
    }


    public function quickStore(Request $request)
    {
        $request->validate([
            'name' => 'required|max:100|unique:facilities,name',
        ]);

        $facility = Facility::create([
            'name'   => $request->name,
            'status' => 'Active',
        ]);

        return response()->json($facility);
    }

    public function update(Request $request, Facility $facility)
    {
        $request->validate([
            'name'         => 'required|max:100',
            'description'  => 'nullable',
            'status'       => 'required|in:Active,Inactive',
            'room_types'   => 'nullable|array',
            'room_types.*' => 'exists:room_types,id',
        ]);

        $facility->update([
            'name'        => $request->name,
            'description' => $request->description,
            'status'      => $request->status,
        ]);

        $facility->roomTypes()->sync($request->room_types ?? []);

        return back()->with('success', 'Facility updated.');
    }

    public function destroy(Facility $facility)
    {
        $facility->roomTypes()->detach();
        $facility->delete();

        return back()->with('success', 'Facility deleted.');
    }
}
