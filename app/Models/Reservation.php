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
        'daily_rate',
        'total_billings',
        'remaining_balance',
        'status',
        'payment_type',
        'first_name',
        'middle_initial',
        'last_name',
        'phone',
        'email',
        'guest_office_id',
        'hostel_office_id',
        'employee_id',
        'purpose_of_stay',
    ];

    public function guests()
    {
        return $this->hasMany(Guest::class);
    }

    public function guestOffice()
    {
        return $this->belongsTo(Office::class, 'guest_office_id');
    }

    public function hostelOffice()
    {
        return $this->belongsTo(Office::class, 'hostel_office_id');
    }

    public function guestBeds()
    {
        return $this->hasMany(GuestBeds::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function reservedBeds()
    {
        return $this->hasManyThrough(Bed::class, GuestBeds::class, 'reservation_id', 'id', 'id', 'bed_id');
    }
}
