<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectAttendanceGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'school_location_id',
        'main_class_id',
        'sub_class_id',
        'session_id',
        'term_id',
        'subject_id',
        'attendance_date',
    ];

    public function registers(){
        return $this->hasMany(SubjectAttendanceRegister::class);
    }
}
