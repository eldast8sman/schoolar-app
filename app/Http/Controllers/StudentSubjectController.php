<?php

namespace App\Http\Controllers;

use App\Models\SchoolLocation;
use App\Models\SchoolSession;
use App\Models\SchoolStudent;
use App\Models\StudentSubject;
use App\Models\SubClass;
use App\Models\Subject;
use Illuminate\Http\Request;
use Mockery\Matcher\Subset;

class StudentSubjectController extends Controller
{
    public $errors;
    
    public function register_subject($student_id, $subject_id, $sub_class_id, $session_id, $location_id){
        $student = SchoolStudent::where('id', $student_id)->where('school_location_id', $location_id);
        $subject = Subject::where('id', $subject_id)->where('school_location_id', $location_id);
        $subclass = SubClass::where('id', $sub_class_id)->where('school_location_id', $location_id);
        $session = SchoolSession::where('id', $session_id)->where('school_location_id', $location_id);
        if(empty($student) or empty($subject) or empty($subclass) or empty($session)){
            $this->errors = "Empty Parametres";
            return false;
        }

        if(!empty(StudentSubject::where('student_id', $student_id)->where('school_session_id', $session_id)->where('sub_class_id', $sub_class_id)->where('subject_id', $subject_id)->where('school_location_id', $location_id))){
            $this->errors = "Subject already registered";
            return false;
        }

        $stud_sub = StudentSubject::create([
            'school_id' => $student->school_id,
            'school_location_id' => $location_id,
            'school_session_id' => $session->id,
            'main_class_id' => $subclass->main_class_id,
            'sub_class_id' => $subclass->id,
            'school_student_id' => $student->id,
            'subject_id' => $subject->id
        ]);

        return $stud_sub;
    }

    public function register_compulsory($student_id, $sub_class_id, $session_id){
        $subjects = Subject::where('sub_class_id', $sub_class_id)->where('compulsory', true);
        $location = SchoolLocation::find($sub_class_id->school_location_id);
        if($subjects->count() > 0){
            foreach($subjects->get() as $subject){
                $this->register_subject($student_id, $subject->id, $sub_class_id, $session_id, $location->id);
            }
        }

        return true;
    }
}