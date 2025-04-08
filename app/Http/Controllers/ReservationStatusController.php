<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\GuestBeds;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
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
            'status' => ['required', Rule::in(['confirmed', 'checked_in', 'checked_out'])]
        ]);

        $reservation = Reservation::where('hostel_office_id', Auth::user()->office_id)
            ->findOrFail($validated['reservation_id']);

        // Check for previous reservations with unpaid balances
        if ($validated['status'] === 'checked_in') {
            $reservation = Reservation::where('hostel_office_id', Auth::user()->office_id)
                ->with('guestBeds.bed')
                ->findOrFail($validated['reservation_id']);

            // Get beds reserved for current reservation
            $reservedBedIds = $reservation->guestBeds->pluck('bed_id')->toArray();

            // Check for previous reservations of the same beds with unpaid balances
            if (!empty($reservedBedIds)) {
                $previousReservations = Reservation::where('hostel_office_id', Auth::user()->office_id)
                    ->whereNotIn('status', ['pending', 'canceled'])
                    ->where(function ($query) {
                        $query->where('remaining_balance', '>', 0)
                            ->where('payment_type', '!=', 'pay_later');
                    })
                    ->whereHas('guestBeds', function ($query) use ($reservedBedIds) {
                        $query->whereIn('bed_id', $reservedBedIds);
                    })
                    ->whereNot('id', $reservation->id)
                    ->get();

                if (count($previousReservations) > 0) {
                    return back()->with('error', 'Cannot check in due to previous unpaid or not pay later reservations on the same beds.');
                }
            }
        }

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
        return Inertia::render('Guest/CheckReservationStatus/CheckReservationStatus');
    }

    public function checkStatus(string $code)
    {
        $reservation = Reservation::with(['hostelOffice.region'])
            ->select(
                'id',
                'reservation_code',
                'status',
                'check_in_date',
                'check_out_date',
                'total_billings',
                'remaining_balance',
                'hostel_office_id',
                'first_name',
                'last_name'
            )
            ->withCount('guests')
            ->where('reservation_code', $code)
            ->first();

        if (!$reservation) {
            return redirect()->back()->with('error', 'Reservation doesn\'t exist.');
        }

        // Obscure the guest's name but show first letter
        $reservation->first_name = $reservation->first_name[0] . str_repeat('*', strlen($reservation->first_name) - 1);
        $reservation->last_name = $reservation->last_name[0] . str_repeat('*', strlen($reservation->last_name) - 1);

        return Inertia::render('Guest/CheckReservationStatus/ReservationStatusResult', [
            'reservation' => $reservation
        ]);
    }


    public function search($search)
    {
        if (empty($search)) {
            return response()->json([
                'success' => false,
                'message' => 'Search term cannot be empty',
                'data' => null
            ], 400);
        }

        $reservations = Reservation::query()
            ->select([
                'id',
                'reservation_code',
                'check_in_date',
                'check_out_date',
            ])
            ->where(function ($query) use ($search) {
                $query->where('reservation_code', 'ILIKE', "%{$search}%")
                    ->orWhere('first_name', 'ILIKE', "%{$search}%")
                    ->orWhere('last_name', 'ILIKE', "%{$search}%")
                    ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'ILIKE', "%{$search}%");
            })
            ->get();

        if ($reservations->isEmpty()) {
            return Redirect::back()->with([
                'error' => 'No reservations found.',
            ]);
        }

        return Redirect::back()->with([
            'response_data' => $reservations
        ]);
    }
}
