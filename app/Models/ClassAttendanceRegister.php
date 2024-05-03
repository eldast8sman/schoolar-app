<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassAttendanceRegister extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'school_id',
        'school_location_id',
        'main_class_id',
        'sub_class_id',
        'session_id',
        'term_id',
        'class_attendance_group_id',
        'student_id',
        'first_name',
        'last_name',
        'enrolment_id',
        'attendance_date',
        'attendance_status',
    ];
}
