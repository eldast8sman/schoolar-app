<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherCertification extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'school_location_id',
        'school_teacher_id',
        'certification',
        'disk',
        'file_path',
        'file_url',
        'file_size'
    ];
}
