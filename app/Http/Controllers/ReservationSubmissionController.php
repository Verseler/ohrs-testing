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
            'employee_identification' => ['required', 'string'],
            'purpose_of_stay' => ['nullable', 'string']
        ]);

        // Validate guest count
        if ($validated['total_guests'] !== ($validated['total_males'] + $validated['total_females'])) {
            return redirect()->back()->with('error', 'Total guests must match the sum of males and females.');
        }

        try {
            DB::transaction(function () use (&$validated) {
                // Calculate total billing amount
                $totalAmount = $this->calculateTotalAmount( $validated);

                // Create reservation
                $reservation = $this->createReservation($validated, $totalAmount);

                // Add additional data to validated array for confirmation page
                $validated['reservation_code'] = $reservation->reservation_code;
                $validated['total_amount'] = $totalAmount;
                $validated['bed_price_rate'] ??= 0;
                $validated['book_by'] = $validated['first_name'] . ' ' . $validated['last_name'];

                // Assign beds and group by room
                $validated['reserved_rooms'] = $this->assignAndGroupBeds($validated, $reservation);

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
            'reserved_rooms'     => $validated['reserved_rooms'],
            'bed_price_rate'    => $validated['bed_price_rate'],
            'total_amount'      => $validated['total_amount'],
            'total_guests'      => $validated['total_guests'],
        ])->with('success', 'Reservation successfully created!');
    }

    private function generateReservationCode(): string
    {
        $date = now()->format('Ymd');
        $latestReservation = Reservation::where('reservation_code', 'like', "RES-{$date}-%")
            ->orderBy('reservation_code', 'desc')
            ->first();

        $sequence = 1;
        if ($latestReservation) {
            $parts = explode('-', $latestReservation->reservation_code);
            $sequence = isset($parts[2]) ? (int)$parts[2] + 1 : 1;
        }

        return "RES-{$date}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    private function calculateTotalAmount( array $validated): float
    {
        //! Temporary
        $bedPriceRate = 200;
        $checkInDate = Carbon::parse($validated['check_in_date']);
        $checkOutDate = Carbon::parse($validated['check_out_date']);
        $daysOfStay = $checkOutDate->diff($checkInDate)->days + 1;

        return $bedPriceRate * $validated['total_guests'] * $daysOfStay;
    }


    private function createReservation(array $validated, float $totalAmount): Reservation
    {
        return Reservation::create([
            'reservation_code' => $this->generateReservationCode(),
            'check_in_date' => $validated['check_in_date'],
            'check_out_date' => $validated['check_out_date'],
            'total_amount' => $totalAmount,
            'current_balance' => $totalAmount,
            'status' => 'pending',
            'first_name' => $validated['first_name'],
            'middle_initial' => $validated['middle_initial'] ?? null,
            'last_name' => $validated['last_name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'guest_office_id' => $validated['guest_office_id'],
            'employee_identification' => $validated['employee_identification'],
            'purpose_of_stay' => $validated['purpose_of_stay'] ?? null,
        ]);
    }

    private function assignAndGroupBeds(array $validated, Reservation $reservation): string
{
    $femaleBedsReserved = $this->assignBeds(
        $validated['total_females'],
        'female',
        $reservation
    );

    $maleBedsReserved = $this->assignBeds(
        $validated['total_males'],
        'male',
        $reservation
    );

    $bedsReserved = collect(array_merge($femaleBedsReserved, $maleBedsReserved))
        ->groupBy('room.id')
        ->map(function ($beds) {
            $room = $beds->first()->room->toArray();
            $room['total_beds'] = $beds->count();
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

        //assign each guest to a bed
        foreach ($beds as $bed) {
            $room = $bed->room;

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

        return $bedsReserved;
    }

    //Slip page that generates reservation confirmation image
    public function confirmation(Request $request)
    {
        return Inertia::render('ReservationProcess/ReservationConfirmation', [
            'canLogin' => Route::has('login'),
            'reservation_code' => $request->reservation_code,
            'booked_by' => $request->booked_by,
            'phone' => $request->phone,
            'check_in_date' => $request->check_in_date,
            'check_out_date' => $request->check_out_date,
            'reserved_rooms' => json_decode($request->reserved_rooms),
            'bed_price_rate' => $request->bed_price_rate,
            'total_amount' => $request->total_amount,
            'total_guests' => $request->total_guests,
        ]);
    }
}
