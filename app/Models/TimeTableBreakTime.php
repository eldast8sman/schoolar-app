<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeTableBreakTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'school_location_id',
        'time_table_configuration_id',
        'break_name',
        'break_start_time',
        'break_end_time',
        'break_days',
    ];
}
