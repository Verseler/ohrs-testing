<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentFactory> */
    use HasFactory;

    protected $fillable = [
        "amount",
        "or_number",
        "or_date",
        "reservation_id",
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function totalPayed($reservationId)
    {
        return $this->where('reservation_id', $reservationId)->sum('amount');
    }
}
