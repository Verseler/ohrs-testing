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
}
