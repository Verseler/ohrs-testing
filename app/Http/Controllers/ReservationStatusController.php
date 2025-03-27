<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\GuestBeds;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ReservationStatusController extends Controller
{
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

    public function cancel(int $id)
    {
        try {
            DB::transaction(function () use ($id) {
                $reservation = Reservation::where('hostel_office_id', Auth::user()->office_id)
                    ->with(['guestBeds', 'guests'])
                    ->findOrFail($id);

                // Delete all bed-guest associations for this reservation
                if ($reservation->guestBeds->isNotEmpty()) {
                    GuestBeds::where('reservation_id', $reservation->id)->delete();
                }

                // Delete all guests associated with this reservation
                if ($reservation->guests->isNotEmpty()) {
                    $reservation->guests()->delete();
                }

                // Update reservation status
                $reservation->status = 'canceled';
                $reservation->daily_rate = 0;
                $reservation->total_billings = 0;
                $reservation->remaining_balance = 0;
                $reservation->save();
            });
        } catch (\Exception $e) {
            return redirect()->route('reservation.show', ['id' => $id])
                ->with('error', 'Failed to cancel reservation: ' . $e->getMessage());
        }

        return redirect()->route('reservation.show', ['id' => $id])
            ->with('success', 'Successfully canceled reservation and removed guest data.');
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
}
