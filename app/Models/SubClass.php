<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubClass extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'school_id',
        'school_location_id',
        'main_class_id',
        'name',
        'type',
        'teacher_id'
    ];

    public function main_class(){
        return $this->belongsTo(MainClass::class);
    }

    public function teacher(){
        return $this->belongsTo(SchoolTeacher::class, 'teacher_id', 'id');
    }
}
