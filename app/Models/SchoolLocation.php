<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'location_type',
        'syllabus',
        'address',
        'town',
        'lga',
        'state',
        'country'
    ];
}
