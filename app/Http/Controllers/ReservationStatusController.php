<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
