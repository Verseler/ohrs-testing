<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\EligibleGenderSchedule;
use App\Models\Guest;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ReservationAssignBedsController extends Controller
{
    //Page for assigning beds to guests
    public function assignBedsForm(int $id)
    {
        $reservation = Reservation::with([
            'guests.stayDetails' => function ($query) {
                $query->select('id', 'guest_id', 'check_in_date', 'check_out_date', 'status')
                    ->orderBy('check_in_date');
            },
            'hostelOffice.region',
        ])->where('hostel_office_id', Auth::user()->office_id)
            ->findOrFail($id);

        $availableBedsForGuests = [];

        // Transform guest data into the required format
        $formattedGuests = $reservation->guests->map(function ($guest) {
            return [
                'id' => $guest->id,
                'name' => "{$guest->first_name} {$guest->last_name}",
                'check_in_date' => $guest->stayDetails->check_in_date,
                'check_out_date' => $guest->stayDetails->check_out_date,
                'gender' => $guest->gender,
                'bed_id' => null,
            ];
        });

        // Replace the original guests data with our formatted version
        $reservationArray = $reservation->toArray();
        $reservationArray['guestAssignment'] = $formattedGuests->toArray();

        foreach ($reservation->guests as $guest) {
            $stayDetails = $guest->stayDetails;
            $checkInDate = $stayDetails->check_in_date;
            $checkOutDate = $stayDetails->check_out_date;
            $hostelOfficeId = $reservation->hostel_office_id;

            $bed = new Bed();
            $availableBeds = $bed->availableBeds($checkInDate, $checkOutDate, $hostelOfficeId);
            $filteredByGender = $availableBeds->filter(function ($bed) use ($guest, $checkInDate, $checkOutDate) {
                return $bed->isEligibleForGender($bed->room, $guest['gender'], $checkInDate, $checkOutDate);
            });
            $availableBedsForGuests[$guest->id] = $filteredByGender;
        }

        return Inertia::render('Admin/WaitingList/GuestBedAssignment', [
            'reservation' => $reservationArray,
            'availableBeds' => $availableBedsForGuests
        ]);
    }

    public function assignBeds(Request $request)
    {
        $validated = $request->validate([
            'reservation_id' => ['required', 'exists:reservations,id'],
            'guests' => ['required', 'array'],
            'guests.*.id' => ['required', 'exists:guests,id'],
            'guests.*.name' => ['required', 'string'],
            'guests.*.bed_id' => ['required', 'exists:beds,id'],
            'guests.*.gender' => ['required'],
            'guests.*.check_in_date' => ['required', 'date'],
            'guests.*.check_out_date' => ['required', 'date'],

        ], [
            'guests.*.bed_id.required' => 'Each guest must be assigned a bed',
            'guests.*.id.exists' => 'One or more guests do not exist in the system',
            'guests.*.bed_id.exists' => 'One or more selected beds do not exist',
            'guests.*.gender.required' => 'Gender is required',
            'guests.*.check_in_date.required' => 'Check-in date is required',
            'guests.*.check_in_date.date' => 'Check-in date must be a valid date',
            'guests.*.check_out_date.required' => 'Check-out date is required',
            'guests.*.check_out_date.date' => 'Check-out date must be a valid date',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $reservation = Reservation::findOrFail($validated['reservation_id']);

                //For each guest assign them to their assigned bed
                foreach ($validated['guests'] as $guestData) {
                    $bedId = $guestData['bed_id'];
                    $guest = Guest::findOrFail($guestData['id']);

                    // Check if the bed is available
                    $bed = Bed::with('room')->findOrFail($bedId);

                    $bedIsNotAvailable = !$bed->isAvailable($guestData['check_in_date'], $guestData['check_out_date'], $guestData['gender']);
                    if ($bedIsNotAvailable) {
                        throw ValidationException::withMessages([
                            'guests.*.bed_id' => "Bed {$bedId} is not available for the reservation period."
                        ]);
                    }

                    // If the room has "any" eligible gender, create a gender schedule for this period
                    if ($bed->room->eligible_gender === 'any') {
                        // Check if there's already a schedule for this room during this period
                        $existingSchedule = EligibleGenderSchedule::where('room_id', $bed->room->id)
                            ->where(function ($query) use ($guestData) {
                                $query->whereBetween('start_date', [$guestData['check_in_date'], $guestData['check_out_date']])
                                    ->orWhereBetween('end_date', [$guestData['check_in_date'], $guestData['check_out_date']])
                                    ->orWhere(function ($q) use ($guestData) {
                                        $q->where('start_date', '<=', $guestData['check_in_date'])
                                          ->where('end_date', '>=', $guestData['check_out_date']);
                                    });
                            })->first();

                        // Only create a new schedule if none exists
                        if (!$existingSchedule) {
                            EligibleGenderSchedule::create([
                                'room_id' => $bed->room->id,
                                'eligible_gender' => $guestData['gender'],
                                'start_date' => $guestData['check_in_date'],
                                'end_date' => $guestData['check_out_date']
                            ]);
                        }
                    }

                    //compute the billing of the guest
                    $billing = $bed->calculateBilling($guestData['check_in_date'], $guestData['check_out_date']);

                    // Update stay details of the guest
                    $guest->stayDetails()->update([
                        'bed_id' => $bedId,
                        'individual_billings' => $billing,
                        'status' => 'confirmed'
                    ]);
                }

                // Update reservation billings
                $totalBillings = $reservation->guests->sum('stayDetails.individual_billings');
                $reservation->total_billings = $totalBillings;
                $reservation->remaining_balance = $totalBillings;
                $reservation->general_status = "confirmed";
                $reservation->save();
            });

            return to_route('reservation.waitingList')->with('success', 'Beds assigned successfully.');

        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->with(['errors' => $e->getMessage()])->withInput();
        }
    }

    //Page for editing bed assignments
    public function editAssignBedForm(int $id)
    {
        $reservation = Reservation::with([
            'guests',
            'stayDetails' => function($query) {
                $query->whereIn('status', ['confirmed', 'checked_in'])
                    ->with('guest');
            },
            'stayDetails.bed.room',
            'hostelOffice.region',
        ])->where(
            'hostel_office_id',
                Auth::user()->office_id
            )
            ->findOrFail($id);

        $availableBedsForGuests = [];

        foreach ($reservation->guests as $guest) {
                $stayDetails = $guest->stayDetails;
                $checkInDate = $stayDetails->check_in_date;
                $checkOutDate = $stayDetails->check_out_date;
                $hostelOfficeId = $reservation->hostel_office_id;

                $bed = new Bed();
                $availableBeds = $bed->availableBeds($checkInDate, $checkOutDate, $hostelOfficeId);
                $availableBedsForGuests[$guest->id] = $availableBeds;
            }

        return Inertia::render('Admin/Reservation/EditBedAssignment/EditBedAssignment', [
            'reservation' => $reservation,
            'availableBeds' => $availableBedsForGuests
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
                $stayDetails = $guest->stayDetails;

                $bedIsNotAvailable = !$bed->isAvailable($stayDetails->check_in_date, $stayDetails->check_out_date, $guest->gender);

                if ($bedIsNotAvailable) {
                    throw ValidationException::withMessages([
                        'selected_bed_id' => "The selected bed is not available for the reservation period"
                    ]);
                }

                // change guest bed with the new selected bed and re-calculate billing for possible changes
                $stayDetails->bed_id = $validated['selected_bed_id'];
                if(!$stayDetails->is_exempted) {
                    $stayDetails->individual_billings = $bed->calculateBilling($stayDetails->check_in_date, $stayDetails->check_out_date);
                }

                $stayDetails->save();

                //re compute total billings and remaining balance
                $newTotalBillings = $reservation->guests->sum('stayDetails.individual_billings');
                $reservation->total_billings = $newTotalBillings;

                $totalPayed = $reservation->payments->sum('amount');
                $newRemainingBalance = max(0, $newTotalBillings - $totalPayed);
                $reservation->remaining_balance = $newRemainingBalance;
                $reservation->save();


                // If the room's eligible gender is "any", create an EligibleGenderSchedule
                if ($bed->room->eligible_gender === 'any') {
                    EligibleGenderSchedule::create([
                        "start_date" => $stayDetails->check_in_date,
                        "end_date" => $stayDetails->check_out_date,
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
