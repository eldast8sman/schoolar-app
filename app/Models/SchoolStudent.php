<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolStudent extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'first_name',
        'middle_name',
        'last_name',
        'mobile',
        'email',
        'registration_id',
        'school_id',
        'school_location_id',
        'main_class_id',
        'sub_class_id',
        'disk',
        'file_path',
        'file_url',
        'file_size',
        'dob',
        'gender',
        'registration_stage',
        'status'
    ];

    protected $hidden = [
        'disk',
        'file_path'
    ];
}
