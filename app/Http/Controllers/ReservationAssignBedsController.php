<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\EligibleGenderSchedule;
use App\Models\Guest;
use App\Models\GuestBeds;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ReservationAssignBedsController extends Controller
{
    //For assigning beds on each guests
    public function assignBedsForm(int $id)
    {
        $reservation = Reservation::with([
            'guests' => function ($query) {
                $query->orderBy('gender');
            },
            'hostelOffice.region',
        ])->where([
                    ['hostel_office_id', Auth::user()->office_id],
                    ['status', 'pending']
                ])->findOrFail($id);

        $checkInDate = $reservation->check_in_date;
        $checkOutDate = $reservation->check_out_date;

        $bed = new Bed();
        $availableBeds = $bed->availableBeds(
            $checkInDate,
            $checkOutDate,
            $reservation->hostel_office_id
        );

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

                // Get all available beds
                $bed = new Bed();
                $availableBedIds = $bed->availableBeds(
                    $reservation->check_in_date,
                    $reservation->check_in_date,
                    $reservation->hostel_office_id
                )->pluck('id')->toArray();

                $totalBedPrice = 0;
                $usedBedIds = [];

                //For each guest assign the selected bed
                foreach ($validated['guests'] as $assignGuest) {
                    $bedId = $assignGuest['bed_id'];

                    // Check for duplicate assignments in current request. to ensures that only one guest is reserved to a bed.
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

                    // Re compute the total bed price
                    $bed = Bed::with('room')->findOrFail($bedId);
                    $totalBedPrice += $bed->price;

                    // Assign each guest to its selected bed
                    GuestBeds::create([
                        'reservation_id' => $reservation->id,
                        'guest_id' => $assignGuest['id'],
                        'bed_id' => $bedId,
                    ]);

                    $currentGuest = Guest::findOrFail($assignGuest['id']);

                    // if the room status is "any" then set eligible gender schedule on that room
                    // with the gender of the first guest who occupied that room
                    // for the given period of time (check in and out)
                    if ($bed->room->eligible_gender == 'any') {
                        EligibleGenderSchedule::create([
                            "start_date" => $reservation->check_in_date,
                            "end_date" => $reservation->check_in_date,
                            "eligible_gender" => $currentGuest->gender,
                            "room_id" => $bed->room->id
                        ]);
                    }
                }

                //* Update reservation billings
                $checkInDate = Carbon::parse($reservation->check_in_date);
                $checkOutDate = Carbon::parse($reservation->check_out_date);
                $lengthOfStay = $checkInDate->diffInDays($checkOutDate, false);

                //if reservation check in and out date is on the same day then the length of stay is counted as 1 day
                if ($lengthOfStay == 0) {
                    $lengthOfStay = 1;
                }

                $dailyRate = $totalBedPrice;
                $totalBillings = $dailyRate * $lengthOfStay;

                $reservation->daily_rate = $dailyRate;
                $reservation->total_billings = $totalBillings;
                $reservation->remaining_balance = $totalBillings;
                $reservation->status = 'confirmed';
                $reservation->save();
            });
        } catch (\Exception $e) {
            return redirect()->back()->with(['error' => $e->getMessage()]);
        }

        return to_route('reservation.waitingList')->with('success', 'Beds assigned successfully.');
    }


    public function editAssignBedForm(int $id)
    {
        $reservation = Reservation::with([
            'guests',
            'guestBeds.guest',
            'guestBeds.bed.room',
            'hostelOffice.region',
        ])->where(
                'hostel_office_id',
                Auth::user()->office_id
            )
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->findOrFail($id);

        $checkInDate = $reservation->check_in_date;
        $checkOutDate = $reservation->check_out_date;

        $bed = new Bed();
        $availableBeds = $bed->availableBeds(
            $checkInDate,
            $checkOutDate,
            $reservation->hostel_office_id
        );

        return Inertia::render('Admin/Reservation/EditBedAssignment/EditBedAssignment', [
            'reservation' => $reservation,
            'availableBeds' => $availableBeds
        ]);
    }

    public function editAssignBed(Request $request)
    {
        $validated = $request->validate([
            "reservation_id" => ['required', 'exists:reservations,id'],
            "selected_guest_id" => ['required', 'exists:guests,id'],
            "selected_bed_id" => ['required', 'exists:beds,id']
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $reservation = Reservation::findOrFail($validated['reservation_id']);
                $bed = Bed::with('room')->findOrFail($validated['selected_bed_id']);
                $guest = Guest::findOrFail($validated['selected_guest_id']);

                if (!$bed->isAvailable($reservation)) {
                    throw ValidationException::withMessages([
                        'selected_bed_id' => 'The selected bed is not available for the reservation period.'
                    ]);
                }

                // Find the current bed assignment for this guest
                $currentGuestBed = GuestBeds::where('guest_id', $validated['selected_guest_id'])
                    ->where('reservation_id', $validated['reservation_id'])
                    ->first();

                if ($currentGuestBed) {
                    // Delete the old assignment
                    $currentGuestBed->delete();
                }

                // Create new bed assignment
                GuestBeds::create([
                    'bed_id' => $validated['selected_bed_id'],
                    'guest_id' => $validated['selected_guest_id'],
                    'reservation_id' => $validated['reservation_id']
                ]);

                // If the room's eligible gender is "any", create an EligibleGenderSchedule
                if ($bed->room->eligible_gender === 'any') {
                    EligibleGenderSchedule::create([
                        "start_date" => $reservation->check_in_date,
                        "end_date" => $reservation->check_out_date,
                        "eligible_gender" => $guest->gender,
                        "room_id" => $bed->room->id
                    ]);
                }
            });
        } catch (\Exception $e) {
            return redirect()->back()->with(['error' => $e->getMessage()]);
        }

        return to_route('reservation.show', ['id' => $validated['reservation_id']])
            ->with('success', 'Bed assignment updated successfully.');
    }
}
