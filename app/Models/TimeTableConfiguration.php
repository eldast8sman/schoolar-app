<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeTableConfiguration extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'school_id',
        'school_location_id',
        'assembly_start_time',
        'assembly_end_time',
        'lecture_start_time',
        'lecture_end_time',
        'configure_break_time',
        'configure_lesson',
        'time_table_lesson_id',
    ];
}
