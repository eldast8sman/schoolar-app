<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'school_location_id',
        'main_class_id',
        'sub_class_id',
        'name',
        'compulsory',
        'primary_teacher',
        'support_teacher'
    ];
}
