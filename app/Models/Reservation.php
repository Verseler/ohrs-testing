<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    /** @use HasFactory<\Database\Factories\ReservationFactory> */
    use HasFactory;

    protected $fillable = [
        'check_in_date',
        'check_out_date',
        'total_amount',
        'current_balance',
        'status',
        'first_name',
        'middle_initial',
        'last_name',
        'phone',
        'email',
        'guest_office_id',
        'employee_identification',
        'purpose_of_stay',
    ];

    public function guests()
    {
        $this->hasMany(Guest::class);
    }
}
