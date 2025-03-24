<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuestBeds extends Model
{
    protected $fillable = ['bed_id', 'guest_id', 'reservation_id'];

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function bed()
    {
        return $this->belongsTo(Bed::class);
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function bedsWithBalance()
    {
        return  $this->whereHas('reservation', function ($query) {
            $query->where('payment_type', 'full_payment')
                ->where('remaining_balance', '>', 0);
        });
    }

    public function reservedBeds($checkInDate, $checkOutDate)
    {
        return $this->whereHas('guest', function ($query) use ($checkInDate, $checkOutDate) {
            $query->whereHas('reservation', function ($query) use ($checkInDate, $checkOutDate) {
                $query->where('check_in_date', '<=', $checkInDate)
                    ->where('check_out_date', '>=', $checkOutDate)
                    ->whereNotIn('status', ['canceled', 'checked_out']);
            });
        });
    }

    public function availableBeds($checkInDate, $checkOutDate)
    {
        return $this->whereDoesntHave('guest', function ($query) use ($checkInDate, $checkOutDate) {
            $query->whereHas('reservation', function ($query) use ($checkInDate, $checkOutDate) {
                $query->where('check_in_date', '<=', $checkOutDate)
                    ->where('check_out_date', '>=', $checkInDate)
                    ->whereNotIn('status', ['canceled', 'checked_out']);
            });
        });
    }
}
