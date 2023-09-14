<?php

namespace App\Models\Teacher;

use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Teacher extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'mobile',
        'password',
        'token',
        'token_expiry',
        'school_id',
        'school_location_id',
        'school_teacher_id',
        'profile_photo_path',
        'profile_photo_url'
    ];

    protected $hidden = [
        'password',
        'token',
        'token_expiry',
        'profile_photo_path'
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
