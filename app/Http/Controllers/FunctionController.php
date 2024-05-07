<?php

namespace App\Http\Controllers;

use App\Models\AssessmentType;
use App\Models\GradingSystem;
use App\Models\MainClass;
use App\Models\School;
use App\Models\SchoolLocation;
use App\Models\SubClass;
use App\Models\Subject;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FunctionController extends Controller
{
    public static function uploadFile($file_path, $file, $disk='public'){
        if($file instanceof UploadedFile){
            if($stored = Storage::disk($disk)->putFile($file_path, $file)){
                return [
                    'file_url' => Storage::disk($disk)->url($stored),
                    'file_path' => $stored,
                    'file_size' => Storage::disk($disk)->size($stored),
                    'file_disk' => $disk
                ];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public static function deleteFile($path, $disk='public'){
        if(Storage::disk($disk)->exists($path)){
            Storage::disk($disk)->delete($path);
        }
    }

    public static function default_subjects(){
        return [
            "primary" => array(
                array("subject" => "English Language", "compulsory" => true),
                array("subject" => "Mathematics", "compulsory" => true),
                array("subject" => "Basic Science", "compulsory" => true),
                array("subject" => "Social Studies", "compulsory" => true),
                array("subject" => "P.H.E. (Physical and Health Education)", "compulsory" => true),
                array("subject" => "C.R.S. (Christian Religious Studies)", "compulsory" => false),
                array("subject" => "I.R.S. (Islamic Religious Studies)", "compulsory" => false),
                array("subject" => "Yoruba Language", "compulsory" => false),
                array("subject" => "Igbo Language", "compulsory" => false),
                array("subject" => "Hausa Language", "compulsory" => false),
                array("subject" => "Basic Technology", "compulsory" => false),
                array("subject" => "Agricultural Science", "compulsory" => false),
                array("subject" => "Computer Studies", "compulsory" => false),
                array("subject" => "Civic Education", "compulsory" => false),
                array("subject" => "Creative and Cultural Arts", "compulsory" => false),
            ),
            "junior_secondary" => array(
                array("subject" => "English Language", "compulsory" => true),
                array("subject" => "Mathematics", "compulsory" => true),
                array("subject" => "Basic Science", "compulsory" => true),
                array("subject" => "Basic Technology", "compulsory" => true),
                array("subject" => "Physical and Health Education", "compulsory" => true),
                array("subject" => "Civic Education", "compulsory" => true),
                array("subject" => "Social Studies", "compulsory" => true),
                array("subject" => "Christian Religious Studies", "compulsory" => false),
                array("subject" => "Islamic Religious Studies", "compulsory" => false),
                array("subject" => "Business Studies", "compulsory" => false),
                array("subject" => "Agricultural Science", "compulsory" => false),
                array("subject" => "Home Economics", "compulsory" => false),
                array("subject" => "Computer Studies", "compulsory" => false),
                array("subject" => "Basic Electricity", "compulsory" => false),
                array("subject" => "French", "compulsory" => false),
                array("subject" => "Yoruba Language", "compulsory" => false),
                array("subject" => "Igbo Language", "compulsory" => false),
                array("subject" => "Hausa Language", "compulsory" => false),
                array("subject" => "Visual Arts", "compulsory" => false),
                array("subject" => "Music", "compulsory" => false),
                array("subject" => "Arabic", "compulsory" => false),
                array("subject" => "Technical Drawing", "compulsory" => false),
                array("subject" => "Home Economics", "compulsory" => false),
                array("subject" => "Basic Electricity", "compulsory" => false),
                array("subject" => "Building Construction", "compulsory" => false),
                array("subject" => "Computer Science", "compulsory" => false),
                array("subject" => "Physical and Health Education", "compulsory" => false),
                array("subject" => "Insurance", "compulsory" => false),
                array("subject" => "Data Processing", "compulsory" => false),
                array("subject" => "Further Mathematics", "compulsory" => false),
                array("subject" => "Food and Nutrition", "compulsory" => false),
                array("subject" => "Animal Husbandry", "compulsory" => false),
                array("subject" => "Photography", "compulsory" => false),
            ),
            "senior_secondary" => array(
                "sciences" => array(
                    array("subject" => "English Language", "compulsory" => true),
                    array("subject" => "Mathematics", "compulsory" => true),
                    array("subject" => "Physics", "compulsory" => true),
                    array("subject" => "Chemistry", "compulsory" => true),
                    array("subject" => "Biology", "compulsory" => true),
                    array("subject" => "Further Mathematics", "compulsory" => true),
                    array("subject" => "Geography", "compulsory" => true),
                    array("subject" => "Technical Drawing", "compulsory" => false),
                    array("subject" => "Agricultural Science", "compulsory" => false),
                    array("subject" => "Computer Science", "compulsory" => false),
                ),
                "arts" => array(
                    array("subject" => "English Language", "compulsory" => true),
                    array("subject" => "Mathematics", "compulsory" => true),
                    array("subject" => "Literature in English", "compulsory" => true),
                    array("subject" => "Government", "compulsory" => true),
                    array("subject" => "History", "compulsory" => true),
                    array("subject" => "Christian Religious Studies", "compulsory" => false),
                    array("subject" => "Islamic Religious Studies", "compulsory" => false),
                    array("subject" => "Yoruba Language", "compulsory" => false),
                    array("subject" => "Igbo Language", "compulsory" => false),
                    array("subject" => "Hausa Language", "compulsory" => false),
                    array("subject" => "Visual Arts", "compulsory" => false),
                    array("subject" => "Music", "compulsory" => false),
                    array("subject" => "French", "compulsory" => false),
                    array("subject" => "Arabic", "compulsory" => false),
                ),
                "commerce" => array(
                    array("subject" => "English Language", "compulsory" => true),
                    array("subject" => "Mathematics", "compulsory" => true),
                    array("subject" => "Economics", "compulsory" => true),
                    array("subject" => "Accounting", "compulsory" => true),
                    array("subject" => "Commerce", "compulsory" => true),
                    array("subject" => "Financial Accounting", "compulsory" => true),
                    array("subject" => "Government", "compulsory" => false),
                    array("subject" => "Civic Education", "compulsory" => false),
                ),
            )
        ];
    }

    public static function default_grading_systems(){
        return array(
            'primary' => array(
                array("grade" => "A", "minimum" => 70, "maximum" => 100, "remarks" => "Excellent"),
                array("grade" => "B", "minimum" => 60, "maximum" => 69, "remarks" => "Very Good"),
                array("grade" => "C", "minimum" => 50, "maximum" => 59, "remarks" => "Good"),
                array("grade" => "D", "minimum" => 45, "maximum" => 49, "remarks" => "Fair"),
                array("grade" => "E", "minimum" => 40, "maximum" => 44, "remarks" => "Poor"),
                array("grade" => "F", "minimum" => 0, "maximum" => 39, "remarks" => "Fail")
            ),
            'secondary' => array(
                array("grade" => "A1", "minimum" => 75, "maximum" => 100, "remarks" => "Excellent"),
                array("grade" => "B2", "minimum" => 70, "maximum" => 74, "remarks" => "Very Good"),
                array("grade" => "B3", "minimum" => 65, "maximum" => 69, "remarks" => "Good"),
                array("grade" => "C4", "minimum" => 60, "maximum" => 64, "remarks" => "Credit"),
                array("grade" => "C5", "minimum" => 55, "maximum" => 59, "remarks" => "Credit"),
                array("grade" => "C6", "minimum" => 50, "maximum" => 54, "remarks" => "Credit"),
                array("grade" => "D7", "minimum" => 45, "maximum" => 49, "remarks" => "Pass"),
                array("grade" => "E8", "minimum" => 40, "maximum" => 44, "remarks" => "Pass"),
                array("grade" => "F9", "minimum" => 0, "maximum" => 39, "remarks" => "Fail")
            )
        );
    }

    public static function load_default($location_id){
        $location = SchoolLocation::find($location_id);
        $school = School::find($location->school_id);

        if(strtolower($location->country) == 'nigeria'){
            $subjects = self::default_subjects();
            if($location->location_type == "primary"){
                for($i=1; $i<=6; $i++){
                    $class = MainClass::create([
                        'school_id' => $school->id,
                        'school_location_id' => $location->id,
                        'class_level' => $i,
                        'name' => 'Primary '.$i
                    ]);
                    $subclass = SubClass::create([
                        'school_id' => $class->school_id,
                        'school_location_id' => $class->school_location_id,
                        'main_class_id' => $class->id,
                        'name' => 'A'
                    ]);

                    foreach($subjects['primary'] as $subject){
                        Subject::create([
                            'school_id' => $school->id,
                            'school_location_id' => $location->id,
                            'main_class_id' => $class->id,
                            'sub_class_id' => $subclass->id,
                            'name' => $subject['subject'],
                            'compulsory' => $subject['compulsory']
                        ]);
                    }
                }
            } elseif($location->location_type == "secondary"){
                for($i=1; $i<=3; $i++){
                    $class = MainClass::create([
                        'school_id' => $school->id,
                        'school_location_id' => $location->id,
                        'class_level' => $i,
                        'name' => 'JSS '.$i
                    ]);

                    $subclass = SubClass::create([
                        'school_id' => $school->id,
                        'school_location_id' => $location->id,
                        'main_class_id' => $class->id,
                        'name' => 'A'
                    ]);

                    foreach($subjects['junior_secondary'] as $subject){
                        Subject::create([
                            'school_id' => $school->id,
                            'school_location_id' => $location->id,
                            'main_class_id' => $class->id,
                            'sub_class_id' => $subclass->id,
                            'name' => $subject['subject'],
                            'compulsory' => $subject['compulsory']
                        ]);
                    }
                }

                for($i=1; $i<=3; $i++){
                    $class = MainClass::create([
                        'school_id' => $school->id,
                        'school_location_id' => $location->id,
                        'class_level' => $i + 3,
                        'name' => 'SSS '.$i
                    ]);

                    $sciences = SubClass::create([
                        'school_id' => $school->id,
                        'school_location_id' => $location->id,
                        'main_class_id' => $class->id,
                        'name' => 'A',
                        'type' => 'sciences'
                    ]);
                    foreach($subjects['senior_secondary']['sciences'] as $subject){
                        Subject::create([
                            'school_id' => $school->id,
                            'school_location_id' => $location->id,
                            'main_class_id' => $class->id,
                            'sub_class_id' => $sciences->id,
                            'name' => $subject['subject'],
                            'compulsory' => $subject['compulsory']
                        ]);
                    }

                    $arts = SubClass::create([
                        'school_id' => $school->id,
                        'school_location_id' => $location->id,
                        'main_class_id' => $class->id,
                        'name' => 'B',
                        'type' => 'arts'
                    ]);
                    foreach($subjects['senior_secondary']['arts'] as $subject){
                        Subject::create([
                            'school_id' => $school->id,
                            'school_location_id' => $location->id,
                            'main_class_id' => $class->id,
                            'sub_class_id' => $arts->id,
                            'name' => $subject['subject'],
                            'compulsory' => $subject['compulsory']
                        ]);
                    }

                    $commerce = SubClass::create([
                        'school_id' => $school->id,
                        'school_location_id' => $location->id,
                        'main_class_id' => $class->id,
                        'name' => 'C',
                        'type' => 'commerce'
                    ]);
                    foreach($subjects['senior_secondary']['commerce'] as $subject){
                        Subject::create([
                            'school_id' => $school->id,
                            'school_location_id' => $location->id,
                            'main_class_id' => $class->id,
                            'sub_class_id' => $commerce->id,
                            'name' => $subject['subject'],
                            'compulsory' => $subject['compulsory']
                        ]);
                    }
                }
            }

            if(isset(FunctionController::default_grading_systems()[$location->location_type])){
                $grades = FunctionController::default_grading_systems()[$location->location_type];
                foreach($grades as $grade){
                    $grade['school_id'] = $school->id;
                    $grade['school_location_id'] = $location->id;
                    $grade['uuid'] = Str::uuid().'-'.time();
                    GradingSystem::create($grade);
                }
            }

            $assessments = self::default_assessment_types();
            if(!empty($assessment_type = AssessmentType::where('school_location_id', $location->id)->first())){
                $assessment_type->update([
                    'assessment_scores' => json_encode($assessments),
                    'minimum_pass_score' => 50
                ]);
            } else {
                AssessmentType::create([
                    'school_id' => $school->id,
                    'school_location_id' => $location->id,
                    'assessment_scores' => json_encode($assessments),
                    'minimum_pass_score' => 50
                ]);
            }
        }
    }

    public static function default_assessment_types(){
        return [
            [
                'assessment_type' => 'Exam',
                'percentage' => 60
            ],
            [
                'assessment_type' => 'Continuous Assessment',
                'percentage' => 30
            ],
            [
                'assessment_type' => 'Attendance',
                'percentage' => 10
            ]
        ];
    }
}
