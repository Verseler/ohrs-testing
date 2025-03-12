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
        "payment_date",
        "or_number",
        "reservation_id",
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function orNumber()
    {
        return $this->belongsTo(ORNumber::class, 'or_number', 'or_number');
    }
}
