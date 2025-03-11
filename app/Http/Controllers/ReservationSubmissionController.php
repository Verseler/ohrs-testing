<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Reservation;
use App\Models\Bed;
use App\Models\Office;
use App\Models\ReservationAssignments;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

class ReservationSubmissionController extends Controller
{
    //Form page for reservation process for guests
    public function form(Request $request)
    {
        $offices = Office::all();
        $hostOffice = Office::where('has_hostel', true)->findOrFail($request->host_office_id);

        return Inertia::render('ReservationProcess/ReservationForm', [
            'canLogin' => Route::has('login'),
            'offices' => $offices,
            'hostOffice' => $hostOffice
        ]);
    }

    //For creating new reservation
    public function create(Request $request)
    {
        $validated = $request->validate([
            'total_guests' => ['required', 'integer', 'min:1'],
            'total_males' => ['required', 'integer', 'min:0'],
            'total_females' => ['required', 'integer', 'min:0'],
            'check_in_date' => ['required', 'date', 'after_or_equal:today', 'before_or_equal:check_out_date'],
            'check_out_date' => ['required', 'date', 'after_or_equal:today', 'after_or_equal:check_in_date'],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_initial' => ['nullable', 'string'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['required', 'regex:/(9)[0-9]{9}/'],
            'guest_office_id' => ['required', 'numeric'],
            'host_office_id' => ['required', 'numeric'],
            'employee_identification' => ['required', 'string'],
            'purpose_of_stay' => ['nullable', 'string']
        ]);

        $hostOffice = Office::where('has_hostel', true)->findOrFail($validated['host_office_id']);


        // Validate guest count
        if ($validated['total_guests'] !== ($validated['total_males'] + $validated['total_females'])) {
            return redirect()->back()->with('error', 'Total guests must match the sum of males and females.');
        }

        try {
            DB::transaction(function () use (&$validated) {
                // Create reservation
                $reservation = $this->createReservation($validated);

                // Assign beds and group by room
                $validated['reserved_rooms'] = $this->assignAndGroupBeds(
                    $validated['total_females'],
                    $validated['total_males'],
                    $reservation
                );

                // Add additional data to be pass to confirmation page
                $validated['reservation_code'] = $reservation->reservation_code;
                $validated['total_billing'] = $reservation->total_billing;
                $validated['book_by'] = $validated['first_name'] . ' ' . $validated['last_name'];
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return to_route('reservation.confirmation', [
            'reservation_code'  => $validated['reservation_code'],
            'booked_by'         => $validated['book_by'],
            'phone'             => $validated['phone'],
            'check_in_date'     => $validated['check_in_date'],
            'check_out_date'    => $validated['check_out_date'],
            'reserved_rooms'    => $validated['reserved_rooms'],
            'total_billing'     => $validated['total_billing'],
            'host_office_name'  => $hostOffice->name,
        ])->with('success', 'Reservation successfully created!');
    }



    private function createReservation(array $validated): Reservation
    {
        return Reservation::create([
            'reservation_code' => $this->generateReservationCode(),
            'check_in_date' => $validated['check_in_date'],
            'check_out_date' => $validated['check_out_date'],
            'total_billing' => 0,
            'remaining_balance' => 0,
            'status' => 'pending',
            'first_name' => $validated['first_name'],
            'middle_initial' => $validated['middle_initial'] ?? null,
            'last_name' => $validated['last_name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'guest_office_id' => $validated['guest_office_id'],
            'host_office_id' => $validated['host_office_id'],
            'employee_identification' => $validated['employee_identification'],
            'purpose_of_stay' => $validated['purpose_of_stay'] ?? null,
        ]);
    }

    private function assignAndGroupBeds(float $totalFemales, float $totalMales, Reservation $reservation): string
    {
        $femaleBedsReserved = $this->assignBeds(
            $totalFemales,
            'female',
            $reservation
        );

        $maleBedsReserved = $this->assignBeds(
            $totalMales,
            'male',
            $reservation
        );

        $bedsReserved = collect(array_merge($femaleBedsReserved, $maleBedsReserved))
            ->groupBy('room.id')
            ->map(function ($beds) {
                $room = $beds->first()->room->toArray();
                $room['total_beds'] = $beds->count();
                $room['total_price'] = $beds->sum('price');
                return $room;
            })
            ->values();

        return json_encode($bedsReserved);
    }


    // Assign each guest to a bed based on their gender.
    private function assignBeds(int $count, string $gender, Reservation $reservation): array
    {
        $bedsReserved = [];
        $checkInDate = $reservation->check_in_date;
        $checkOutDate = $reservation->check_out_date;
        $lengthOfStay = Carbon::parse($checkInDate)->diffInDays(Carbon::parse($checkOutDate)) + 1;
        $totalPrice = 0;

        if ($count === 0) {
            return [];
        }

        // Get beds that are not reserved during the requested dates
        $reservedBedIds = ReservationAssignments::whereHas('reservation', function ($query) use ($checkInDate, $checkOutDate) {
            $query->where('check_out_date', '>', $checkInDate)
                ->where('check_in_date', '<', $checkOutDate)
                ->whereNotIn('status', ['canceled', 'checked_out']);
        })->pluck('bed_id');

        $beds = Bed::whereNotIn('id', $reservedBedIds)
            ->whereHas('room', function ($query) use ($gender) {
                $query->whereIn('eligible_gender', [$gender, 'any']);
            })
            ->with('room')
            ->take($count)
            ->get();

        if ($beds->count() < $count) {
            throw new \Exception("Not enough available beds for {$gender} guests.");
        }

        // Calculate total price and assign each guest to a bed
        foreach ($beds as $bed) {
            $room = $bed->room;
            $totalPrice += $bed->price;

            /**
             * If the room currently accepts any gender, update its eligible gender.
             * Because of the hostel rule that a room can only be occupied by one gender and
             * whoever gender who reserved or occupied the room first then the room will be
             * only occupied by that gender.
             */
            if ($room->eligible_gender === 'any') {
                $room->update(['eligible_gender' => $gender]);
            }

            // Create the guest record.
            $guest = Guest::create([
                'display_name' => "$reservation->first_name companion",
                'gender' => $gender,
                'office_id' => $reservation->guest_office_id,
            ]);

            // Create a reservation assignment linking reservation, bed, and guest.
            ReservationAssignments::create([
                'reservation_id' => $reservation->id,
                'bed_id' => $bed->id,
                'guest_id' => $guest->id,
            ]);

            $bedsReserved[] = $bed;
        }

        // Update reservation billing
        $total_billing_amount = $totalPrice * $lengthOfStay;
        $reservation->total_billing = $total_billing_amount;
        $reservation->remaining_balance = $total_billing_amount;
        $reservation->save();

        return $bedsReserved;
    }


    private function generateReservationCode(): string
    {
        $date = now()->format('Ymd');
        $maxAttempts = 100;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            $randomNumber = random_int(1000, 9999);
            $code = "RES-{$date}-{$randomNumber}";

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

        $sequence = $latestReservation ? (int)explode('-', $latestReservation->reservation_code)[2] + 1 : 1;
        return "RES-{$date}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }


    //Slip page that generates reservation confirmation image
    public function confirmation(Request $request)
    {
        return Inertia::render('ReservationProcess/ReservationConfirmation', [
            'canLogin'          => Route::has('login'),
            'reservation_code'  => $request->reservation_code,
            'booked_by'         => $request->booked_by,
            'phone'             => $request->phone,
            'check_in_date'     => $request->check_in_date,
            'check_out_date'    => $request->check_out_date,
            'reserved_rooms'    => json_decode($request->reserved_rooms),
            'total_billing'     => $request->total_billing,
            'host_office_name'  => $request->host_office_name,
        ]);
    }
}
