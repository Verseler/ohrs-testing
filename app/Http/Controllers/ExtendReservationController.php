<?php

namespace App\Http\Controllers;

use App\Models\ExtendedReservation;
use App\Models\GuestBeds;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ExtendReservationController extends Controller
{
    public function extendForm(int $id)
    {
        $reservation = Reservation::where('hostel_office_id', Auth::user()->office_id)->findOrFail($id);
        return Inertia::render('Admin/Reservation/ReservationExtendForm', [
            'reservation' => $reservation
        ]);
    }

    public function extend(Request $request)
    {
        $reservation = Reservation::findOrFail($request->reservation_id);

        $validated = $request->validate([
            'reservation_id' => ['required', 'exists:reservations,id'],
            'new_check_out_date' => [
                'required',
                'date',
                'after_or_equal:today',
                function ($attribute, $value, $fail) use ($reservation) {
                    if (Carbon::parse($value)->lte(Carbon::parse($reservation->check_out_date))) {
                        $fail('The new check-out date must be after the current check-out date.');
                    }
                },
            ],
        ]);

        try {
            DB::transaction(function () use ($reservation, $validated) {
                $reservation = Reservation::findOrFail($validated['reservation_id']);

                $oldCheckOutDate = Carbon::parse($reservation->check_out_date)->startOfDay();
                $newCheckOutDate = Carbon::parse($validated['new_check_out_date'], 'Asia/Manila')->setTimezone($oldCheckOutDate->timezone)->startOfDay();

                //check if newCheckOutDate is valid which means there is no overlapping of reservation
                $guestBed = new GuestBeds();
                $reservedBedIds = $guestBed->reservedBeds($oldCheckOutDate, $newCheckOutDate)
                    ->pluck('bed_id')->toArray();

                $currentReservedBedIds = $reservation->reservedBeds->pluck('id')->toArray();

                // Check if any of the currently reserved beds are already reserved by others
                $overlappingBeds = array_diff($reservedBedIds, $currentReservedBedIds);

                if (!empty($overlappingBeds)) {
                    throw ValidationException::withMessages([
                        'new_check_out_date' => 'Unable to extend the reservation because the current beds are already booked for future dates.'
                    ]);
                }

                //get additional days
                $additionalDays = $oldCheckOutDate->diffInDays($newCheckOutDate, false);

                ExtendedReservation::create([
                    'check_in_date' => $reservation->check_in_date,
                    'old_check_out_date' => $reservation->check_out_date,
                    'new_check_out_date' => $newCheckOutDate,
                    'days_extended' => $additionalDays,
                    'reservation_id' => $reservation->id
                ]);

                //calculate additional charges
                $dailyRate = $reservation->daily_rate;
                $additionalCharges = $dailyRate * $additionalDays;

                $reservation->total_billings += $additionalCharges;
                $reservation->remaining_balance += $additionalCharges;
                $reservation->check_out_date = $newCheckOutDate;
                $reservation->save();
            });
        } catch (\Exception $e) {
            return redirect()->back()->with(['error' => $e->getMessage()]);
        }

        return to_route('reservation.show', ['id' => $reservation->id])
            ->with('success', 'Reservation extended successfully.');
    }
}
