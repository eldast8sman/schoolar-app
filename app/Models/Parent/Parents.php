<?php

namespace App\Models\Parent;

use App\Models\FileManager;
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
        'photo',
    ];

    protected $hidden = [
        'password',
        'token',
        'token_expiry'
    ];

    public function photo(){
        return $this->belongsTo(FileManager::class, 'photo', 'id');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
