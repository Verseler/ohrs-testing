<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Reservation;
use App\Models\Bed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservationSubmissionController extends Controller
{
    public function create(Request $request)
    {
        $validated = $request->validate([
            // Reservation details
            'total_guests' => ['required', 'integer', 'min:1'],
            'total_males' => ['required', 'integer', 'min:0'],
            'total_females' => ['required', 'integer', 'min:0'],
            'check_in_date' => ['required', 'date', 'before_or_equal:check_out_date'],
            'check_out_date' => ['required', 'date', 'after_or_equal:check_in_date'],

            // Guest details
            'first_name' => ['required', 'string', 'max:255'],
            'middle_initial' => ['nullable', 'string'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['required', 'regex:/(9)[0-9]{9}/'],
            'guest_office_id' => ['required', 'numeric'],
            'employee_identification' => ['required', 'string'],
            'purpose_of_stay' => ['nullable', 'string']
        ]);

        try {
            DB::transaction(function () use ($validated) {
                // Create Reservation
                $reservation = Reservation::create([
                    'check_in_date' => $validated['check_in_date'],
                    'check_out_date' => $validated['check_out_date'],
                    'total_amount' => 0, //TODO: Will be calculated later
                    'current_balance' => 0, //TODO: Will be calculated later
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

                // --- Assign Guests and Beds ---
                $this->assignBeds($validated['total_males'], 'male', $reservation);
                $this->assignBeds($validated['total_females'], 'female', $reservation);

            });
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
        dd('success');
        return redirect()->back()->with('success', 'Reservation successfully created and beds assigned!');
    }

    private function assignBeds(int $count, string $gender, Reservation $reservation): void
    {
        if ($count === 0) return;

        $beds = Bed::where('status', 'available')
            ->whereHas('room', function ($query) use ($gender) {
                $query->whereIn('eligible_gender', ['any', $gender]);
            })
            ->take($count)
            ->get();

        if ($beds->count() < $count) {
            throw new \Exception("Not enough available beds for {$gender} guests.");
        }

        foreach ($beds as $bed) {
            $room = $bed->room;

            if ($room->eligible_gender === 'any') {
                $room->update(['eligible_gender' => $gender]);
            }

            Guest::create([
                'display_name' => "{$reservation->first_name} {$reservation->last_name}",
                'gender' => $gender,
                'reservation_id' => $reservation->id,
                'bed_id' => $bed->id,
                'office_id' => $reservation->guest_office_id,
            ]);

            $bed->update([
                'reservation_id' => $reservation->id,
                'status' => 'occupied',
            ]);
        }
    }
}
