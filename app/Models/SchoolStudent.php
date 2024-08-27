<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolStudent extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'mobile',
        'email',
        'registration_id',
        'school_id',
        'school_location_id',
        'main_class_id',
        'class_level',
        'sub_class_id',
        'photo',
        'dob',
        'gender',
        'registration_stage',
        'status'
    ];

    public function photo(){
        return $this->belongsTo(FileManager::class, 'photo', 'id');
    }
}
