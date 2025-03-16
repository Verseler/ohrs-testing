<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    /** @use HasFactory<\Database\Factories\RoomFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'eligible_gender',
        'office_id'
    ];

    public function beds()
    {
        return $this->hasMany(Bed::class);
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function EligibleGenderSchedules()
    {
        return $this->hasMany(EligibleGenderSchedule::class);
    }

    public function availableBeds($checkInDate, $checkOutDate)
    {
        return $this->beds()->whereDoesntHave('reservationAssignments.reservation', function ($query) use ($checkInDate, $checkOutDate) {
            $query->where('check_in_date', '<', $checkOutDate)
                  ->where('check_out_date', '>', $checkInDate);
        });
    }
}
