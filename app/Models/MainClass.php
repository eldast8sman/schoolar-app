<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MainClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'school_location_id',
        'class_level',
        'name',
        'teacher_id'
    ];


}
