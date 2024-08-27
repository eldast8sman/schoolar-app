<?php

namespace App\Models\Teacher;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherSchoolTeacher extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'teacher_id',
        'school_teacher_id',
        'school_id',
        'school_location_id',
        'status'
    ];
}
