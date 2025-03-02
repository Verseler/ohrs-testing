<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class RoomController extends Controller
{
    public function list(Request $request)
    {
        $query = Room::withCount([
            'beds',
            'beds as available_beds' => fn($query) => $query->where('status', 'available')
        ]);

        // Search Filter
        if ($request->filled('search')) {
            $query->where('name', 'ILIKE', "%{$request->search}%");
        }

        // Gender Filter
        if ($request->filled('eligible_gender') && in_array($request->eligible_gender, ['any', 'male', 'female'])) {
            $query->where('eligible_gender', $request->eligible_gender);
        }

        // Status Filter
        if ($request->filled('status') && in_array($request->status, ['available', 'fully_occupied', 'maintenance'])) {
            $query->where('status', $request->status);
        }

        // Sorting
        if ($request->filled('sort_by')) {
            $sortBy = in_array($request->sort_by, ['name', 'status', 'eligible_gender', 'beds_count', 'available_bed']) ? $request->sort_by : 'name';
            $sortOrder = $request->sort_order === 'desc' ? 'desc' : 'asc';
            $query->orderBy($sortBy, $sortOrder);
        }

        $rooms = $query->paginate(10)->withQueryString();

        return Inertia::render("RoomManagement/RoomManagement", [
            'rooms' => $rooms,
            'filters' => $request->only(['search', 'eligible_gender', 'status', 'sort_by', 'sort_order'])
        ]);
    }


    public function create(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:20'],
            'eligible_gender' => ['required', Rule::in(['any', 'male', 'female'])],
            'beds' => ['required', 'array', 'min:1'],
            'beds.*.name' => ['required', 'string', 'max:8'],
            'beds.*.price' => ['required', 'numeric'],
        ]);


        $roomNameAlreadyExisted = Room::where('name', $validated['name'])->first();
        if ($roomNameAlreadyExisted) {
            return redirect()->back()->with('error', 'Room name already existed.');
        }

        $room = Room::create([
            'name' => $validated['name'],
            'eligible_gender' => $validated['eligible_gender'],
            'status' => 'available'
        ]);

        foreach ($validated['beds'] as $bed) {
            $room->beds()->create([
                'name' => $bed['name'],
                'price' => $bed['price'],
                'status' => 'available',
            ]);
        }

        return to_route('room.list')->with('success', 'Successfully added new room.');
    }









    public function show($id)
    {

        $room = Room::with([
            'beds'
        ])->withCount([
                    'beds',
                    'beds as occupied_beds' => fn($query) => $query->where('status', 'occupied'),
                    'beds as under_maintenance_beds' => fn($query) => $query->where('status', 'maintenance'),
                    'beds as available_beds' => fn($query) => $query->where('status', 'available')
                ])->findOrFail($id);

        return Inertia::render("RoomManagement/RoomDetails", ['room' => $room]);
    }

    public function delete($id)
    {
        $room = Room::findOrFail($id);
        $room->delete();

        return to_route('room.list')->with('success', "Successfully deleted $room->name room.");
    }


    public function editForm($id)
    {
        $room = Room::with('beds')->findOrFail($id);
        return Inertia::render('RoomManagement/EditRoom', ['room' => $room]);
    }


    public function edit(Request $request, Room $room)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:20'],
            'eligible_gender' => ['required', Rule::in(['any', 'male', 'female'])],
            'status' => ['required', Rule::in(['available', 'fully_occupied', 'maintenance'])],
            'beds' => ['required', 'array', 'min:1'],
            'beds.*.name' => ['required', 'string', 'max:8'],
            'beds.*.price' => ['required', 'numeric', 'min:0'],
            'beds.*.status' => ['required', Rule::in(['available', 'reserved', 'occupied', 'maintenance'])],
        ]);

        // Update the room
        $room->update([
            'name' => $validated['name'],
            'eligible_gender' => $validated['eligible_gender'],
            'status' => $validated['status'],
        ]);

        $existingBedIds = $room->beds->pluck('id')->toArray();
        $submittedBedIds = [];

        foreach ($validated['beds'] as $bedData) {
            // Check if bed exists in the room
            if (isset($bedData['id']) && in_array($bedData['id'], $existingBedIds)) {
                // Update existing bed
                $room->beds()
                    ->where('id', $bedData['id'])
                    ->update([
                        'name' => $bedData['name'],
                        'price' => $bedData['price'],
                        'status' => $bedData['status'],
                    ]);
                $submittedBedIds[] = $bedData['id'];
            } else {
                // Create new bed (ignore client-side UUID)
                $newBed = $room->beds()->create([
                    'name' => $bedData['name'],
                    'price' => $bedData['price'],
                    'status' => $bedData['status'],
                ]);
                $submittedBedIds[] = $newBed->id;
            }
        }

        // Delete beds not present in the submitted data
        $room->beds()
            ->whereNotIn('id', $submittedBedIds)
            ->delete();


        return to_route('room.list')->with('success', 'Room updated successfully.');
    }
}

