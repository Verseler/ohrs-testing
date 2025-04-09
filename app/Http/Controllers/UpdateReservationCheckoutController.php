<?php

namespace App\Http\Controllers;

use App\Models\EligibleGenderSchedule;
use App\Models\Guest;
use App\Models\StayDetails;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class UpdateReservationCheckoutController extends Controller
{
    public function updateCheckoutForm(int $id)
    {
        $reservation = Reservation::where('hostel_office_id', Auth::user()->office_id)
            ->with(['guests' => function($query) {
                $query->whereHas('stayDetails', function($query) {
                    $query->whereNotIn('status', ['canceled', 'checked_out']);
                });
            }, 'guests.stayDetails' => function($query) {
                $query->whereNotIn('status', ['canceled', 'checked_out']);
            }, 'guests.stayDetails.bed.room'])
            ->findOrFail($id);

        return Inertia::render('Admin/Reservation/ReservationUpdateCheckoutForm', [
            'reservation' => $reservation,
        ]);
    }

    public function updateCheckout(Request $request)
    {
        $validated = $request->validate([
            'reservation_id' => ['required', 'exists:reservations,id'],
            'guest_id' => ['required', 'exists:guests,id'],
            'new_check_out_date' => ['required', 'date',],
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $reservation = Reservation::findOrFail($validated['reservation_id']);
                $guest = Guest::with('stayDetails')->findOrFail($validated['guest_id']);
                $stayDetails = $guest->stayDetails;

                $oldCheckOutDate = Carbon::parse($stayDetails->check_out_date)->startOfDay();
                $newCheckOutDate = Carbon::parse($validated['new_check_out_date'], 'Asia/Manila')->setTimezone($oldCheckOutDate->timezone)->startOfDay();
                $extensionStart = $oldCheckOutDate->copy()->addDay(); //old checkOut date plus one

                // Check if the new check-out date is valid by ensuring no overlapping reservations
                $bedId = $stayDetails->bed_id;

                $stayDetailsModel = new StayDetails();
                // Find other reservations that use the same bed and overlap with the extension period
                $overlappingReservations = $stayDetailsModel->reservedBeds($extensionStart, $newCheckOutDate)
                    ->where('reservation_id', '!=', $reservation->id)
                    ->where('bed_id', $bedId)
                    ->count();

                if ($overlappingReservations > 0) {
                    throw ValidationException::withMessages([
                        'new_check_out_date' => 'Unable to extend the reservation because the current bed is already booked for future dates.'
                    ]);
                }

                // Handle gender conflicts - Check if need to update room gender schedule
                $room = $stayDetails->bed->room;
                $guestGender = $guest->gender;

                if ($room->eligible_gender === 'any') {
                    // Get other guests staying in the same room during our stay period
                    $otherGuestsInRoom = StayDetails::whereHas('bed', function($query) use ($room) {
                            $query->where('room_id', $room->id);
                        })
                        ->where('id', '!=', $stayDetails->id)
                        ->where('check_in_date', '<', $newCheckOutDate)
                        ->where('check_out_date', '>', $stayDetails->check_in_date)
                        ->whereNotIn('status', ['canceled', 'checked_out'])
                        ->with('guest')
                        ->get();

                    // Check if there are other guests with different genders
                    $otherGenders = $otherGuestsInRoom->pluck('guest.gender')->unique()->filter()->toArray();

                    if (count($otherGenders) > 0) {
                        // If other guests are present, their gender must match this guest's gender
                        foreach ($otherGenders as $otherGender) {
                            if ($otherGender !== $guestGender) {
                                throw ValidationException::withMessages([
                                    'new_check_out_date' => 'Cannot update checkout date because it would create a gender conflict in the room.'
                                ]);
                            }
                        }
                    }

                    // Find existing gender schedule that overlaps with our stay period
                    $existingSchedule = EligibleGenderSchedule::where('room_id', $room->id)
                        ->where(function($query) use ($stayDetails, $newCheckOutDate) {
                            $query->whereBetween('start_date', [$stayDetails->check_in_date, $newCheckOutDate])
                                ->orWhereBetween('end_date', [$stayDetails->check_in_date, $newCheckOutDate])
                                ->orWhere(function($q) use ($stayDetails, $newCheckOutDate) {
                                    $q->where('start_date', '<=', $stayDetails->check_in_date)
                                      ->where('end_date', '>=', $newCheckOutDate);
                                });
                        })
                        ->first();

                    if ($existingSchedule) {
                        // Update existing schedule if needed
                        if ($existingSchedule->eligible_gender !== $guestGender) {
                            throw ValidationException::withMessages([
                                'new_check_out_date' => 'Cannot update checkout date because it conflicts with an existing gender schedule.'
                            ]);
                        }

                        // Extend the schedule if needed
                        $startDate = min(new \DateTime($existingSchedule->start_date), new \DateTime($stayDetails->check_in_date));
                        $endDate = max(new \DateTime($existingSchedule->end_date), new \DateTime($newCheckOutDate));

                        $existingSchedule->start_date = $startDate->format('Y-m-d');
                        $existingSchedule->end_date = $endDate->format('Y-m-d');
                        $existingSchedule->save();
                    } else {
                        // Create new gender schedule for this stay period
                        if (count($otherGuestsInRoom) === 0) {
                            // Only create a schedule if the room will be occupied
                            EligibleGenderSchedule::create([
                                'room_id' => $room->id,
                                'start_date' => $stayDetails->check_in_date,
                                'end_date' => $newCheckOutDate,
                                'eligible_gender' => $guestGender
                            ]);
                        }
                    }
                } else if ($room->eligible_gender !== $guestGender) {
                    // The room has a fixed gender that doesn't match the guest - this shouldn't happen
                    throw ValidationException::withMessages([
                        'new_check_out_date' => 'Guest gender does not match room eligible gender.'
                    ]);
                }

                //Update check out date
                $stayDetails->check_out_date = $newCheckOutDate;

                //Calculate new guest billing
                if($stayDetails->is_exempted) {
                    $stayDetails->individual_billings = 0;
                } else {
                    $stayDetails->individual_billings = $stayDetails->bed->calculateBilling($stayDetails->check_in_date, $validated['new_check_out_date']);
                }

                $stayDetails->save();

                $reservation->recomputeBillings();

                if ($reservation->total_billings < 0 || $reservation->remaining_balance < 0) {
                    throw ValidationException::withMessages([
                        'new_check_out_date' => 'Unable to extend the reservation because the new remaining balance is less than zero.'
                    ]);
                }

            });
        } catch (\Exception $e) {
            return redirect()->back()->with(['error' => $e->getMessage()]);
        }

        return to_route('reservation.show', ['id' => $validated['reservation_id']])
            ->with('success', 'Reservation extended successfully.');
    }
}
