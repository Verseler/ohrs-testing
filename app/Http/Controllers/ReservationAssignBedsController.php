<?php

namespace App\Http\Controllers;

use App\Models\Bed;
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
                $totalBedPrice = 0; //$totalBedPrice is the sum of all bed prices assigned to guests


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
                    $totalBedPrice += $bed->price;

                    // Create GuestBeds record with dates
                    GuestBeds::create([
                        'guest_id' => $guest['id'],
                        'bed_id' => $bedId
                    ]);
                }

                // Update reservation daily_rate, total_billings,remaining_balance and status
                $checkInDate = Carbon::parse($reservation->check_in_date);
                $checkOutDate = Carbon::parse($reservation->check_out_date);
                $lengthOfStay = $checkInDate->diffInDays($checkOutDate, false);

                //if guest set check in and out date on the same day, length of stay is 1
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

                //TODO: take into consideration the eligible gender schedule
            });
        } catch (\Exception $e) {
            return redirect()->back()->with(['error' => $e->getMessage()]);
        }

        return to_route('reservation.waitingList')->with('success', 'Beds assigned successfully.');
    }



    public function editBedAssignmentForm(int $id)
    {
        return Inertia::render('Admin/Reservation/EditBedAssignment');
    }
}
