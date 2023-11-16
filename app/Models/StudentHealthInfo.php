<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentHealthInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_student_id',
        'weight',
        'weight_measurement',
        'height',
        'height_measurement',
        'blood_group',
        'genotype',
        'immunizations',
        'disabled',
        'disability'
    ];
}
