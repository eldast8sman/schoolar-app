<?php

namespace App\Models\Teacher;

use App\Models\FileManager;
use App\Traits\HasUuid;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Teacher extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, HasUuid;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'email_verified',
        'mobile',
        'password',
        'otp',
        'otp_expiry',
        'token',
        'token_expiry',
        'school_id',
        'school_location_id',
        'school_teacher_id',
        'photo_id'
    ];

    protected $hidden = [
        'password',
        'token',
        'token_expiry',
        'otp',
        'otp_expiry'
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function photo(){
        return $this->belongsTo(FileManager::class, 'photo_id', 'id');
    }
}
