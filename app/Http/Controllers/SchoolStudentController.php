<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\SubClass;
use App\Models\MainClass;
use Illuminate\Support\Str;
use App\Mail\AddStudentMail;
use Illuminate\Http\Request;
use App\Models\SchoolStudent;
use App\Models\Student\Student;
use App\Models\StudentHealthInfo;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\StoreSchoolStudentRequest;

class SchoolStudentController extends Controller
{
    private $user;
    private $disk = 'public';

    public function __construct()
    {
        $this->middleware('auth:user-api');
        $this->user = AuthController::user();
    }

    public function student(SchoolStudent $student) : SchoolStudent
    {
        if(!empty($student->main_class_id)){
            $student->main_class = MainClass::find($student->main_class_id)->name;
        }
        if(!empty($student->sub_class_id)){
            $student->sub_class = SubClass::find($student->sub_class_id)->name;
        }

        $health_info = StudentHealthInfo::where('school_student_id', $student->id)->first();
        unset($health_info->student_id);
        $student->health_info = $health_info;

        return $student;
    }

    public function store(StoreSchoolStudentRequest $request){
        if(SchoolStudent::where('school_location_id', $this->user->school_location_id)->where('registration_id', $request->registration_id)
        ->where('status', '<>', 2)->count() > 0){
            return response([
                'status' => 'failed',
                'message' => 'Registration Number already exists'
            ], 409);
        }

        $subclass = SubClass::find($request->sub_class_id);
        if(($subclass->school_id != $this->user->school_id) or ($subclass->school_location_id != $this->user->school_location_id)){
            return response([
                'status' => 'failed',
                'message' => 'Wrong Class'
            ], 409);
        }
        $main_class = MainClass::find($subclass->main_class_id);
        if(isset($request->file) and !empty($request->file)){
            $school = School::find($this->user->school_id);
            if(empty($school)){
                return response([
                    'status' => 'failed',
                    'message' => 'No School was fetched'
                ], 409);
                exit;
            }

            $path = $school->slug.'/students';
            $disk = !empty($request->disk) ? $request->disk : $this->disk;

            if($upload = FunctionController::uploadFile($path, $request->file('file'), $disk)){
                $file_url = $upload['file_url'];
                $file_path = $upload['file_path'];
                $file_size = $upload['file_size'];
                $file_disk = $disk;
            } else {
                $file_url = "";
                $file_path = "";
                $file_disk = "";
                $file_size = 0;
            }
        } else {
            $file_url = "";
            $file_path = "";
            $file_disk = "";
            $file_size = 0;
        }

        $all = $request->except(['file']);
        $all['main_class_id'] = $main_class->id;
        $all['school_id'] = $this->user->school_id;
        $all['school_location_id'] = $this->user->school_location_id;
        $all['disk'] = $file_disk;
        $all['file_path'] = $file_path;
        $all['file_url'] = $file_url;
        $all['file_size'] = $file_size;
        $all['registration_stage'] = 1;
        $all['status'] = 1;

        for($i=1; $i<=20; $i++){
            $uuid = Str::uuid();
            if(SchoolStudent::where('school_location_id', $this->user->school_location_id)->where('uuid', $uuid)->count() < 1){
                $all['uuid'] = $uuid;
                break;
            } else {
                continue;
            }
        }

        if(!$student = SchoolStudent::create($all)){
            return response([
                'status' => 'failed',
                'message' => 'Student upload failed'
            ], 409);
        }
        StudentHealthInfo::create([
            'school_student_id' => $student->id
        ]);

        $token = Str::random(20).time();
        $expiry = date('Y-m-d H:i:s', time() + (60 * 60 * 24));
        Student::create([
            'first_name' => $student->first_name,
            'middle_name' => $student->middle_name,
            'last_name' => $student->last_name,
            'email' => $student->email,
            'mobile' => $student->mobile,
            'username' => $student->registration_id,
            'token' => $token,
            'token_expiry' => $expiry,
            'school_id' => $this->user->school_id,
            'school_location_id' => $this->user->school_location_id,
            'school_student_id' => $student->id
        ]);

        if(!empty($student->email)){
            if(!isset($school)){
                $school = School::find($this->user->school_id);
            }
            $student->name = $student->first_name.' '.$student->last_name;
            Mail::to($student)->send(new AddStudentMail($student->name, $token, $school->name));
            unset($student->name);
        }

        return response([
            'status' => 'success',
            'message' => 'Student added successfully',
            'data' => $this->student($student)
        ], 200);
    }

    public function store_health_info(){
        
    }
}
