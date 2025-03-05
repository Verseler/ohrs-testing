<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
    /** @use HasFactory<\Database\Factories\GuestFactory> */
    use HasFactory;

    protected $fillable = [
        'display_name',
        'gender',
        'office_id',
        'reservation_id',
        'bed_id'
    ];

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function bed()
    {
        return $this->belongsTo(Bed::class);
    }
}
