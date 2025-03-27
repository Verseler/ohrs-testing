<?php

namespace App\Models;

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

    public function guestBeds()
    {
        return $this->hasMany(GuestBeds::class);
    }

    public function availableBeds($checkInDate, $checkOutDate, $hostelOfficeId)
    {
        // Get all reserved beds in a given period of time (check-in and check-out date) and beds with remaining balance
        $guestBeds = new GuestBeds();
        $reservedBedIds = $guestBeds->reservedBeds($checkInDate, $checkOutDate)
            ->pluck('bed_id')->toArray();

        //NOTE: one of the rules of hostel is that if the bed is not yet paid it will be locked or
        // not yet available until it get paid or if the payment_type is pay later.
        $bedsWithBalance = $guestBeds->bedsWithBalance()->pluck('bed_id')->toArray();

        $excludedBedIds = array_unique(array_merge($reservedBedIds, $bedsWithBalance));

        // Get all available beds by checking if beds are not reserved or have balance
        $availableBeds = $this->whereNotIn('id', $excludedBedIds)
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

    public function isAvailable($reservation)
    {
        return $this->where('id', $this->id)
            ->whereDoesntHave('guestBeds.reservation', function ($query) use ($reservation) {
                $query->whereIn('status', ['confirmed', 'checked_in'])
                    ->where(function ($q) use ($reservation) {
                        $q->whereBetween('check_in_date', [$reservation->check_in_date, $reservation->check_out_date])
                            ->orWhereBetween('check_out_date', [$reservation->check_in_date, $reservation->check_out_date])
                            ->orWhere(function ($subQuery) use ($reservation) {
                                $subQuery->where('check_in_date', '<=', $reservation->check_in_date)
                                    ->where('check_out_date', '>=', $reservation->check_out_date);
                            });
                    });
            })
            ->exists();
    }

}
