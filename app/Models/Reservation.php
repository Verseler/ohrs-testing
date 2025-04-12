<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    /** @use HasFactory<\Database\Factories\ReservationFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'total_billings',
        'remaining_balance',
        'payment_type',
        'first_name',
        'middle_initial',
        'last_name',
        'phone',
        'email',
        'hostel_office_id',
        'id_type',
        'employee_id',
        'purpose_of_stay',
        'general_status',
        'modify_token',
        'modify_token_expires_at'
    ];

    public function guests()
    {
        return $this->hasMany(Guest::class);
    }

    public function hostelOffice()
    {
        return $this->belongsTo(Office::class, 'hostel_office_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function paymentExemptions()
    {
        return $this->hasMany(PaymentExemption::class);
    }

    public function stayDetails()
    {
        return $this->hasMany(StayDetails::class);
    }


   public function reservedBeds()
   {
       return $this->hasManyThrough(Bed::class, StayDetails::class, 'reservation_id', 'id', 'id', 'bed_id');
   }

   public function reservedBedsWithGuests()
   {
       return $this->stayDetails()->with(['bed.room.eligibleGenderSchedules', 'guest']);
   }

   public function recomputeBillings()
   {
        $newTotalBillings = $this->guests->sum('stayDetails.individual_billings');
        $this->total_billings = $newTotalBillings;

        $totalPayed = $this->payments->sum('amount');
        $newRemainingBalance = max(0, $newTotalBillings - $totalPayed);
        $this->remaining_balance = $newRemainingBalance;
        $this->save();
   }

    public function getStayDateRange()
    {
        $stayDetails = $this->stayDetails;

        if ($stayDetails->isEmpty()) {
            return [
                'min_check_in_date' => null,
                'max_check_out_date' => null
            ];
        }

        $minCheckInDate = $stayDetails->min('check_in_date');
        $maxCheckOutDate = $stayDetails->max('check_out_date');

        return [
            'min_check_in_date' => $minCheckInDate,
            'max_check_out_date' => $maxCheckOutDate
        ];
    }
}
