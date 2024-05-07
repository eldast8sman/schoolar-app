<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'school_location_id',
        'assessment_scores',
        'minimum_pass_score'
    ];
}
