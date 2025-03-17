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
        "payment_method",
        "transaction_id",
        "or_number",
        "or_date",
        "reservation_id",
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
