<?php

namespace App\Http\Controllers;

use App\Models\GuestBeds;
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
        $reservation = Reservation::where('hostel_office_id', Auth::user()->office_id)->findOrFail($id);
        return Inertia::render('Admin/Reservation/ReservationUpdateCheckoutForm', [
            'reservation' => $reservation
        ]);
    }

    public function updateCheckout(Request $request)
    {
        $reservation = Reservation::findOrFail($request->reservation_id);

        $validated = $request->validate([
            'reservation_id' => ['required', 'exists:reservations,id'],
            'new_check_out_date' => [
                'required',
                'date',
                'after:check_in_date',
                'after_or_equal:today'
            ],
        ]);

        try {
            DB::transaction(function () use ($reservation, $validated) {
                $reservation = Reservation::findOrFail($validated['reservation_id']);

                $oldCheckOutDate = Carbon::parse($reservation->check_out_date)->startOfDay();
                $newCheckOutDate = Carbon::parse($validated['new_check_out_date'], 'Asia/Manila')->setTimezone($oldCheckOutDate->timezone)->startOfDay();
                $extensionStart = $oldCheckOutDate->copy()->addDay(); //old checkOut date plus one

                // Check if the new check-out date is valid by ensuring no overlapping reservations
                $guestBed = new GuestBeds();
                $reservedBedIds = $guestBed->reservedBeds($extensionStart, $newCheckOutDate)
                    ->where('reservation_id', '!=', $reservation->id)
                    ->pluck('bed_id')->toArray();

                $currentReservedBedIds = $reservation->reservedBeds->pluck('id')->toArray();

                // Identify overlapping beds that are reserved by others
                $overlappingBeds = array_intersect($reservedBedIds, $currentReservedBedIds);

                if (!empty($overlappingBeds)) {
                    throw ValidationException::withMessages([
                        'new_check_out_date' => 'Unable to extend the reservation because the current beds are already booked for future dates.'
                    ]);
                }

                //get additional days
                $dailyRate = $reservation->daily_rate;
                $additionalDays = $oldCheckOutDate->diffInDays($newCheckOutDate, false);


                //if reservation checkout date extension
                if ($additionalDays > 0) {
                    $additionalCharges = $dailyRate * $additionalDays;

                    $reservation->total_billings += $additionalCharges;
                    $reservation->remaining_balance += $additionalCharges;
                    $reservation->check_out_date = $newCheckOutDate;
                    $reservation->save();
                }

                //if reservation checkout date reduction
                if ($additionalDays < 0) {
                    $reductionAmount = $dailyRate * abs($additionalDays); //reduction days

                    $reservation->total_billings -= $reductionAmount;
                    $reservation->remaining_balance -= $reductionAmount;
                    $reservation->check_out_date = $newCheckOutDate;

                    if ($reservation->total_billings < 0 || $reservation->remaining_balance < 0) {
                        throw ValidationException::withMessages([
                            'new_check_out_date' => 'Unable to extend the reservation because the new remaining balance is less than zero.'
                        ]);
                    } else {
                        $reservation->save();
                    }
                }
            });
        } catch (\Exception $e) {
            return redirect()->back()->with(['error' => $e->getMessage()]);
        }

        return to_route('reservation.show', ['id' => $reservation->id])
            ->with('success', 'Reservation extended successfully.');
    }
}
