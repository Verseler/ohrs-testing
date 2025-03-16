<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EligibleGenderSchedule extends Model
{
    protected $table = "eligible_gender_schedules";

    protected $fillable = [
        "start_date",
        "end_date",
        "eligible_gender",
        "room_id"
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
