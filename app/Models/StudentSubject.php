<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentSubject extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'school_location_id',
        'school_session_id',
        'main_class_id',
        'sub_class_id',
        'school_student_id',
        'subject_id'
    ];
}
