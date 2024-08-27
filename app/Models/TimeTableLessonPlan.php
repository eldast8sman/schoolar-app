<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeTableLessonPlan extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'school_id',
        'school_location_id',
        'time_table_configuration_id',
        'lesson_start_time',
        'lesson_end_time',
        'lesson_days',
    ];
}

