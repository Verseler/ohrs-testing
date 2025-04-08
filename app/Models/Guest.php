<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
    /** @use HasFactory<\Database\Factories\GuestFactory> */
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'gender',
        'office',
        'reservation_id'
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function paymentExemption()
    {
        return $this->hasOne(PaymentExemption::class);
    }

    public function stayDetails()
    {
        return $this->hasMany(StayDetails::class);
    }

    //TODO: remove
    public function guestBeds()
    {
        return $this->hasMany(GuestBeds::class);
    }
}
