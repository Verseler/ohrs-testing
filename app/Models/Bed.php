<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bed extends Model
{
    /** @use HasFactory<\Database\Factories\BedFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'room_id'
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function reservationAssignments()
    {
        return $this->hasMany(ReservationAssignments::class);
    }

    public function guest()
    {
        return $this->hasOne(Guest::class);
    }
}
