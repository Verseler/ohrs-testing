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
        'price',
        'status',
        'room_id'
    ];

    public function room()
    {
        $this->belongsTo(Room::class);
    }
}
