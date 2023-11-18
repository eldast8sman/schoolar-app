<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolParent extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'school_id',
        'school_location_id',
        'title',
        'first_name',
        'last_name',
        'mobile',
        'email',
        'nationality',
        'occupation',
        'address',
        'town',
        'lga',
        'state',
        'country',
        'file_path',
        'file_url',
        'file_size',
        'file_disk'
    ];
}
