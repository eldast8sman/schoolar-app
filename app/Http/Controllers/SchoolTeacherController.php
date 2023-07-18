<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\SubClass;
use App\Models\MainClass;
use Illuminate\Http\File;
use Illuminate\Support\Str;
use App\Mail\AddTeacherMail;
use Illuminate\Http\Request;
use App\Models\SchoolTeacher;
use App\Models\Teacher\Teacher;
use App\Models\TeacherCertification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use App\Mail\SchoolTeacherRegistrationMail;
use App\Models\Teacher\TeacherSchoolTeacher;
use App\Http\Requests\StoreSchoolTeacherRequest;
use App\Http\Requests\StoreTeacherCertificationRequest;
use App\Http\Requests\UpdateTeacherCertificationRequest;

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
                $teacher->classes = MainClass::where('school_id', $teacher->school_id)->where('school_location_id', $teacher->school_location_id)->where('school_teacher_id', $teacher->id)->get();
                $teacher->sub_classes = SubClass::where('school_id', $teacher->school_id)->where('school_location_id', $teacher->school_location_id)->where('school_teacher_id', $teacher->id)->get();
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
                $file_disk = $this->disk;
            } else {
                $photo_url = "";
                $photo_path = "";
                $file_disk = "";
            }
        } else {
            $photo_url = "";
            $photo_path = "";
            $file_disk = "";
        }

        $all = $request->except(['certifications', 'file', 'form_class']);
        $all['school_id'] = $this->user->school_id;
        $all['school_location_id'] = $this->user->school_location_id;
        $all['profile_photo_url'] = $photo_url;
        $all['profile_photo_path'] = $photo_path;
        $all['file_disk'] = $file_disk;
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

            if(!empty($request->form_class)){
                $class_type = $request->form_class->class_type;
                $class_id = $request->form_class->class_id;

                if($class_type == 'main_class'){
                    $class = MainClass::find($class_id);
                } elseif($class_type == 'sub_class'){
                    $class = SubClass::find($class_id);
                }
                if(!empty($class)){
                    $class->school_teacher_id = $school_teacher->id;
                    $class->save();
                }
            }

            $school_teacher->certifications = $certifications;

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

                $teacher->name = $teacher->first_name.' '.$teacher->last_name;
                Mail::to($teacher)->send(new AddTeacherMail($teacher->name, $teacher->id, $token));
                unset($teacher->name);
            }

            TeacherSchoolTeacher::create([
                'teacher_id' => $teacher->id,
                'school_teacher_id' => $school_teacher->id,
                'status' => $school_teacher->status
            ]);

            $school_teacher->name = $school_teacher->first_name.' '.$school_teacher->last_name;
            Mail::to($school_teacher)->send(new SchoolTeacherRegistrationMail($school_teacher->name, $school->name));

            return response([
                'status' => 'success',
                'message' => $request->first_name.' has been added as a Teacher',
                'data' => $school_teacher
            ], 200);
        } else {
            return response([
                'status' => 'failed',
                'message' => 'Could not create Teacher'
            ], 500);
        }
    }

    public function show(SchoolTeacher $teacher){
        if($teacher->school_location_id == $this->user->school_location_id){
            $teacher->certifications = TeacherCertification::where('school_teacher_id', $teacher->id)->get();
            $teacher->classes = MainClass::where('school_id', $teacher->school_id)->where('school_location_id', $teacher->school_location_id)->where('school_teacher_id', $teacher->id)->get();
            $teacher->sub_classes = SubClass::where('school_id', $teacher->school_id)->where('school_location_id', $teacher->school_location_id)->where('school_teacher_id', $teacher->id)->get();

            return response([
                'status' => 'success',
                'message' => 'School Teacher fetched successfully',
                'data' => $teacher
            ], 200);
        } else {
            return response([
                'status' => 'failed',
                'message' => 'No School Teacher was fetched'
            ], 404);
        }
    }

    public function update(SchoolTeacher $teacher, StoreSchoolTeacherRequest $request){
        if($teacher->school_location_id == $this->user->school_location_id){
            $all = $request->except(['file']);
            if($teacher->update($all)){
                if(!empty($request->file)){
                    $old_path = $teacher->profile_photo_path;
                    $old_disk = $teacher->file_disk;

                    $school = School::find($teacher->school_id);
                    $path = $school->slug.'/teachers';
                    if($upload = FunctionController::uploadFile($path, $request->file('file'), $this->disk)){
                        $teacher->profile_photo_path = $upload['file_path'];
                        $teacher->profile_photo_url = $upload['file_url'];
                        $teacher->file_disk = $upload['file_disk'];
                        $teacher->save();

                        if(!empty($old_path)){
                            FunctionController::deleteFile($old_path, $old_disk);
                        }
                    }
                }

                return response([
                    'status' => 'success',
                    'message' => 'School Teacher updated successfully',
                    'data' => $teacher
                ], 200);
            } else {
                return response([
                    'status' => 'failed',
                    'message' => 'School Teacher Update Failed'
                ], 500);
            }
        } else {
            return response([
                'status' => 'failed',
                'message' => 'No School Teacher not found'
            ]);
        }
    }

    public function add_certification(StoreTeacherCertificationRequest $request){
        $teacher = SchoolTeacher::find($request->school_teacher_id);
        if($teacher->school_location_id == $this->user->school_location_id){
            $school = School::find($this->user->school_id);
            $path = $school->slug.'/teachers';
            if($upload = FunctionController::uploadFile($path, $request->file('file'), $this->disk)){
                $certification = TeacherCertification::create([
                    'school_id' => $teacher->school_id,
                    'school_location_id' => $teacher->school_location_id,
                    'school_teacher_id' => $teacher->id,
                    'certification' => $request->certification,
                    'disk' => $this->disk,
                    'file_path' => $upload['file_path'],
                    'file_url' => $upload['file_url'],
                    'file_size' => $upload['file_size']
                ]);

                return response([
                    'status' => 'success',
                    'message' => 'Teacher Certification uploaded successfully',
                    'data' => $certification
                ], 200);
            } else {
                return response([
                    'status' => 'failed',
                    'message' => 'Could not add Certification'
                ], 500);
            }
        } else {
            return response([
                'status' => 'failed',
                'message' => 'No School Teacher was found'
            ], 404);
        }
    }

    public function remove_certification(TeacherCertification $certification){
        if($certification->school_location_id == $this->user->school_location_id){
            $old_path = $certification->file_path;
            $certification->delete();
            if(!empty($old_path)){
                FunctionController::deleteFile($old_path, $certification->disk);
            }
            return response([
                'status' => 'success',
                'message' => 'Certification successfully removed'
            ], 200);
        } else {
            return response([
                'status' => 'failed',
                'No Certification was fetched'
            ], 404);
        }
    }

    public function update_ceritification(UpdateTeacherCertificationRequest $request, $id){
        $certification = TeacherCertification::find($id);
        if(empty($certification) || ($certification->school_location_id != $this->user->school_location_id)){
            return response([
                'status' => 'failed',
                'message' => 'No Certification was fetched'
            ], 404);
            exit;
        }
        $certification->certification = $request->certification;
        if(!empty($request->file)){
            $old_path = $certification->file_path;
            $old_disk = $certification->disk;

            $school = School::find($this->user->school_id);
            $path = $school->slug.'/teachers';
            if($upload = FunctionController::uploadFile($path, $request->file('file'), $this->disk)){
                $certification->file_path = $upload['file_path'];
                $certification->file_url = $upload['file_url'];
                $certification->file_size = $upload['file_size'];
                $certification->disk = $this->disk;

                if(!empty($old_path)){
                    FunctionController::deleteFile($old_path, $old_disk);
                }
            }
        }
        $certification->save();

        return response([
            'status' => 'success',
            'message' => 'Certification updated successfully',
            'data' => $certification
        ], 200);
    }
}
