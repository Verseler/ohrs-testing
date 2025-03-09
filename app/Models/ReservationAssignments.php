<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationAssignments extends Model
{

    protected $fillable = [
        'reservation_id',
        'bed_id',
        'guest_id'
    ];

     public function reservation()
     {
         return $this->belongsTo(Reservation::class);
     }


     public function bed()
     {
         return $this->belongsTo(Bed::class);
     }

     public function guest()
     {
         return $this->belongsTo(Guest::class);
     }
}
