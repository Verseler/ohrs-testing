<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StayDetails extends Model
{
    protected $fillable = [
        'check_in_date',
        'check_out_date',
        'individual_billings',
        'is_exempted',
        'status',
        'bed_id',
        'reservation_id',
        'guest_id',
    ];

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function bed()
    {
        return $this->belongsTo(Bed::class);
    }

    public function reservedBeds($checkInDate, $checkOutDate)
    {
        return $this->where(function ($query) use ($checkInDate, $checkOutDate) {
            $query->where('check_in_date', '<=', $checkOutDate)
                ->where('check_out_date', '>=', $checkInDate)
                ->whereNotIn('status', ['canceled', 'checked_out'])
                ->whereNotNull('bed_id');
        });
    }
}
