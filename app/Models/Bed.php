<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bed extends Model
{
    /** @use HasFactory<\Database\Factories\BedFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'room_id',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function stayDetails()
    {
        return $this->hasMany(StayDetails::class);
    }


    public function calculateBilling($checkInDate, $checkOutDate)
    {
        $checkInDate = Carbon::parse($checkInDate);
        $checkOutDate = Carbon::parse($checkOutDate);
        $lengthOfStay = $checkInDate->diffInDays($checkOutDate);

        if($lengthOfStay == 0) {
            $lengthOfStay = 1;
        }

        return $this->price * $lengthOfStay;
    }


    public function isAvailable($checkInDate, $checkOutDate, $gender)
    {
        // Step 1: Check for any overlapping reservations for this bed
        $overlappingReservations = StayDetails::where('bed_id', $this->id)
            ->whereHas('reservation', function ($query) {
                $query->whereIn('status', ['confirmed', 'checked_in']);
            })
            ->where(function ($query) use ($checkInDate, $checkOutDate) {
                // Check for any date range overlap
                $query->whereBetween('check_in_date', [$checkInDate, $checkOutDate])
                    ->orWhereBetween('check_out_date', [$checkInDate, $checkOutDate])
                    ->orWhere(function ($query) use ($checkInDate, $checkOutDate) {
                        $query->where('check_in_date', '<=', $checkInDate)
                            ->where('check_out_date', '>=', $checkOutDate);
                    });
            })
            ->exists();

            // If there are overlapping reservations, the bed is not available
        if ($overlappingReservations) {
            return false;
        }

        // Step 2: Get the room for this bed
        $room = $this->room;

        // Step 3: Check gender compatibility
        // If room accepts any gender, check for gender schedules during this period
        if ($room->eligible_gender === 'any') {
            // Check if there's a gender schedule for this room during this period
            $genderSchedule = EligibleGenderSchedule::where('room_id', $room->id)
                ->where(function ($query) use ($checkInDate, $checkOutDate) {
                    $query->whereBetween('start_date', [$checkInDate, $checkOutDate])
                        ->orWhereBetween('end_date', [$checkInDate, $checkOutDate])
                        ->orWhere(function ($q) use ($checkInDate, $checkOutDate) {
                            $q->where('start_date', '<=', $checkInDate)
                                ->where('end_date', '>=', $checkOutDate);
                        });
                })
                ->first();

            // If there's a gender schedule, check if it matches the guest's gender
            if ($genderSchedule && $genderSchedule->eligible_gender !== $gender) {
                return false;
            }

            // If no gender schedule, any gender is fine
            return true;
        }

        // If room has a specific gender requirement, check if it matches
        return $room->eligible_gender === $gender;
    }

    public function isEligibleForGender($room, $gender, $checkInDate, $checkOutDate)
    {
        if ($room->eligible_gender === 'any') {
            $genderSchedule = EligibleGenderSchedule::where('room_id', $room->id)
                ->where(function ($query) use ($checkInDate, $checkOutDate) {
                    $query->whereBetween('start_date', [$checkInDate, $checkOutDate])
                        ->orWhereBetween('end_date', [$checkInDate, $checkOutDate])
                        ->orWhere(function ($q) use ($checkInDate, $checkOutDate) {
                            $q->where('start_date', '<=', $checkInDate)
                                ->where('end_date', '>=', $checkOutDate);
                        });
                })
                ->first();

            if ($genderSchedule && $genderSchedule->eligible_gender !== $gender) {
                return false;
            }

            return true;
        }

        return $room->eligible_gender === $gender;
    }

    public function availableBeds($checkInDate, $checkOutDate, $hostelOfficeId)
    {
        // Get all reserved beds in a given period of time (check-in and check-out date)
        $stayDetails = new StayDetails();
        $reservedBedIds = $stayDetails->reservedBeds($checkInDate, $checkOutDate)
            ->pluck('bed_id')
            ->toArray();

        // Get all available beds by checking if beds are not reserved or have balance
        $availableBeds = $this->whereNotIn('id', $reservedBedIds)
            ->whereHas('room', function ($query) use ($hostelOfficeId) {
                $query->where('office_id', $hostelOfficeId);
            })
            ->with([
                'room.eligibleGenderSchedules' => function ($query) use ($checkInDate, $checkOutDate) {
                    $query->where(function ($dateQuery) use ($checkInDate, $checkOutDate) {
                        $dateQuery->where('start_date', '<=', $checkOutDate)
                            ->where('end_date', '>=', $checkInDate);
                    });
                },
            ])
            ->orderBy('room_id', 'asc')->get();

        return $availableBeds;
    }

}
