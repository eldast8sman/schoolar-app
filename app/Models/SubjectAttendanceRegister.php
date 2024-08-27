<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectAttendanceRegister extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'school_id',
        'school_location_id',
        'main_class_id',
        'sub_class_id',
        'session_id',
        'term_id',
        'subject_attendance_group_id',
        'subject_id',
        'student_id',
        'first_name',
        'last_name',
        'enrolment_id',
        'attendance_date',
        'attendance_status',
    ];

    public function group(){
        return $this->belongsTo(SubjectAttendanceGroup::class);
    }
}
