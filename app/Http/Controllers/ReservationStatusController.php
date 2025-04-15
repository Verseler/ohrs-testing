<?php

namespace App\Http\Controllers;

use App\Jobs\SendReservationCancelledEmail;
use App\Models\Office;
use App\Models\Reservation;
use App\Models\StayDetails;
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
            ->with(['guests' => function($query) {
                $query->whereHas('stayDetails', function($query) {
                    $query->whereNotIn('status', ['canceled', 'checked_out']);
                });
            }, 'guests.stayDetails' => function($query) {
                $query->whereNotIn('status', ['canceled', 'checked_out']);
            }])
            ->findOrFail($id);

        return Inertia::render('Admin/Reservation/EditGuestReservationStatus', [
            'reservation' => $reservation
        ]);
    }

    public function editStatus(Request $request)
    {
        $validated = $request->validate([
            'reservation_id' => ['required', 'exists:reservations,id'],
            'status' => ['required', Rule::in(['confirmed', 'checked_in', 'checked_out'])],
            'selected_guest_id' => ['required', 'exists:guests,id']
        ]);

        $reservation = Reservation::where('hostel_office_id', Auth::user()->office_id)
                    ->with('guests.stayDetails')
                    ->findOrFail($validated['reservation_id']);

       try {
            DB::transaction(function () use ($validated, $reservation) {
                $guest = $reservation->guests()->findOrFail($validated['selected_guest_id']);

                $guest->stayDetails()->update([
                    'status' => $validated['status']
                ]);

                // Reload stayDetails to get the updated status
                $reservation->load('guests.stayDetails');

                // Check if this was the last guest with the previous status
                $previousStatusCount = $reservation->guests->filter(function($g) use ($reservation) {
                    return $g->stayDetails->status === $reservation->general_status;
                })->count();

                if ($previousStatusCount == 0) {
                    // Update reservation general_status to next status
                    $statusOrder = ['confirmed', 'checked_in', 'checked_out'];
                    $currentIndex = array_search($reservation->general_status, $statusOrder);
                    if ($currentIndex !== false && isset($statusOrder[$currentIndex + 1])) {
                        $reservation->update(['general_status' => $statusOrder[$currentIndex + 1]]);
                    }
                }
            });
       }
       catch(\Exception $e) {
        return redirect()->back()->with('error', 'Failed to update reservation status: ' . $e->getMessage());
       }

        return to_route('reservation.show', ['id' => $reservation->id])
            ->with('success', 'Reservation status updated successfully.');
    }


    public function editAllStatusForm(int $id)
    {
        $reservation = Reservation::with('guests.stayDetails.bed.room')
            ->addSelect([
                'min_check_in_date' => StayDetails::select('check_in_date')
                    ->whereColumn('reservation_id', 'reservations.id')
                    ->orderBy('check_in_date')
                    ->limit(1),
                'max_check_out_date' => StayDetails::select('check_out_date')
                    ->whereColumn('reservation_id', 'reservations.id')
                    ->orderByDesc('check_out_date')
                    ->limit(1)
            ])
            ->where('hostel_office_id', Auth::user()->office_id)
            ->findOrFail($id);

        return Inertia::render('Admin/Reservation/EditReservationStatus', [
            'reservation' => $reservation,
        ]);
    }

    public function editAllStatus(Request $request)
    {
        $validated = $request->validate([
            'reservation_id' => ['required', 'exists:reservations,id'],
            'status' => ['required', Rule::in(['confirmed', 'checked_in', 'checked_out'])]
        ]);

        $reservation = Reservation::where('hostel_office_id', Auth::user()->office_id)
            ->findOrFail($validated['reservation_id']);


        //Prevent checking in new reservation if the previous reservation has unpaid balance or its payment type is not pay later
        if ($validated['status'] === 'checked_in') {
            $reservation = Reservation::where('hostel_office_id', Auth::user()->office_id)
                ->with('stayDetails.bed')
                ->findOrFail($validated['reservation_id']);

            $reservedBedIds = $reservation->stayDetails->pluck('bed_id')->toArray();

            // Check for previous reservations of the same beds with unpaid balances
            if (!empty($reservedBedIds)) {
                $previousReservations = Reservation::where('hostel_office_id', Auth::user()->office_id)
                    ->whereNotIn('general_status', ['pending', 'canceled'])
                    ->where(function ($query) {
                        $query->where('remaining_balance', '>', 0)
                            ->where('payment_type', '!=', 'pay_later');
                    })
                    ->whereHas('stayDetails', function ($query) use ($reservedBedIds) {
                        $query->whereIn('bed_id', $reservedBedIds);
                    })
                    ->whereNot('id', $reservation->id)
                    ->get();

                if (count($previousReservations) > 0) {
                    return back()->with('error', 'Cannot check in due to previous unpaid or not pay later reservations on the same beds.');
                }
            }
        }

        $reservation->stayDetails()->where('status', $reservation->general_status)->update([
            'status' => $validated['status']
        ]);
        $reservation->general_status = $validated['status'];
        $reservation->save();

        return to_route('reservation.show', ['id' => $reservation->id])
            ->with('success', 'Reservation status updated successfully.');
    }



    public function cancel(int $id)
    {
        try {
            DB::transaction(function () use ($id) {
                $reservation = Reservation::where('hostel_office_id', Auth::user()->office_id)
                    ->with(['stayDetails', 'guests'])
                    ->findOrFail($id);

                // Delete all bed-guest associations for this reservation
                if ($reservation->stayDetails->isNotEmpty()) {
                    $reservation->stayDetails()->delete();
                }

                // Delete all guests associated with this reservation
                if ($reservation->guests->isNotEmpty()) {
                    $reservation->guests()->delete();
                }

                // Update reservation status
                $reservation->general_status = 'canceled';
                $reservation->stayDetails()->update([
                    'status' => 'canceled',
                    'individual_billings' => 0
                ]);
                $reservation->total_billings = 0;
                $reservation->remaining_balance = 0;
                $reservation->save();

                  //Send reservation code to email
                  $details = [
                    'title' => 'Reservation Cancellation Notice',
                    'content' => $reservation->code,
                ];
                SendReservationCancelledEmail::dispatch($reservation->email, $details);
            });
        } catch (\Exception $e) {
            return redirect()->route('reservation.show', ['id' => $id])
                ->with('error', 'Failed to cancel reservation: ' . $e->getMessage());
        }

        return redirect()->route('reservation.list')
            ->with('success', 'Successfully canceled reservation and removed guest data.');
    }


    //Check Reservation Status for Guests
    public function checkStatusForm()
    {
        $hostels = Office::select('id as value', 'name as label')->where('has_hostel', true)->get();

        return Inertia::render('Guest/CheckReservationStatus/CheckReservationStatus', [
            'hostels' => $hostels
        ]);
    }

    public function checkStatus(string $code)
    {
        $reservation = Reservation::with(['hostelOffice'])
            ->select(
                'id',
                'code',
                'general_status',
                'total_billings',
                'remaining_balance',
                'hostel_office_id',
                'first_name',
                'last_name'
            )
            ->addSelect([
                'min_check_in_date' => StayDetails::select('check_in_date')
                    ->whereColumn('reservation_id', 'reservations.id')
                    ->orderBy('check_in_date')
                    ->limit(1),
                'max_check_out_date' => StayDetails::select('check_out_date')
                    ->whereColumn('reservation_id', 'reservations.id')
                    ->orderByDesc('check_out_date')
                    ->limit(1)
            ])
            ->withCount('guests')
            ->where('code', $code)
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


    public function search(Request $request)
    {
        $validated = $request->validate([
            'search' => ['required', 'string', 'max:255'],
            'hostel_id' => ['required', 'exists:offices,id'],
        ]);

        if (empty($validated['search'])) {
            return response()->json([
                'success' => false,
                'message' => 'Search term cannot be empty',
                'data' => null
            ]);
        }

        $reservations = Reservation::query()
            ->select([
                'id',
                'code'
            ])
            ->addSelect([
                'min_check_in_date' => StayDetails::select('check_in_date')
                    ->whereColumn('reservation_id', 'reservations.id')
                    ->orderBy('check_in_date')
                    ->limit(1),
                'max_check_out_date' => StayDetails::select('check_out_date')
                    ->whereColumn('reservation_id', 'reservations.id')
                    ->orderByDesc('check_out_date')
                    ->limit(1)
            ])
            ->where('hostel_office_id', $validated['hostel_id'])
            ->where(function ($query) use ($validated) {
                $query->where('code', 'ILIKE', "%{$validated['search']}%")
                    ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'ILIKE', "%{$validated['search']}%")
                    ->orWhereHas('guests', function($q) use ($validated) {
                        $q->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'ILIKE', "%{$validated['search']}%");
                    });
            })
            ->get();

        if ($reservations->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No reservations found.',
                'data' => null
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Reservations found.',
            'data' => $reservations->values()
        ]);
    }
}
