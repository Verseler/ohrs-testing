<?php

namespace App\Http\Controllers;

use App\Jobs\SendModifyReservationTokenEmail;
use App\Models\Reservation;
use App\Models\StayDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class EditReservationController extends Controller
{
    public function requestEdit(Request $request) {
        $validated = $request->validate([
            'reservation_code' => ['required', 'string', 'exists:reservations,code']
        ]);

        $reservation = Reservation::where('code', $validated['reservation_code'])
        ->firstOrFail();

        try {
            DB::transaction(function () use ($validated, $reservation) {
                $token = $this->generateToken($reservation);

                // Send email with token
                $details = [
                    'title' => 'Token',
                    'content' => $token,
                ];
             
                SendModifyReservationTokenEmail::dispatch($reservation->email, $details);
          
                session([
                    'action' => 'edit',
                    'email' => $reservation->email,
                    'reservation_id' => $reservation->id
                ]);
            });
        }
        catch(\Exception $e) {
            return redirect()->back()->with('errors', $e->getMessage());
        }

        return to_route('reservation.otpForm')->with('success', 'A code has been sent to your email for confirmation.');
    }


    public function verifyEdit($reservation_id, string $token) {
        $reservation = Reservation::where('general_status', 'pending')->findOrFail($reservation_id);

        if ($reservation->modify_token !== $token ||
        now()->gt($reservation->modify_token_expires_at)) {
        abort(403, 'Invalid or expired token');
        }

        // Clear token after successful verification
        $reservation->update([
            'modify_token' => null,
            'modify_token_expires_at' => null
        ]);

        // Store verification in session
        session()->put("reservation_edit_verified_{$reservation->id}", true);

        $reservation = Reservation::with([
            'guests',
            'hostelOffice',
            'stayDetails.guest'
        ])->findOrFail($reservation->id);
 
        return Inertia::render('Guest/ModifyReservation/EditReservationForm', [
            'reservation' => $reservation
        ]);
    }

   public function edit(Request $request)
   {
       $validated = $request->validate([
           'reservation_id' => ['required', 'exists:reservations,id'],
           'stay_details' => ['required', 'array'],
           'stay_details.*.id' => ['required', 'exists:stay_details,id'],
           'stay_details.*.check_in_date' => ['required', 'date', 'after_or_equal:today'],
           'stay_details.*.check_out_date' => ['required', 'date', 'after_or_equal:check_in_date'],
       ]);

       $reservation = Reservation::with(['stayDetails'])
       ->findOrFail($validated['reservation_id']);

       try {
       DB::transaction(function () use ($validated, $reservation) {
            // Check if verification is exist or not expired
           if (!session()->get("reservation_edit_verified_{$reservation->id}")) {
               abort(403, 'Verification required');
           }


           foreach($validated['stay_details'] as $stayDetail) {
                StayDetails::where([
                    ['id', '=', $stayDetail['id']],
                    ['reservation_id', '=', $reservation->id]
                ])
                    ->update([
                        'check_in_date' => $stayDetail['check_in_date'],
                        'check_out_date' => $stayDetail['check_out_date']
                    ]);
            }
       });
       }
       catch(\Exception $e) {
           return redirect()->back()->with('errors', $e->getMessage());
       }

       return redirect()
           ->route('reservation.checkStatus', ['code' => $reservation->code])
           ->with('success', 'Reservation updated successfully');
   }


   private function generateToken(Reservation $reservation) {
        // Generate 6-digit code
        $token = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = now()->addMinutes(5);

        $reservation->update([
            'modify_token' => $token,
            'modify_token_expires_at' => $expiresAt
        ]);

        return $token;
   }
}
