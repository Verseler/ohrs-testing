<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuestBeds extends Model
{
    protected $fillable = ['bed_id', 'guest_id'];

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function bed()
    {
        return $this->belongsTo(Bed::class);
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
}
