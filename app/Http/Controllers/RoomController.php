<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\ReservationAssignments;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class RoomController extends Controller
{
    // --- Room Management initial page that display all rooms ---
    public function list(Request $request)
    {
        $request->validate([
            'selected_date'=> ['nullable', 'date'],
            'eligible_gender' => ['nullable', Rule::in(['any', 'male', 'female'])],
            'status' => ['nullable', Rule::in(['available', 'fully_occupied'])],
            'sort_by' => ['nullable', Rule::in(['name', 'status', 'eligible_gender', 'beds_count', 'available_beds', 'bed_price'])],
            'sort_order' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);

        $date = $request->selected_date ? Carbon::parse($request->selected_date) : Carbon::today();

        // Get all beds reserved for the selected date
        $reservedBedIds = ReservationAssignments::whereHas('reservation', function ($query) use ($date) {
            $query->where('check_in_date', '<=', $date)
                ->where('check_out_date', '>=', $date)
                ->whereNotIn('status', ['canceled', 'checked_out']);
        })->pluck('bed_id');

        $query = Room::withCount([
            'beds',
            'beds as available_beds' => function ($query) use ($reservedBedIds) {
                $query->whereNotIn('id', $reservedBedIds);
            }
        ])
        //Bed prices
        ->with(['beds' => function($query) {
            $query->select('id', 'room_id', 'price');
        }])
        ->where('office_id', Auth::user()->office_id);

        // Search Filter
        if ($request->filled('search')) {
            $query->where('name', 'ILIKE', "%{$request->search}%");
        }

        // Gender Filter
        if ($request->filled('eligible_gender') && in_array($request->eligible_gender, ['any', 'male', 'female'])) {
            $query->where('eligible_gender', $request->eligible_gender);
        }

        // Sorting updates
        if ($request->filled('sort_by')) {
            $sortBy = in_array($request->sort_by, [
                'name', 'eligible_gender', 'beds_count',
                'available_beds', 'bed_price'
            ]) ? $request->sort_by : 'name';

            $sortOrder = $request->sort_order === 'desc' ? 'desc' : 'asc';

            if ($sortBy === 'bed_price') {
                $query->withMax('beds', 'price')->orderBy('beds_max_price', $sortOrder);
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }
        }

        $rooms = $query->paginate(10)->withQueryString();

        return Inertia::render("RoomManagement/RoomManagement", [
            'rooms' => $rooms,
            'filters' => $request->only(['selected_date', 'search', 'eligible_gender', 'status', 'sort_by', 'sort_order'])
        ]);
    }


    // --- For adding new room and beds ---
    public function create(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:16'],
            'eligible_gender' => ['required', Rule::in(['any', 'male', 'female'])],
            'bed_price_rate' => ['required', 'min:1', 'numeric'],
            'number_of_beds' => ['required', 'min:1', 'numeric']
        ]);


        $roomNameAlreadyExisted = Room::where('name', $validated['name'])->first();
        if ($roomNameAlreadyExisted) {
            return redirect()->back()->with('error', 'Room name already existed.');
        }

        $room = Room::create([
            'name' => $validated['name'],
            'eligible_gender' => $validated['eligible_gender'],
            'office_id' => Auth::user()->office_id,
        ]);

        for($i = 1; $i <= $validated['number_of_beds']; $i++) {
            $room->beds()->create(['name' => "Bed #$i", 'price' => $validated['bed_price_rate']]);
        }

        return to_route('room.list')->with('success', 'Successfully added new room.');
    }

    // --- For deleting room ---
    public function delete($id)
    {
        $room = Room::findOrFail($id);

        // Check if any beds in this room have future reservations
        $hasFutureReservations = $room->beds()->whereHas('reservationAssignments.reservation', function($query) {
            $query->where('check_out_date', '>=', Carbon::today());
        })->exists();

        if ($hasFutureReservations) {
            return redirect()->back()->with('error', "Cannot delete room - it has active or future reservations.");
        }

        $room->delete();

        return redirect()->back()->with('success', "Successfully deleted $room->name room.");
    }


    // --- Form page for editing room ---
    public function editForm($id)
    {
        $room = Room::with(['beds'])->findOrFail($id);
        return Inertia::render('RoomManagement/EditRoom', ['room' => $room]);
    }


    // --- For updating room ---
    public function edit(Request $request, Room $room)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:16'],
            'eligible_gender' => ['required', Rule::in(['any', 'male', 'female'])],
            'beds' => ['required', 'array', 'min:1'],
            'beds.*.name' => ['required', 'string', 'max:8'],
            'beds.*.price' => ['required', 'min:1', 'numeric'],
        ], [
            'beds.*.name.required' => 'Bed name is required.',
            'beds.*.name.string' => 'Bed name must be a string.',
            'beds.*.name.max' => 'Bed name must be at most 8 characters.',
            'beds.*.price.required' => 'Price is required.',
            'beds.*.price.min' => 'Price must be at least 1 peso.',
        ]);


        $roomNameAlreadyExisted = Room::where([
            ['id', '!=', $request->id],
            [
                'name',
                $validated['name'],
            ]
        ])->first();
        if ($roomNameAlreadyExisted) {
            return redirect()->back()->with('error', 'Room name already existed.');
        }

        // Update the room
        $room->update([
            'name' => $validated['name'],
            'eligible_gender' => $validated['eligible_gender'],
        ]);

        $existingBedIds = $room->beds->pluck('id')->toArray();
        $submittedBedIds = [];

        foreach ($validated['beds'] as $bedData) {
            // If bed already existed perform update
            if (isset($bedData['id']) && in_array($bedData['id'], $existingBedIds)) {
                $room->beds()
                    ->where('id', $bedData['id'])
                    ->update(['name' => $bedData['name'], 'price' => $bedData['price']]);
                $submittedBedIds[] = $bedData['id'];
            }
            // else create
            else {
                $newBed = $room->beds()->create(['name' => $bedData['name'], 'price' => $bedData['price']]);
                $submittedBedIds[] = $newBed->id;
            }
        }

        // Delete beds not present in the submitted data
        $room->beds()
            ->whereNotIn('id', $submittedBedIds)
            ->delete();


        return to_route('room.list')->with('success', 'Room updated successfully.');
    }




    public function getAvailableRooms(Request $request)
    {
    $validated = $request->validate([
        'selected_date' => ['required','date'],
    ]);

    $date = Carbon::parse($validated['selected_date']);

    // Get all beds with active reservations for the target date
    $reservedBedIds = ReservationAssignments::whereHas('reservation', function ($query) use ($date) {
        $query->where('check_in_date', '<=', $date)
              ->where('check_out_date', '>=', $date)
              ->whereNotIn('status', ['canceled', 'checked_out']);
    })->pluck('bed_id');

    // Get all rooms with their beds
    $rooms = Room::with('beds')->get()->map(function (Room $room) use ($reservedBedIds) {
        //get all available beds of the current room
        $beds = $room->beds
            ->whereNotIn('id', $reservedBedIds)
            ->map(function (Bed $bed) use ($room) {
                return $bed;
            })
            ->toArray();


        //structure the data so that we get the room with its available beds
        return [
            'id' => $room->id,
            'name' => $room->name,
            'eligible_gender' => $room->eligible_gender,
            'beds' => array_values($beds)
        ];
    });

    return response()->json($rooms);
    }
}
