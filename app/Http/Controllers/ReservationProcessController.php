<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Office;
use App\Models\Reservation;
use App\Models\User;
use App\Notifications\NewReservationNotification;
use App\Jobs\SendReservationCodeEmail;
use App\Models\StayDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ReservationProcessController extends Controller
{
    public function form(Request $request)
    {
        $hostelOffice = Office::with('region')->where('has_hostel', true)
            ->findOrFail($request->hostel_office_id);

        $offices = Office::with('region')
            ->get()
            ->map(fn($office) => "{$office->name} - {$office->region->name}")
            ->toArray();

        return Inertia::render('Guest/ReservationForm/ReservationForm', [
            'hostelOffice' => $hostelOffice,
            'offices' => $offices
        ]);
    }

    public function create(Request $request)
    {
        $validated = $request->validate(
            [
                'first_name' => ['required', 'string', 'max:255'],
                'middle_initial' => ['nullable', 'string', 'max:1'],
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
                $hostelOffice = Office::where('has_hostel', true)->findOrFail($validated['hostel_office_id']);

                //create an initial reservation
                $reservation = Reservation::create([
                    'code' => $this->generateReservationCode($hostelOffice->name),
                    'total_billings' => 0,
                    'remaining_balance' => 0,
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
                $admins = User::where('office_id', $hostelOffice->id)->get();
                Notification::send(
                    $admins,
                    new NewReservationNotification($reservation)
                );

                //Send reservation code to email
                $details = [
                    'title' => 'Reservation Code - OHRS',
                    'content' => $reservation->code,
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
        $date = now()->format('Ym');

        // Office acronym codes
        $officeCodes = [
            'Regional Office' => 'RO',
            'PENRO Camiguin' => 'PC',
            'PENRO Misamis Oriental' => 'PMO',
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

    public function confirmation()
    {
        $reservationDetails = [
            'from' => null,
            'to' => null,
            'code' => null,
            'hostel_office_name' => null,
            'total_guests' => null
        ];

        try {
            DB::transaction(function () use (&$reservationDetails) {
                // Retrieve reservation details from the session
                $reservationId = session('reservation_id');
                $totalGuests = session('total_guests');

                $reservation = Reservation::findOrFail($reservationId);
                $stayRange = $reservation->getStayDateRange();
                $hostelOffice = Office::with('region')->findOrFail($reservation->hostel_office_id);
                $regionName = $hostelOffice->region->name;

                $reservationDetails = [
                    'from' => $stayRange['min_check_in_date'],
                    'to' => $stayRange['max_check_out_date'],
                    'code' => $reservation->code,
                    'hostel_office_name' => "Region $regionName - $hostelOffice->name",
                    'total_guests' => $totalGuests
                ];
        
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('errors', 'An error occurred while processing your reservation.');
        }

        return Inertia::render('Guest/ReservationConfirmation/ReservationConfirmation', [
            'reservation' => $reservationDetails
        ]);
    }
}
