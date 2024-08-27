<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherCertification extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'school_id',
        'school_location_id',
        'school_teacher_id',
        'certification',
        'file_id'
    ];

    public function file(){
        return $this->belongsTo(FileManager::class, 'file_id', 'id');
    }
}
