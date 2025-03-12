<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    /** @use HasFactory<\Database\Factories\ReservationFactory> */
    use HasFactory;

    protected $fillable = [
        'reservation_code',
        'check_in_date',
        'check_out_date',
        'total_billing',
        'remaining_balance',
        'status',
        'first_name',
        'middle_initial',
        'last_name',
        'phone',
        'email',
        'guest_office_id',
        'host_office_id',
        'employee_identification',
        'purpose_of_stay',
    ];

    public function guests()
    {
        return $this->belongsToMany(Guest::class, 'reservation_assignments');
    }

    public function guestOffice()
    {
        return $this->belongsTo(Office::class, 'guest_office_id');
    }

    public function hostOffice()
    {
        return $this->belongsTo(Office::class, 'host_office_id');
    }

    public function beds()
    {
        return $this->belongsToMany(Bed::class, 'reservation_assignments');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
