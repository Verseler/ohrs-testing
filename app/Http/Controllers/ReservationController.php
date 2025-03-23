<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\GuestBeds;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReservationController extends Controller
{
    //Reservation Management
    public function list(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['pending', 'checked_in', 'checked_out', 'canceled'])],
            'balance' => ['nullable', Rule::in(['paid', 'has_balance'])],
            'sort_by' => ['nullable', Rule::in(['reservation_code', 'first_name', 'check_in_date', 'check_out_date', 'total_billing', 'remaining_balance', 'status'])],
            'sort_order' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);

        $query = Reservation::with(relations: ['guests', 'guestOffice', 'hostelOffice'])
            //Make sure that the reservations is ony accessible by authorized admin
            ->where('hostel_office_id', Auth::user()->office_id)
            //Make sure to not include the pending reservations because it has a dedicated page for that. (Waiting List Page)
            ->whereNotIn('status', ['pending']);


        // Search Filter
        if ($request->filled('search')) {
            $query->where(function ($query) use ($request) {
                $query->where('first_name', 'ILIKE', "%{$request->search}%")
                    ->orWhere('last_name', 'ILIKE', "%{$request->search}%")
                    ->orWhere('reservation_code', 'ILIKE', "%{$request->search}%");
            });
        }

        // Status Filter
        if ($request->filled('status') && in_array($request->status, ['confirmed', 'checked_in', 'checked_out', 'canceled'])) {
            $query->where('status', $request->status);
        }

        // Balance Filter
        if ($request->filled('balance')) {
            match ($request->balance) {
                'paid' => $query->where('remaining_balance', 0),
                'has_balance' => $query->where('remaining_balance', '>', 0),
            };
        }

        // Sorting updates
        if ($request->filled('sort_by')) {
            $sortBy = in_array($request->sort_by, [
                'reservation_code',
                'first_name',
                'check_in_date',
                'check_out_date',
                'total_billings',
                'remaining_balance',
                'status'
            ]) ? $request->sort_by : 'reservation_code';

            $sortOrder = $request->sort_order === 'desc' ? 'desc' : 'asc';
            $query->orderBy($sortBy, $sortOrder);
        }

        $reservations = $query->paginate(10);

        return Inertia::render("Admin/Reservation/ReservationManagement", [
            'reservations' => $reservations,
            'filters' => $request->only(['search', 'status', 'balance', 'sort_by', 'sort_order'])
        ]);
    }

    public function show(int $id)
    {
        $reservation = Reservation::with([
            'guests',
            'guestOffice.region',
            'hostelOffice.region',
            'reservedBeds.room'
        ])->where('hostel_office_id', Auth::user()->office_id)->findOrFail($id);

        return Inertia::render("Admin/Reservation/ReservationDetails/ReservationDetails", [
            'reservation' => $reservation,
        ]);
    }

    public function extendForm(int $id)
    {
        $reservation = Reservation::where('hostel_office_id', Auth::user()->office_id)->findOrFail($id);
        return Inertia::render('Admin/Reservation/ReservationExtendForm', [
            'reservation' => $reservation
        ]);
    }

    public function editStatusForm(int $id)
    {
        $reservation = Reservation::where('hostel_office_id', Auth::user()->office_id)
            ->findOrFail($id);

        return Inertia::render('Admin/Reservation/EditReservationStatus', [
            'reservation' => $reservation,
        ]);
    }

    public function editStatus(Request $request)
    {
        $validated = $request->validate([
            'reservation_id' => ['required', 'exists:reservations,id'],
            'status' => ['required', Rule::in(['confirmed', 'checked_in', 'checked_out', 'canceled'])]
        ]);

        $reservation = Reservation::where('hostel_office_id', Auth::user()->office_id)
            ->findOrFail($validated['reservation_id']);

        $reservation->status = $validated['status'];
        $reservation->save();

        return to_route('reservation.show', ['id' => $reservation->id])
            ->with('success', 'Reservation status updated successfully.');
    }

    public function editBedAssignmentForm(int $id)
    {
        return Inertia::render('Admin/Reservation/EditBedAssignment');
    }


    //Check Reservation Status for Guests
    public function checkStatusForm()
    {
        return Inertia::render('Guest/CheckReservationStatus/CheckReservationStatus', [
            'canLogin' => Route::has('login'),
        ]);
    }

    public function checkStatus(string $code)
    {
        $reservation = Reservation::with('guests')->where('reservation_code', $code)->first();

        if (!$reservation) {
            return redirect()->back()->with('error', 'Reservation doesn\'t exist.');
        }

        return Inertia::render('Guest/CheckReservationStatus/ReservationStatusResult', [
            'reservation' => $reservation,
            'canLogin' => Route::has('login'),
        ]);
    }

    //Waiting list page
    public function waitingList(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'sort_by' => ['nullable', Rule::in(['reservation_code', 'created_at', 'first_name', 'check_in_date', 'check_out_date', 'guest_office_id'])],
            'sort_order' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);

        $query = Reservation::with(relations: ['guests', 'guestOffice', 'hostelOffice'])
            //Make sure that the reservations is only accessible by authorized admin
            ->where('hostel_office_id', Auth::user()->office_id)
            //Make sure the reservation status is pending
            ->whereIn('status', ['pending']);


        // Search Filter
        if ($request->filled('search')) {
            $query->where(function ($query) use ($request) {
                $query->where('first_name', 'ILIKE', "%{$request->search}%")
                    ->orWhere('last_name', 'ILIKE', "%{$request->search}%")
                    ->orWhere('reservation_code', 'ILIKE', "%{$request->search}%");
            });
        }

        // Sorting updates
        if ($request->filled('sort_by')) {
            $sortBy = in_array($request->sort_by, [
                'reservation_code',
                'created_at',
                'first_name',
                'check_in_date',
                'check_out_date',
                'guest_office_id'
            ]) ? $request->sort_by : 'reservation_code';

            $sortOrder = $request->sort_order === 'desc' ? 'desc' : 'asc';
            $query->orderBy($sortBy, $sortOrder);
        }

        $reservations = $query->paginate(10);

        return Inertia::render('Admin/WaitingList/WaitingList', [
            'reservations' => $reservations,
            'filters' => $request->only(['search', 'sort_by', 'sort_order'])
        ]);
    }

    //For assigning beds on each guests
    public function assignBedsForm(int $id)
    {

        $reservation = Reservation::with([
            'guests' => function ($query) {
                $query->orderBy('gender');
            },
            'guestOffice.region',
            'hostelOffice.region',
            'reservedBeds'
        ])->where([
                    ['hostel_office_id', Auth::user()->office_id],
                    ['status', 'pending']
                ])->findOrFail($id);


        //Available Beds
        $checkInDate = $reservation->check_in_date;
        $checkOutDate = $reservation->check_out_date;

        $guestBeds = new GuestBeds();
        $reservedBedIds = $guestBeds->reservedBeds($checkInDate, $checkOutDate)
            ->pluck('bed_id')->toArray();

        $availableBeds = Bed::whereNotIn('id', $reservedBedIds)
            ->with([
                'room' => function ($query) use ($reservation) {
                    $query->where('office_id', $reservation->hostel_office_id);
                }
            ])
            ->orderBy('room_id', 'asc')->get();

        return Inertia::render('Admin/WaitingList/GuestBedAssignment', [
            'reservation' => $reservation,
            'availableBeds' => $availableBeds
        ]);
    }

    public function assignBeds(Request $request)
    {
        $validated = $request->validate([
            'reservation_id' => ['required', 'exists:reservations,id'],
            'guests' => ['required', 'array'],
            'guests.*.id' => ['required'],
            'guests.*.name' => ['required', 'string'],
            'guests.*.bed_id' => ['required']
        ], [
            'guests.*.bed_id' => 'Required to assign bed'
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $reservation = Reservation::findOrFail($validated['reservation_id']);

                //TODO: improve this later. don't get the reservedBedsIds, go directly get the availableBeds
                // Get all reserved beds during the reservation period
                $guestBed = new GuestBeds();
                $reservedBedIds = $guestBed->reservedBeds(
                    $reservation->check_in_date,
                    $reservation->check_out_date
                )->pluck('bed_id')->toArray();

                // Get available beds
                $availableBeds = Bed::whereNotIn('id', $reservedBedIds)
                    ->whereHas('room', function ($query) use ($reservation) {
                        $query->where('office_id', $reservation->hostel_office_id);
                    })
                    ->get();


                $availableBedIds = $availableBeds->pluck('id')->toArray();
                $usedBedIds = [];
                $totalPrice = 0;


                foreach ($validated['guests'] as $guest) {
                    $bedId = $guest['bed_id'];

                    // Check for duplicate assignments in current request
                    if (in_array($bedId, $usedBedIds)) {
                        throw ValidationException::withMessages([
                            'guests.*.bed_id' => "Bed {$bedId} is assigned to multiple guests. Please click reset."
                        ]);
                    }

                    // Check bed availability
                    if (!in_array($bedId, $availableBedIds)) {
                        throw ValidationException::withMessages([
                            'guests.*.bed_id' => "Bed {$bedId} is not available. Please click reset."
                        ]);
                    }

                    $usedBedIds[] = $bedId;

                    // Get the bed price
                    $bed = Bed::findOrFail($bedId);
                    $totalPrice += $bed->price;

                    // Create GuestBeds record with dates
                    GuestBeds::create([
                        'guest_id' => $guest['id'],
                        'bed_id' => $bedId
                    ]);
                }

                // Update reservation total_billings and remaining_balance
                $reservation->total_billings += $totalPrice;
                $reservation->remaining_balance += $totalPrice;

                //change the status to confirmed
                $reservation->status = 'confirmed';
                $reservation->save();

                //TODO: take into consideration the eligible gender schedule
            });
        } catch (\Exception $e) {
            return redirect()->back()->with(['error' => $e->getMessage()]);
        }

        return to_route('reservation.waitingList')->with('success', 'Beds assigned successfully.');
    }
}
