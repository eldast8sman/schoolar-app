<?php

namespace App\Models\Teacher;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherSchoolTeacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'school_teacher_id',
        'status'
    ];
}
