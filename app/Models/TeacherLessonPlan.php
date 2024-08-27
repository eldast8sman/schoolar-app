<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherLessonPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'school_location_id',
        'main_class_id',
        'sub_class_id',
        'session_id',
        'term_id',
        'subject_id',
        'teacher_id',
        'document_id',
        'approval_status'
    ];

    public function document(){
        return $this->belongsTo(FileManager::class, 'document_id', 'id');
    }
}
