<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Http\Resources\SchoolResource;
use App\Traits\HasUuid;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, HasUuid;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'mobile',
        'password',
        'otp',
        'otp_expiry',
        'email_verified',
        'onboarding_status',
        'school_id',
        'school_location_id',
        'token',
        'token_expiry'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'otp',
        'otp_expiry',
        'token',
        'token_expiry'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function user_schools(){
        return $this->hasMany(UserSchool::class);
    }

    public function user_details(){
        $details = [];
        foreach($this->user_schools()->get() as $user_school){
            $details[] = new SchoolResource($user_school->school);
        }
        return $details;
    }

    public function school(){
        return $this->belongsTo(School::class);
    }

    public function school_location(){
        return $this->belongsTo(SchoolLocation::class);
    }
}
