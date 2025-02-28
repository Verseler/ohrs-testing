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
            'beds as available_bed' => fn($query) => $query->where('status', 'available')
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



    //     public function upsertForm($type, ?string $id = null)
//     {
//         $room = Room::where('id', $id)->with('beds')->first();

    //         return Inertia::render('RoomManagement/UpsertRoom', ['room' => $room, 'type' => $type]);
//     }

    //     public function upsert(Request $request)
//     {
//         $validatedFields = $request->validate([
//             'name' => ['required', 'string', 'min:2', 'max:20'],
//             'eligible_gender' => ['required', Rule::in(['any', 'male', 'female'])],
//             'beds' => ['required', 'array'],
//             'beds.*.code' => ['required', 'string', 'max:8'],
//             'beds.*.price' => ['required', 'numeric'],
//         ]);

    //         // Remove 'beds' from room data (because beds should be inserted separately)
//         $beds = $validatedFields['beds'];
//         unset($validatedFields['beds']);

    //         $validatedFields['status'] = 'available';

    //         //If type is create and the room name provided return an error
//         if ($request->type === 'create') {
//             $RoomNameAlreadyExisted = Room::where('name', $validatedFields['name'])->first();

    //             if ($RoomNameAlreadyExisted) {
//                 return back()->with('error', 'Room name already existed');
//             }
//         }

    //         $room = Room::updateOrCreate(['id' => $request->id ?? null], $validatedFields);

    //         if ($room) {
//             foreach ($beds as $bed) {
//                 Bed::updateOrCreate(
//                     ['code' => $bed['code'], 'room_id' => $room->id],
//                     [
//                         'price' => $bed['price'],
//                         'status' => 'available',
//                         'room_id' => $room->id
//                     ]
//                 );
//             }
//         }

    //         return to_route('room.list')->with('success', "Successfully");
//     }


    //     public function create(Request $request)
//     {
//         $validatedFields = $request->validate([
//             'name' => ['required', 'string', 'min:2', 'max:20'],
//             'eligible_gender' => ['required', Rule::in(['any', 'male', 'female'])],
//             'beds' => ['required', 'array'],
//             'beds.*.code' => ['required', 'string', 'max:8'],
//             'beds.*.price' => ['required', 'numeric'],
//         ]);


    //         $roomAlreadyExisted = Room::where('name', $validatedFields['name'])->first();
//         if ($roomAlreadyExisted) {
//             return redirect()->back()->with('error', 'Room name already existed.');
//         }

    //         $room = Room::create([
//             'name' => $validatedFields['name'],
//             'eligible_gender' => $validatedFields['eligible_gender'],
//             'status' => 'available'
//         ]);

    //         if ($room) {
//             foreach ($validatedFields['beds'] as $bed) {
//                 Bed::create([
//                     'code' => $bed['code'],
//                     'price' => $bed['price'],
//                     'status' => 'available',
//                     'room_id' => $room->id
//                 ]);
//             }
//         }

    //         return to_route('room.list')->with('success', 'Successfully added new room.');
//     }


    //     public function delete($id)
//     {
//         $room = Room::findOrFail($id);
//         $room->delete();

    //         return to_route('room.list')->with('success', "Successfully deleted $room->name room.");
//     }


    //     public function show($id)
//     {

    //         $room = Room::with([
//             'beds'
//         ])->withCount([
//                     'beds',
//                     'beds as occupied_bed' => fn($query) => $query->where('status', 'occupied'),
//                     'beds as under_maintenance_bed' => fn($query) => $query->where('status', 'maintenance'),
//                     'beds as available_bed' => fn($query) => $query->where('status', 'available')
//                 ])->findOrFail($id);

    //         return Inertia::render("RoomManagement/RoomDetails", ['room' => $room]);
//     }
}
