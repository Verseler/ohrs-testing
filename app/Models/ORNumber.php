<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

//ORNumber or Official Receipt Number

class ORNumber extends Model
{
    protected $table = 'or_numbers';

    protected $fillable = ["or_number", "sequence"];

    public function payment()
    {
        return $this->hasOne(Payment::class, 'or_number', 'or_number');
    }

    public function reservation()
    {
        return $this->hasOneThrough(
            Reservation::class,
            Payment::class,
            'or_number',
            'id',
            'or_number',
            'reservation_id'
        );
    }
}
