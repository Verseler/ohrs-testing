<?php

namespace App\Http\Controllers;

use App\Mail\ReservationCodeMail;
use App\Models\Guest;
use App\Models\Office;
use App\Models\Region;
use App\Models\Reservation;
use App\Models\User;
use App\Notifications\NewReservationNotification;
use App\Jobs\SendReservationCodeEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ReservationProcessController extends Controller
{
    public function form(Request $request)
    {
        $hostelOffice = Office::with('region')->where('has_hostel', true)
            ->findOrFail($request->hostel_office_id);

        return Inertia::render('Guest/ReservationForm/ReservationForm', [
            'hostelOffice' => $hostelOffice
        ]);
    }

    public function create(Request $request)
    {
        $validated = $request->validate(
            [
                'check_in_date' => ['required', 'date', 'after_or_equal:today', 'before_or_equal:check_out_date'],
                'check_out_date' => ['required', 'date', 'after_or_equal:today', 'after_or_equal:check_in_date'],
                'first_name' => ['required', 'string', 'max:255'],
                'middle_initial' => ['nullable', 'string', 'max:1'],
                'last_name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'phone' => ['required', 'size:10', 'regex:/(9)[0-9]{9}/'],
                'hostel_office_id' => ['required', 'numeric'],
                'id_type' => ['required', 'string'],
                'employee_id' => ['required', 'string'],
                'purpose_of_stay' => ['required', 'string'],
                'guests' => ['required', 'array', 'min:1'],
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
                'guests.*.office.required' => 'Required'
            ]
        );

        try {
            DB::transaction(function () use ($validated) {
                $hostelOffice = Office::where('has_hostel', true)->findOrFail($validated['hostel_office_id']);

                //create an initial reservation
                $reservation = Reservation::create([
                    'reservation_code' => $this->generateReservationCode($hostelOffice->name),
                    'check_in_date' => $validated['check_in_date'],
                    'check_out_date' => $validated['check_out_date'],
                    'daily_rate' => 0,
                    'total_billings' => 0,
                    'remaining_balance' => 0,
                    'status' => 'pending',
                    'payment_type' => 'full_payment',
                    'first_name' => $validated['first_name'],
                    'middle_initial' => $validated['middle_initial'] ?? null,
                    'last_name' => $validated['last_name'],
                    'phone' => $validated['phone'],
                    'email' => $validated['email'] ?? null,
                    'hostel_office_id' => $hostelOffice->id,
                    'id_type' => $validated['id_type'],
                    'employee_id' => $validated['employee_id'],
                    'purpose_of_stay' => $validated['purpose_of_stay'] ?? null,
                ]);

                //create guests
                foreach ($validated['guests'] as $guest) {
                    $guest = Guest::create([
                        'first_name' => $guest['first_name'],
                        'last_name' => $guest['last_name'],
                        'gender' => $guest['gender'],
                        'office' => $guest['office'],
                        'reservation_id' => $reservation->id,
                        'is_exempted' => false
                    ]);
                }

                // Store reservation details in the session
                session([
                    'reservation_id' => $reservation->id,
                    'total_guests' => count($validated['guests'])
                ]);

                //send notification to admin
                $admins = User::where('office_id', $hostelOffice->id)->get();
                Notification::send(
                    $admins,
                    new NewReservationNotification($reservation)
                );

                //Send reservation code to guest email
                $details = [
                    'title' => 'Reservation Code - OHRS',
                    'content' => $reservation->reservation_code,
                ];

                SendReservationCodeEmail::dispatch($reservation->email, $details);
            });
        } catch (\Exception $e) {
            return redirect()->back()->with("error", $e->getMessage());
        }

        //redirect to confirmation
        return Inertia::location(route('reservation.confirmation'));
    }

    private function generateReservationCode(string $officeName): string
    {
        $date = now()->format('Ym'); // Year and month only

        // Office acronym codes
        $officeCodes = [
            'Regional Office' => 'RO',
            'PENRO Camiguin' => 'PC',
            'PENRO Misamis Oriental' => 'PMO',
        ];

        // Default code if office not found
        $officeCode = $officeCodes[$officeName] ?? 'RES';

        // Get the latest reservation for this office in current month
        $latestReservation = Reservation::where('reservation_code', 'like', "{$officeCode}-{$date}-%")
            ->orderBy('reservation_code', 'desc')
            ->first();

        // Determine the next sequence number
        if ($latestReservation) {
            $parts = explode('-', $latestReservation->reservation_code);
            $lastSequence = (int) end($parts);
            $sequence = $lastSequence + 1;
        } else {
            $sequence = 1;
        }

        $sequenceNumber = str_pad($sequence, 4, '0', STR_PAD_LEFT);

        return "{$officeCode}-{$date}-{$sequenceNumber}";
    }

    public function confirmation()
    {
        $reservationConfirmationInfo = [
            'check_in_date' => null,
            'check_out_date' => null,
            'status' => null,
            'reservation_code' => null,
            'hostel_office_name' => null,
            'total_guests' => null
        ];

        try {
            DB::transaction(function () use (&$reservationConfirmationInfo) {
                // Retrieve reservation details from the session
                $reservationId = session('reservation_id');
                $totalGuests = session('total_guests');

                $reservation = Reservation::findOrFail($reservationId);
                $hostelOffice = Office::with('region')->findOrFail($reservation->hostel_office_id);
                $regionName = $hostelOffice->region->name;

                $reservationConfirmationInfo = [
                    'check_in_date' => $reservation->check_in_date,
                    'check_out_date' => $reservation->check_out_date,
                    'status' => $reservation->status,
                    'reservation_code' => $reservation->reservation_code,
                    'hostel_office_name' => "Region $regionName - $hostelOffice->name",
                    'total_guests' => $totalGuests
                ];
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('errors', 'An error occurred while processing your reservation.');
        }

        return Inertia::render('Guest/ReservationConfirmation/ReservationConfirmation', [
            'reservation' => $reservationConfirmationInfo
        ]);
    }
}
