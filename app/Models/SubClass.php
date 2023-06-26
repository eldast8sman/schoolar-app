<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'school_location_id',
        'main_class_id',
        'name',
        'teacher_id'
    ];
}
