<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'school_id',
        'school_location_id',
        'session_name',
        'start_date',
        'end_date',
        'status'
    ];
}
