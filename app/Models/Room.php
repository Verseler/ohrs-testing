<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    /** @use HasFactory<\Database\Factories\RoomFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'eligible_gender',
        'bed_price_rate',
        'office_id'
    ];

    public function beds()
    {
        return $this->hasMany(Bed::class);
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }
}
