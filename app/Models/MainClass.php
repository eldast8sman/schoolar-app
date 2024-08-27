<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MainClass extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'school_id',
        'school_location_id',
        'class_level',
        'name',
        'teacher_id'
    ];

    public function update_dependencies(){
        $students = SchoolStudent::where('main_class_id', $this->id);
        if($students->count() > 0){
            foreach($students->get() as $student){
                $student->class_level = $this->class_level;
                $student->save();
            }
        }
    }

    public function sub_classes(){
        return $this->hasMany(SubClass::class);
    }

    public function teacher(){
        return $this->belongsTo(SchoolTeacher::class, 'teacher_id', 'id');
    }
}
