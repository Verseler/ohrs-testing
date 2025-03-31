<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentExemption extends Model
{
    protected $fillable = [
        'reservation_id',
        'price',
        'guest_id',
        'user_id',
        'reason'
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
