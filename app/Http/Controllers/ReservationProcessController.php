<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Office;
use App\Models\Region;
use App\Models\Reservation;
use App\Models\User;
use App\Notifications\NewReservationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ReservationProcessController extends Controller
{
    public function form(Request $request)
    {
        $regions = Region::all();
        $offices = Office::all();
        $hostelOffice = Office::with('region')->where('has_hostel', true)
            ->findOrFail($request->hostel_office_id);

        return Inertia::render('Guest/ReservationForm/ReservationForm', [
            'regions' => $regions,
            'offices' => $offices,
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
                'guest_office_id' => ['required', 'numeric'],
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
                //validate if guest and hostel office exists
                $guestOffice = Office::findOrFail($validated['guest_office_id']);
                $hostelOffice = Office::where('has_hostel', true)->findOrFail($validated['hostel_office_id']);


                //create an initial reservation
                $reservation = Reservation::create([
                    'reservation_code' => $this->generateReservationCode($guestOffice->id, $hostelOffice->id),
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
                    'guest_office_id' => $guestOffice->id,
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
            });
        } catch (\Exception $e) {
            return redirect()->back()->with("error", $e->getMessage());
        }

        //redirect to confirmation
        return redirect()->route('reservation.confirmation');
    }

    private function generateReservationCode(int $guestOfficeId, int $hostelOfficeId): string
    {
        $date = now()->format('Ymd');
        $maxAttempts = 100;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            $randomNumber = random_int(1000, 9999);
            $code = "RES-{$date}-{$guestOfficeId}{$hostelOfficeId}{$randomNumber}";

            // Check if code already exists
            if (!Reservation::where('reservation_code', $code)->exists()) {
                return $code;
            }

            $attempt++;
        }

        // Fallback to sequential if random fails after max attempts
        $latestReservation = Reservation::where('reservation_code', 'like', "RES-{$date}-%")
            ->orderBy('reservation_code', 'desc')
            ->first();

        $sequence = $latestReservation ? (int) explode('-', $latestReservation->reservation_code)[2] + 1 : 1;
        return "RES-{$date}{$guestOfficeId}{$hostelOfficeId}-" . str_pad($sequence, 6, '0', STR_PAD_LEFT);
    }

    public function confirmation()
    {
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

        return Inertia::render('Guest/ReservationConfirmation', [
            'reservation' => $reservationConfirmationInfo
        ]);
    }
}
