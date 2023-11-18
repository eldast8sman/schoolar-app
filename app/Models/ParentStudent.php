<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParentStudent extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'school_location_id',
        'school_student_id',
        'school_parent_id',
        'primary',
        'relationship'
    ];
}
