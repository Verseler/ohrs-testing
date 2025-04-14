<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RebookReservation extends Model
{
    protected $fillable = [
        'prev_reservation_id',
        'new_reservation_id'
    ];

    public function prevReservation()
    {
        return $this->belongsTo(Reservation::class, 'prev_reservation_id');
    }

    public function newReservation()
    {
        return $this->belongsTo(Reservation::class, 'new_reservation_id');
    }
}
