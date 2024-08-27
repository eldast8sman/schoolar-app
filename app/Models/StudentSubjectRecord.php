<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentSubjectRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'school_location_id',
        'school_session_id',
        'school_term_id',
        'school_student_id',
        'subject_id',
        'student_subject_id',
        'records'
    ];
}
