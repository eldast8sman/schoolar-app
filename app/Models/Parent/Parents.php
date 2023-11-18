<?php

namespace App\Models\Parent;

use App\Models\SchoolParent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Parents extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'mobile',
        'email',
        'password',
        'token',
        'token_expiry',
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

    protected $hidden = [
        'password',
        'token',
        'token_expiry',
        'file_path',
        'file_disk'
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
