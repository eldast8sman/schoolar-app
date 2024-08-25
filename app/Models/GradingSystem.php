<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradingSystem extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'school_location_id',
        'minimum',
        'maximum',
        'grade',
        'remarks'
    ];
}
