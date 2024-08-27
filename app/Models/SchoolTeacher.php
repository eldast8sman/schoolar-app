<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolTeacher extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'school_id',
        'school_location_id',
        'first_name',
        'last_name',
        'mobile',
        'email',
        'gender',
        'address',
        'city',
        'state',
        'country',
        'trcn_registration_number',
        'profile_photo_url',
        'profile_photo_path',
        'file_disk',
        'status'
    ];
}
