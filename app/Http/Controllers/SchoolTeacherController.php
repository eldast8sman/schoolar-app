<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\File;
use Illuminate\Support\Str;
use App\Mail\AddTeacherMail;
use Illuminate\Http\Request;
use App\Models\SchoolTeacher;
use App\Models\Teacher\Teacher;
use App\Models\TeacherCertification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use App\Models\Teacher\TeacherSchoolTeacher;
use App\Http\Requests\StoreSchoolTeacherRequest;
use App\Mail\SchoolTeacherRegistrationMail;

class SchoolTeacherController extends Controller
{
    private $user;
    private $disk = 'public';

    public function __construct()
    {
        $this->middleware('auth:user-api');
        $this->user = AuthController::user();
    }

    public function index(){
        $search = !empty($_GET['search']) ? (string)$_GET['search'] : "";
        $filter = isset($_GET['filter']) ? (int)$_GET['filter'] : NULL;
        $sort = !empty($_GET['sort']) ? (string)$_GET['sort'] : "asc";
        $limit = !empty($_GET['limit']) ? (string)$_GET['limit'] : "";

        $teachers = SchoolTeacher::where('school_location_id', $this->user->school_location_id);
        if(!empty($search)){
            $names = explode(' ', $search);
            foreach($names as $name){
                $name = trim($name);
                $teachers = $teachers->where(function($query) use ($name){
                    $query->where("first_name", "like", '%'.$name.'%')
                        ->orWhere("last_name", "like", '%'.$name.'%');
                });
            }
        }
        if($filter !== NULL){
            $teachers = $teachers->where('status', $filter);
        }
        if($filter != 2){
            $teachers = $teachers->where('status', '<>', 2);
        }
        $teachers = $teachers->orderBy('first_name', $sort)->orderBy('last_name', $sort);
        if($teachers->count() > 0){
            $teachers = $teachers->paginate($limit);
            foreach($teachers as $teacher){
                $teacher->certifications = TeacherCertification::where('school_teacher_id', $teacher->id)->get();
            }

            return response([
                'status' => 'success',
                'message' => 'Teachers successfully fetched',
                'data' => $teachers
            ], 200);
        } else {
            return response([
                'status' => 'failed',
                'message' => 'No Teacher was fetched',
                'data' => null
            ], 200);
        }
    }

    public function store(StoreSchoolTeacherRequest $request){
        $mobile = $request->mobile;
        $email = $request->email;
        if(SchoolTeacher::where('school_location_id', $this->user->school_location_id)->where(function($query) use ($mobile, $email){
            $query->where('mobile', $mobile)
                ->orWhere('email', $email);
        })->where('status', '<>', 2)->count() > 0){
            return response([
                'status' => 'failed',
                'message' => 'Email or Phone number has already been registered as for a School Teacher in this School Location'
            ], 409);
        }

        if(isset($request->file) && !empty($request->file)){
            $school = School::find($this->user->school_id);
            if(empty($school)){
                return response([
                    'status' => 'failed',
                    'message' => 'No School was fetched'
                ], 409);
                exit;
            }

            $path = $school->slug.'/teachers';

            if($upload = FunctionController::uploadFile($path, $request->file('file'), $this->disk)){
                $photo_url = $upload['file_url'];
                $photo_path = $upload['file_path'];
            } else {
                $photo_url = "";
                $photo_path = "";
            }
        } else {
            $photo_url = "";
            $photo_path = "";
        }

        $all = $request->except(['certifications', 'file']);
        $all['school_id'] = $this->user->school_id;
        $all['school_location_id'] = $this->user->school_location_id;
        $all['profile_photo_url'] = $photo_url;
        $all['profile_photo_path'] = $photo_path;
        $all['status'] = 1;

        if($school_teacher = SchoolTeacher::create($all)){
            $path = $school->slug.'/teachers/certifications';
            $certifications = [];
            foreach($request->certifications as $certification){
                if(isset($certification['file']) && !empty($certification['file'])){
                    if($upload = FunctionController::uploadFile($path, new File($certification['file']), 'public')){
                        $certified = TeacherCertification::create([
                            'school_id' => $this->user->school_id,
                            'school_location_id' => $this->user->school_location_id,
                            'school_teacher_id' => $school_teacher->id,
                            'certification' => $certification['certification'],
                            'disk' => $this->disk,
                            'file_path' => $upload['file_path'],
                            'file_url' => $upload['file_url'],
                            'file_size' => $upload['file_size'] 
                        ]);

                        $certifications[] = $certified;
                    }
                }
            }

            $teacher = Teacher::where('mobile', $school_teacher->mobile)->first();
            if(empty($teacher)){
                $token = Str::random(20);
                $expiry = date('Y-m-d H:i:s', time() + (60 * 60 * 24));
                $teacher = Teacher::create([
                    'first_name' => $school_teacher->first_name,
                    'last_name' => $school_teacher->last_name,
                    'email' => $school_teacher->email,
                    'mobile' => $school_teacher->mobile,
                    'token' => Crypt::encryptString($token),
                    'token_expiry' => $expiry,
                    'school_id' => $this->user->school_id,
                    'school_location_id' => $this->user->school_location_id,
                    'profile_photo_path' => $school_teacher->profile_photo_path,
                    'profile_photo_url' => $school_teacher->profile_photo_url
                ]);

                TeacherSchoolTeacher::create([
                    'teacher_id' => $teacher->id,
                    'school_teacher_id' => $school_teacher->id,
                    'status' => $school_teacher->status
                ]);

                $teacher->name = $teacher->first_name.' '.$teacher->last_name;
                Mail::to($teacher)->send(new AddTeacherMail($teacher->name, $teacher->id, $token));
                unset($teacher->name);
            }

            $school_teacher->name = $school_teacher->first_name.' '.$school_teacher->last_name;
            Mail::to($school_teacher)->send(new SchoolTeacherRegistrationMail($school_teacher->name, $school->name));
        } else {
            return response([
                'status' => 'failed',
                'message' => 'Could not create Teacher'
            ], 500);
        }
    }
}
