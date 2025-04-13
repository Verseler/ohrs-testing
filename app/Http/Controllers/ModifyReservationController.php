<?php

namespace App\Http\Controllers;

use App\Jobs\SendModifyReservationTokenEmail;
use App\Jobs\SendReservationCodeEmail;
use App\Models\Guest;
use App\Models\Office;
use App\Models\RebookReservation;
use App\Models\Reservation;
use App\Models\StayDetails;
use App\Models\User;
use App\Notifications\ModifyReservationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ModifyReservationController extends Controller
{
    public function requestModify(Request $request)
    {
        $validated = $request->validate([
            'reservation_code' => ['required', 'string', 'exists:reservations,code'],
            'action' => ['required', 'string', Rule::in(['edit', 'cancel', 'rebook'])]
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
                    'action' => $validated['action'],
                    'email' => $reservation->email,
                    'reservation_id' => $reservation->id
                ]);
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('errors', $e->getMessage());
        }

        return to_route('reservation.otpForm')->with('success', 'A code has been sent to your email for confirmation.');
    }


    public function verifyEdit($reservation_id, string $token)
    {
        $reservation = Reservation::where('general_status', 'pending')->findOrFail($reservation_id);

        if (
            $reservation->modify_token !== $token ||
            now()->gt($reservation->modify_token_expires_at)
        ) {
            abort(403, 'Invalid or expired token');
        }

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
                foreach ($validated['stay_details'] as $stayDetail) {
                    StayDetails::where([
                        ['id', '=', $stayDetail['id']],
                        ['reservation_id', '=', $reservation->id]
                    ])
                        ->update([
                            'check_in_date' => $stayDetail['check_in_date'],
                            'check_out_date' => $stayDetail['check_out_date']
                        ]);
                }

                //send notification to admin
                $admins = User::where('office_id', $reservation->hostel_office_id)->get();
                Notification::send(
                    $admins,
                    new ModifyReservationNotification('edited', $reservation)
                );
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('errors', $e->getMessage());
        }

        return redirect()
            ->route('reservation.checkStatus', ['code' => $reservation->code])
            ->with('success', 'Reservation updated successfully');
    }

    public function verifyCancel($reservation_id, string $token)
    {
        $reservation = Reservation::where('general_status', 'pending')
            ->orWhere('general_status', 'confirmed')
            ->findOrFail($reservation_id);

        if (
            $reservation->modify_token !== $token ||
            now()->gt($reservation->modify_token_expires_at)
        ) {
            abort(403, 'Invalid or expired token');
        }

        try {
            DB::transaction(function () use ($reservation) {
                // Clear token after successful verification
                $reservation->update([
                    'modify_token' => null,
                    'modify_token_expires_at' => null
                ]);

                $reservation = Reservation::with([
                    'guests',
                    'hostelOffice',
                    'stayDetails.guest'
                ])->findOrFail($reservation->id);

                $this->cancelReservation($reservation->id);

                //send notification to admin
                $admins = User::where('office_id', $reservation->hostel_office_id)->get();
                Notification::send(
                    $admins,
                    new ModifyReservationNotification('canceled', $reservation)
                );
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('errors', $e->getMessage());
        }


        return redirect()
            ->route('reservation.checkStatus', ['code' => $reservation->code])
            ->with('success', 'Reservation updated successfully');
    }


    public function verifyRebook($reservation_id, string $token)
    {
        $reservation = Reservation::where('general_status', 'confirmed')->findOrFail($reservation_id);

        if (
            $reservation->modify_token !== $token ||
            now()->gt($reservation->modify_token_expires_at)
        ) {
            abort(403, 'Invalid or expired token');
        }

        // Store verification in session
        session()->put("reservation_rebook_verified_{$reservation->id}", true);

        $reservation = Reservation::with([
            'guests.stayDetails',
            'hostelOffice',
        ])->findOrFail($reservation->id);

        $offices = Office::all();

        return Inertia::render('Guest/ModifyReservation/RebookReservationForm', [
            'reservation' => $reservation,
            'offices' => $offices
        ]);
    }

    public function rebook(Request $request)
    {
        $validated = $request->validate(
            [
                'prev_reservation_id' => ['required', 'exists:reservations,id'],
                'first_name' => ['required', 'string', 'max:255'],
                'middle_initial' => ['required', 'string', 'max:1'],
                'last_name' => ['required', 'string', 'max:255'],
                'phone' => ['required', 'size:10', 'regex:/(9)[0-9]{9}/'],
                'email' => ['required', 'email', 'max:255'],
                'hostel_office_id' => ['required', 'numeric'],
                'id_type' => ['required', 'string'],
                'employee_id' => ['required', 'string'],
                'purpose_of_stay' => ['required', 'string'],
                'guests' => ['required', 'array', 'min:1'],
                'guests.*.check_in_date' => ['required', 'date', 'after_or_equal:today', 'before_or_equal:guests.*.check_out_date'],
                'guests.*.check_out_date' => ['required', 'date', 'after_or_equal:today', 'after_or_equal:guests.*.check_in_date'],
                'guests.*.first_name' => ['required', 'string', 'max:20'],
                'guests.*.last_name' => ['required', 'string', 'max:20'],
                'guests.*.gender' => ['required', Rule::in(['male', 'female'])],
                'guests.*.office' => ['required', 'string'],
            ],
            [
                'guests.*.first_name.required' => 'Required.',
                'guests.*.first_name.string' => 'First name must be a string.',
                'guests.*.first_name.max' => 'Must be at most 20 characters.',
                'guests.*.last_name.required' => 'Required.',
                'guests.*last_name.string' => 'Last name must be a string.',
                'guests.*.last_name.max' => 'Must be at most 20 characters.',
                'guests.*.gender.required' => 'Required.',
                'guests.*.office.required' => 'Required',
                'guests.*.check_in_date.required' => 'Required.',
                'guests.*.check_in_date.date' => 'Invalid date.',
                'guests.*.check_in_date.after_or_equal' => 'Must be after or equal to today.',
                'guests.*.check_in_date.before_or_equal' => 'Must be before or equal to check out date.',
                'guests.*.check_out_date.required' => 'Required.',
                'guests.*.check_out_date.date' => 'Invalid date.',
            ]
        );

        try {
            DB::transaction(function () use ($validated) {
                //Cancel old reservation

                $prevReservation = Reservation::findOrFail($validated['prev_reservation_id']);
                $this->cancelReservation($prevReservation->id);


                $hostelOffice = Office::where('has_hostel', true)->findOrFail($validated['hostel_office_id']);

                //create an initial reservation
                $reservation = Reservation::create([
                    'code' => $this->generateReservationCode($hostelOffice->name),
                    'total_billings' => 0,
                    'remaining_balance' => 0,
                    'payment_type' => 'full_payment',
                    'first_name' => $validated['first_name'],
                    'middle_initial' => $validated['middle_initial'],
                    'last_name' => $validated['last_name'],
                    'phone' => $validated['phone'],
                    'email' => $validated['email'],
                    'hostel_office_id' => $hostelOffice->id,
                    'id_type' => $validated['id_type'],
                    'employee_id' => $validated['employee_id'],
                    'purpose_of_stay' => $validated['purpose_of_stay'],
                ]);

                //create guests
                foreach ($validated['guests'] as $guest) {
                    $currentGuest = Guest::create([
                        'first_name' => $guest['first_name'],
                        'last_name' => $guest['last_name'],
                        'gender' => $guest['gender'],
                        'office' => $guest['office'],
                        'reservation_id' => $reservation->id,
                    ]);

                    StayDetails::create([
                        'check_in_date' => $guest['check_in_date'],
                        'check_out_date' => $guest['check_out_date'],
                        'individual_billings' => 0,
                        'is_exempted' => false,
                        'status' => 'pending',
                        'bed_id' => null,
                        'reservation_id' => $reservation->id,
                        'guest_id' => $currentGuest->id,
                    ]);
                }

                
                // Store reservation details in the session
                session([
                    'reservation_id' => $reservation->id,
                    'total_guests' => count($validated['guests'])
                ]);


                //send notification to admin
                $admins = User::where('office_id', $reservation->hostel_office_id)->get();
                Notification::send(
                    $admins,
                    new ModifyReservationNotification('rebook', $reservation)
                );


                //Send reservation code to email
                $details = [
                    'title' => 'Reservation Code - OHRS',
                    'content' => $reservation->code,
                ];
                SendReservationCodeEmail::dispatch($reservation->email, $details);

                //Create a reference from old to new reservation
                RebookReservation::create([
                    'prev_reservation_id' => $prevReservation->id,
                    'new_reservation_id' => $reservation->id
                ]);
            });
        } catch (\Exception $e) {
            return redirect()->back()->with("error", $e->getMessage());
        }

        //redirect to confirmation
        return Inertia::location(route('reservation.confirmation'));
    }


    private function cancelReservation(int $id)
    {
        try {
            DB::transaction(function () use ($id) {
                $reservation = Reservation::with(['stayDetails', 'guests'])
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
            });
        } catch (\Exception $e) {
            return redirect()->route('reservation.show', ['id' => $id])
                ->with('error', 'Failed to cancel reservation: ' . $e->getMessage());
        }
    }


    private function generateToken(Reservation $reservation)
    {
        // Generate 6-digit code
        $token = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = now()->addMinutes(5);

        $reservation->update([
            'modify_token' => $token,
            'modify_token_expires_at' => $expiresAt
        ]);

        return $token;
    }

    private function generateReservationCode(string $officeName): string
    {
        $date = now()->format('Ym');

        // Office acronym codes
        $officeCodes = [
            'Region 10' => 'RO',
            'PENRO Camiguin' => 'TCAM',
            'PENRO Misamis Oriental' => 'ILPLS',
        ];

        // Default code if office not found
        $officeCode = $officeCodes[$officeName] ?? 'RES';

        // Get the latest reservation for this office in current month
        $latestReservation = Reservation::where('code', 'like', "{$officeCode}-{$date}-%")
            ->orderBy('code', 'desc')
            ->first();

        // Determine the next sequence number
        if ($latestReservation) {
            $parts = explode('-', $latestReservation->code);
            $lastSequence = (int) end($parts);
            $sequence = $lastSequence + 1;
        } else {
            $sequence = 1;
        }

        $sequenceNumber = str_pad($sequence, 4, '0', STR_PAD_LEFT);

        return "{$officeCode}-{$date}-{$sequenceNumber}";
    }
}
