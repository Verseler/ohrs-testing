<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StayDetails extends Model
{
    protected $fillable = [
        'check_in_date',
        'check_out_date',
        'daily_rate',
        'is_exempted',
        'status',
        'bed_id',
        'reservation_id',
        'guest_id',
    ];

    public function bed()
    {
        return $this->belongsTo(Bed::class);
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

}
