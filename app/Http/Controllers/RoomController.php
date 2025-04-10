<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class RoomController extends Controller
{
    // --- Room Management initial page that display all rooms ---
    public function list(Request $request)
    {
        $request->validate([
            'check_in_date' => ['nullable', 'date'],
            'check_out_date' => ['nullable', 'date'],
            'eligible_gender' => ['nullable', Rule::in(['any', 'male', 'female'])],
            'status' => ['nullable', Rule::in(['available', 'fully_occupied'])],
            'sort_by' => ['nullable', Rule::in(['name', 'status', 'eligible_gender', 'beds_count', 'available_beds', 'bed_price'])],
            'sort_order' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);

        $checkInDate = $request->check_in_date ? Carbon::parse($request->check_in_date) : Carbon::today();
        $checkOutDate = $request->check_out_date ? Carbon::parse($request->check_out_date) : Carbon::now()->addDays(1);


        $bed = new Bed();
        $availableBedsIds = $bed->availableBeds(
            $checkInDate,
            $checkOutDate,
            Auth::user()->office_id
        )->pluck('id')->toArray();

        $query = Room::withCount([
            'beds',
            'beds as available_beds' => function ($query) use ($availableBedsIds) {
                $query->whereIn('id', $availableBedsIds);
            }
        ])
            //Bed prices
            ->with([
                'beds' => function ($query) {
                    $query->select('id', 'room_id', 'price');
                }
            ])
            ->where('office_id', Auth::user()->office_id);


        // Gender Filter
        if ($request->filled('eligible_gender') && in_array($request->eligible_gender, ['any', 'male', 'female'])) {
            $query->where('eligible_gender', $request->eligible_gender);
        }

        // Sorting updates
        if ($request->filled('sort_by')) {
            $sortBy = in_array($request->sort_by, [
                'name',
                'eligible_gender',
                'beds_count',
                'available_beds',
                'bed_price'
            ]) ? $request->sort_by : 'name';

            $sortOrder = $request->sort_order === 'desc' ? 'desc' : 'asc';

            if ($sortBy === 'bed_price') {
                $query->withMax('beds', 'price')->orderBy('beds_max_price', $sortOrder);
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }
        }

        $rooms = $query->paginate(10)->withQueryString();

        return Inertia::render("Admin/Room/RoomManagement", [
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


        $roomNameAlreadyExisted = Room::where('name', $validated['name'])->where('office_id', Auth::user()->office_id)->first();
        if ($roomNameAlreadyExisted) {
            return redirect()->back()->with('error', 'Room name already existed.');
        }

        $room = Room::create([
            'name' => $validated['name'],
            'eligible_gender' => $validated['eligible_gender'],
            'office_id' => Auth::user()->office_id,
        ]);

        for ($i = 1; $i <= $validated['number_of_beds']; $i++) {
            $room->beds()->create(['name' => "Bed #$i", 'price' => $validated['bed_price_rate']]);
        }

        return to_route('room.list')->with('success', 'Successfully added new room.');
    }

    // --- For deleting room ---
    public function delete($id)
    {
        $room = Room::findOrFail($id);

        // Check if any beds in this room have future reservations
        $hasFutureReservations = $room->beds()->whereHas('stayDetails.guest.reservation', function ($query) {
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
        return Inertia::render('Admin/Room/EditRoom', ['room' => $room]);
    }


    // --- For updating room ---
    public function edit(Request $request, Room $room)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:16'],
            'eligible_gender' => ['required', Rule::in(['any', 'male', 'female'])],
            'beds' => ['required', 'array', 'min:1'],
            'beds.*.id' => ['required'],
            'beds.*.name' => ['required', 'string', 'max:8'],
            'beds.*.price' => ['required', 'min:1', 'numeric'],
        ], [
            'beds.*.name.required' => 'Bed name is required.',
            'beds.*.name.string' => 'Bed name must be a string.',
            'beds.*.name.max' => 'Bed name must be at most 8 characters.',
            'beds.*.price.required' => 'Price is required.',
            'beds.*.price.min' => 'Price must be at least 1 peso.',
        ]);

        try {
            DB::transaction(function () use ($validated, $room) {
                // Check if room name already exists (excluding this room)
                $roomNameAlreadyExisted = Room::where([
                    ['id', '!=', $room->id],
                    ['name', $validated['name']],
                ])->first();
                if ($roomNameAlreadyExisted) {
                    throw new \Exception('Room name already exists.');
                }

                // Update Room Info (Always allowed)
                $room->update([
                    'name' => $validated['name'],
                    'eligible_gender' => $validated['eligible_gender'],
                ]);

                // Get existing bed IDs for comparison
                $existingBedIds = $room->beds->pluck('id')->toArray();
                $submittedBedIds = [];

                // First check all beds for reservations before making any changes
                foreach ($validated['beds'] as $bedData) {
                    if (!empty($bedData['id']) && in_array($bedData['id'], $existingBedIds)) {
                        $bed = $room->beds()->find($bedData['id']);

                        // Check if bed has current or future reservations
                        $hasReservations = $bed->StayDetails()
                            ->whereHas('guest.reservation', function ($query) {
                                $query->where('check_out_date', '>=', Carbon::today());
                            })
                            ->exists();

                        if ($hasReservations) {
                            // Check if price is being changed
                            if ($bed->price != $bedData['price']) {
                                throw new \Exception("Cannot update price for bed '{$bed->name}' as it has current or future reservations.");
                            }
                        }
                    }
                }

                // Now process updates since we've verified no restricted changes
                foreach ($validated['beds'] as $bedData) {
                    // If bed already exists, update it
                    if (!empty($bedData['id']) && in_array($bedData['id'], $existingBedIds)) {
                        $room->beds()
                            ->where('id', $bedData['id'])
                            ->update([
                                'name' => $bedData['name'],
                                'price' => $bedData['price']
                            ]);
                        $submittedBedIds[] = $bedData['id'];
                    }
                    // Otherwise, create a new bed
                    else {
                        $newBed = $room->beds()->create([
                            'name' => $bedData['name'],
                            'price' => $bedData['price']
                        ]);
                        $submittedBedIds[] = $newBed->id;
                    }
                }

                // Prevent Deleting Beds That Have Reservations
                $bedsToDelete = array_diff($existingBedIds, $submittedBedIds);

                // Check if any of the beds to delete have reservations
                $reservedBeds = $room->beds()
                    ->whereIn('id', $bedsToDelete)
                    ->whereHas('stayDetails.guest.reservation', function ($query) {
                        $query->where('check_out_date', '>=', Carbon::today());
                    })
                    ->pluck('id')
                    ->toArray();

                // If any reserved beds are in the delete list, throw an error
                if (!empty($reservedBeds)) {
                    throw new \Exception("Cannot delete beds that have current or future reservations");
                }

                // Delete Beds That Are Not Reserved
                if (!empty($bedsToDelete)) {
                    $room->beds()->whereIn('id', $bedsToDelete)->delete();
                }
            });
        } catch (\Exception $e) {
            return redirect()->back()->with("error", $e->getMessage());
        }

        return to_route('room.list')->with('success', 'Room updated successfully.');
    }



    public function getAvailableRooms(Request $request)
    {
        $validated = $request->validate([
            'check_in_date' => ['required', 'date', 'after_or_equal:today', 'before_or_equal:check_out_date'],
            'check_out_date' => ['required', 'date', 'after_or_equal:today', 'after_or_equal:check_in_date'],
            'hostel_id' => ['required', 'exists:offices,id'],
        ]);

        $bed = new Bed();
        $availableBeds = $bed->availableBeds(
            $validated['check_in_date'],
            $validated['check_out_date'],
            $validated['hostel_id']
        );

        // Group beds by room and format results
        $availableBedsInRooms = $availableBeds->groupBy('room_id')->map(function ($beds) use ($validated) {
            $room = $beds->first()->room;

            // Determine eligible gender considering if there is an scheduled eligible gender
            $eligibleGender = $room->eligible_gender;
            $applicableSchedule = $room->eligibleGenderSchedules
                ->where('start_date', '<=', $validated['check_in_date'])
                ->where('end_date', '>=', $validated['check_in_date'])
                ->first();

            if ($applicableSchedule) {
                $eligibleGender = $applicableSchedule->eligible_gender;
            }

            return [
                'id' => $room->id,
                'name' => $room->name,
                'eligible_gender' => $eligibleGender,
                'beds_count' => $beds->count(),
            ];
        })->values();

        return redirect()->back()->with([
            'response_data' => $availableBedsInRooms
        ]);
    }
}
