<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolParent extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
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
        'photo_id'
    ];

    public function photo(){
        return $this->belongsTo(FileManager::class, 'photo_id', 'id');
    }
}
