<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\School;
use App\Models\Subject;
use App\Models\SubClass;
use App\Models\MainClass;
use Illuminate\Http\Request;
use App\Models\SchoolLocation;
use App\Http\Requests\AddLocationToSchoolRequest;

class SchoolController extends Controller
{
    private $user;

    public function __construct()
    {
        $this->middleware('auth:user-api');
        $this->user = AuthController::user();
    }

    public function add_locations(AddLocationToSchoolRequest $request){
        $school = School::find($this->user->school_id);
        if($school->type == "group"){
            $locations = $request->locations;

            $count = 0;
            $success = 0;
            $failed = 0;
            $data = [];
            foreach($locations as $location){
                $count += 1;
                if($added = SchoolLocation::create([
                    'school_id' => $this->user->school_id,
                    'address' => $location['address'],
                    'town' =>$location['town'],
                    'lga' => !empty($location['lga']) ? $location['lga'] : "",
                    'state' => $location['state'],
                    'country' => !empty($location['country']) ? $location['country'] : "Nigeria",
                    'syllabus' => !empty($location['syllabus']) ? $location['syllabus'] : "",
                    'location_type' => $location['location_type']
                ])){
                    if($location['load_default'] == true){
                        if(strtolower($location['country']) == 'nigeria'){
                            $subjects = FunctionController::default_subjects();
                            if($added->location_type == "primary"){
                                for($i=1; $i<=6; $i++){
                                    $class = MainClass::create([
                                        'school_id' => $school->id,
                                        'school_location_id' => $added->id,
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
                                            'school_location_id' => $added->id,
                                            'main_class_id' => $class->id,
                                            'sub_class_id' => $subclass->id,
                                            'name' => $subject['subject'],
                                            'compulsory' => $subject['compulsory']
                                        ]);
                                    }
                                }
                            } elseif($added->location_type == "secondary"){
                                for($i=1; $i<=3; $i++){
                                    $class = MainClass::create([
                                        'school_id' => $school->id,
                                        'school_location_id' => $added->id,
                                        'class_level' => $i,
                                        'name' => 'JSS '.$i
                                    ]);
    
                                    $subclass = SubClass::create([
                                        'school_id' => $school->id,
                                        'school_location_id' => $added->id,
                                        'main_class_id' => $class->id,
                                        'name' => 'A'
                                    ]);
    
                                    foreach($subjects['junior_secondary'] as $subject){
                                        Subject::create([
                                            'school_id' => $school->id,
                                            'school_location_id' => $added->id,
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
                                        'school_location_id' => $added->id,
                                        'class_level' => $i + 3,
                                        'name' => 'SSS '.$i
                                    ]);
    
                                    $sciences = SubClass::create([
                                        'school_id' => $school->id,
                                        'school_location_id' => $added->id,
                                        'main_class_id' => $class->id,
                                        'name' => 'A'
                                    ]);
                                    foreach($subjects['senior_secondary']['sciences'] as $subject){
                                        Subject::create([
                                            'school_id' => $school->id,
                                            'school_location_id' => $added->id,
                                            'main_class_id' => $class->id,
                                            'sub_class_id' => $sciences->id,
                                            'name' => $subject['subject'],
                                            'compulsory' => $subject['compulsory']
                                        ]);
                                    }
    
                                    $arts = SubClass::create([
                                        'school_id' => $school->id,
                                        'school_location_id' => $added->id,
                                        'main_class_id' => $class->id,
                                        'name' => 'B'
                                    ]);
                                    foreach($subjects['senior_secondary']['arts'] as $subject){
                                        Subject::create([
                                            'school_id' => $school->id,
                                            'school_location_id' => $added->id,
                                            'main_class_id' => $class->id,
                                            'sub_class_id' => $arts->id,
                                            'name' => $subject['subject'],
                                            'compulsory' => $subject['compulsory']
                                        ]);
                                    }
    
                                    $commerce = SubClass::create([
                                        'school_id' => $school->id,
                                        'school_location_id' => $added->id,
                                        'main_class_id' => $class->id,
                                        'name' => 'C'
                                    ]);
                                    foreach($subjects['senior_secondary']['commerce'] as $subject){
                                        Subject::create([
                                            'school_id' => $school->id,
                                            'school_location_id' => $added->id,
                                            'main_class_id' => $class->id,
                                            'sub_class_id' => $commerce->id,
                                            'name' => $subject['subject'],
                                            'compulsory' => $subject['compulsory']
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                    $success += 1;
                    $data[] = $added;
                } else {
                    $failed += 1;
                }
            }

            if($success > 0){
                if($this->user->onboarding_status == 2){
                    $user = User::find($this->user->id);
                    $user->onboarding_status = 3;
                    $user->save();
                }
                return response([
                    'status' => 'success',
                    'message' => 'Locations Added to School',
                    'data' => [
                        'attempted' => $count,
                        'success' => $success,
                        'failed' => $failed,
                        'locations' => $data
                    ]
                ], 200);
            } else {
                return response([
                    'status' => 'failed',
                    'message' => 'Errors encountered in adding locations'
                ], 500);
            }
        } else {
            return response([
                'status' => 'failed',
                'message' => 'You can only add more schools to a group of Schools'
            ], 409);
        }
    }

    public function switch_location(SchoolLocation $location){
        $school = School::find($this->user->school_id);
        if($school->type == 'group'){
            if($location->school_id == $this->user->school_id){
                $user = User::find($this->user->id);
                $user->school_location_id = $location->id;
                $user->save();

                $user->schools = AuthController::user_details($user->id);
                return response([
                    'status' => 'success',
                    'message' => 'Location switched successfully',
                    'data' => $user
                ], 200);
            } else {
                return response([
                    'status' => 'failed',
                    'message' => 'No Location was fetched'
                ], 404);
            }
        } else {
            return response([
                'status' => 'failed',
                'message' => 'This feature is only available for a Group of Schools'
            ], 409);
        }
    }
}