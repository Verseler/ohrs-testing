<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtendedReservation extends Model
{
    protected $fillable = [
        'check_in_date',
        'old_check_out_date',
        'new_check_out_date',
        'days_extended',
        'reservation_id'
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
