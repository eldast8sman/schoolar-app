<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionCriteria extends Model
{
    use HasFactory, HasUuid;
    
    protected $fillable = [
        'uuid',
        'school_id',
        'school_location_id',
        'main_class_id',
        'sub_class_id',
        'session_id',
        'must_pass_count',
        'must_pass_subjects'
    ];

}
