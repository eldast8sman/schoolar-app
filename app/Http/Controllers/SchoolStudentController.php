<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\SubClass;
use App\Models\MainClass;
use App\Mail\AddParentMail;
use Illuminate\Support\Str;
use App\Mail\AddStudentMail;
use App\Models\SchoolParent;
use Illuminate\Http\Request;
use App\Models\ParentStudent;
use App\Models\SchoolStudent;
use App\Models\Parent\Parents;
use App\Models\Student\Student;
use App\Models\StudentHealthInfo;
use App\Mail\AddStudentToParentMail;
use Illuminate\Support\Facades\Mail;
use App\Models\Parent\ParentSchoolParent;
use App\Http\Requests\StoreSchoolParentRequest;
use App\Http\Requests\StoreSchoolStudentRequest;
use App\Http\Requests\StoreStudentHealthInfoRequest;
use App\Http\Requests\StoreSchoolStudentParentRequest;
use App\Http\Requests\StoreStudentExistingParentRequest;

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
        if(!empty($health_info->immunizations)){
            $health_info->immunizations = explode(',', $health_info->immunizations);
        }
        if(!empty($health_info->disability)){
            $health_info->disability = explode(',', $health_info->disability);
        }
        $student->health_info = $health_info;

        $parents = [];
        $stu_parents = ParentStudent::where('school_student_id', $student->id);
        if($stu_parents->count() > 0){
            foreach($stu_parents->get() as $stu_parent){
                $parent = SchoolParent::find($stu_parent->school_parent_id);
                unset($parent->id);
                unset($parent->file_path);
                unset($parent->file_disk);
                $parent->primary_parent = $stu_parent->primary;
                $parents[] = $parent;
            }
        }
        $student->parents = $parents;

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
        $all['class_level'] = $main_class->class_level;
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

    public function store_health_info(StoreStudentHealthInfoRequest $request, $uuid){
        $student = SchoolStudent::where('school_location_id', $this->user->school_location_id)->where('uuid', $uuid)->first();
        if(empty($student)){
            return response([
                'status' => 'failed',
                'message' => 'No Student was fetched'
            ], 404);
        }
        $all = $request->except(['immunizations', 'disability']);
        if(isset($request->immunizations) and !empty($request->immunizations)){
            $all['immunizations'] = join(',', $request->immunizations);
        }
        if(isset($request->disability) and !empty($request->disability)){
            $all['disability'] = join(',', $request->disability);
        }
        $info = StudentHealthInfo::where('school_student_id', $student->id)->first();
        $info->update($all);

        $student->registration_stage = ($student->registration_stage < 2) ? 2 : $student->registration_stage;
        $student->save();
        
        return response([
            'status' => 'success',
            'message' => 'Student\'s Health Records updated successfully',
            'data' => $this->student($student)
        ], 200);
    }

    public function skip_health_info($uuid){
        $student = SchoolStudent::where('school_location_id', $this->user->school_location_id)->where('uuid', $uuid)->first();
        if(empty($student)){
            return response([
                'status' => 'failed',
                'message' => 'No Student was fetched'
            ], 404);
        }  
        
        $student->registration_stage = ($student->registration_stage < 2) ? 2 : $student->registration_stage;
        $student->save();
        
        return response([
            'status' => 'success',
            'message' => 'Student\'s Health Records updated successfully',
            'data' => $this->student($student)
        ], 200);
    }

    public function store_new_parent(StoreSchoolStudentParentRequest $request, $uuid){
        $student = SchoolStudent::where('school_location_id', $this->user->school_location_id)->where('uuid', $uuid)->first();
        if(empty($student)){
            return response([
                'status' => 'failed',
                'message' => 'No Student was fetched'
            ], 404);
        }
        if(SchoolParent::where('school_location_id', $this->user->school_location_id)->where('mobile', $request->mobile)->count() > 0){
            return response([
                'status' => 'failed',
                'message' => 'Duplicate Mobile Number'
            ], 409);
        }
        if(ParentStudent::where('school_student_id', $student->id)->count() > 1){
            return response([
                'status' => 'failed',
                'message' => 'A Student can only have maximum of two(2) Parents/Guardians'
            ], 409);
        }

        $all = $request->except(['primary_parent']);
        $all['school_id'] = $this->user->school_id;
        $all['school_location_id'] = $this->user->school_location_id;

        for($i=1; $i<=20; $i++){
            $uuid = Str::uuid();
            if(SchoolParent::where('school_location_id', $this->user->school_location_id)->where('uuid', $uuid)->count() < 1){
                $all['uuid'] = $uuid;
                break;
            } else {
                continue;
            }
        }

        if(isset($request->file) and !empty($request->file)){
            $school = School::find($this->user->school_id);
            if(empty($school)){
                return response([
                    'status' => 'failed',
                    'message' => 'No School was fetched'
                ], 409);
                exit;
            }

            $path = $school->slug.'/parents';
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
        $all['file_disk'] = $file_disk;
        $all['file_path'] = $file_path;
        $all['file_url'] = $file_url;
        $all['file_size'] = $file_size;

        if(!$school_parent = SchoolParent::create($all)){
            return response([
                'status' => 'failed',
                'message' => 'Parent Upload failed'
            ], 409);
        }

        $parent_student = ParentStudent::create([
            'school_id' => $this->user->school_id,
            'school_location_id' => $this->user->school_location_id,
            'school_student_id' => $student->id,
            'school_parent_id' => $school_parent->id,
            'primary' => $request->primary,
            'relationship' => $request->relationship
        ]);
        if($request->primary == true){
            $parentStudents = ParentStudent::where('school_student_id', $student->id)->where('id', '<>', $parent_student->id);
            if($parentStudents->count() > 0){
                foreach($parentStudents->get() as $p_student){
                    $p_student->primary = false;
                    $p_student->save();
                }
            }
        }

        if(Parents::where('mobile', $school_parent->mobile)->count() < 1){
            $token = Str::random(20).time();
            $expiry = date('Y-m-d H:i:s', time() + (60 * 60 *24));
            $parent = Parents::create([
                'first_name' => $school_parent->first_name,
                'last_name' => $school_parent->last_name,
                'email' => $school_parent->email,
                'mobile' => $school_parent->mobile,
                'token' => $token,
                'token_expiry' => $expiry,
                'nationality' => $school_parent->nationality,
                'occupation' => $school_parent->occupation,
                'address' => $school_parent->address,
                'town' => $school_parent->town,
                'lga' => $school_parent->lga,
                'state' => $school_parent->state,
                'country' => $school_parent->country,
                'file_path' => $school_parent->file_path,
                'file_url' => $school_parent->file_url,
                'file_size' => $school_parent->file_size,
                'file_disk' => $school_parent->file_disk
            ]);
            ParentSchoolParent::create([
                'parents_id' => $parent->id,
                'school_parent_id' => $school_parent->id
            ]);
            
            if(!empty($parent->email) and filter_var($parent->email, FILTER_VALIDATE_EMAIL)){
                $parent->name = $parent->first_name.' '.$parent->last_name;
                Mail::to($parent)->send(new AddParentMail($parent->name, $token));
                unset($parent->name);
            }
        }

        $student->registration_stage = 3;
        $student->save();

        if(!isset($school)){
            $school = School::find($this->user->school_id);
        }        
        if(!empty($school_parent->email) and filter_var($school_parent->email. FILTER_VALIDATE_EMAIL)){
            $student_name = $student->first_name.' '.$student->last_name;
            $school_parent->name = $school_parent->first_name.' '.$school_parent->last_name;
            Mail::to($school_parent)->send(new AddStudentToParentMail($school_parent->name, $school->name, $student_name));
            unset($school_parent->name);
            unset($student_name);
        }
        
        return response([
            'status' => 'success',
            'message' => 'Parent added successfully',
            'data' => $this->student($student)
        ], 200);
    }

    public function store_existing_parent(StoreStudentExistingParentRequest $request, $uuid){
        $student = SchoolStudent::where('school_location_id', $this->user->school_location_id)->where('uuid', $uuid)->first();
        if(empty($student)){
            return response([
                'status' => 'failed',
                'message' => 'No Student was fetched'
            ], 404);
        }
        if(ParentStudent::where('school_student_id', $student->id)->count() > 0){
            return response([
                'status' => 'failed',
                'message' => 'A Student can only have maximum of two(2) Parents/Guardians'
            ], 409);
        }
        $parent = SchoolParent::where('school_location_id', $this->user->school_location_id)->where('uuid', $request->parent_uuid)->first();
        if(empty($parent)){
            return response([
                'status' => 'failed',
                'message' => 'No Parent was fetched'
            ], 404);
        }
        if(ParentStudent::where('school_student_id', $student->id)->where('school_parent_id', $parent->id)->count() > 0){
            return response([
                'status' => 'failed',
                'message' => 'This Parent already added to this Student'
            ], 409);
        }

        $parent_student = ParentStudent::create([
            'school_id' => $this->user->school_id,
            'school_location_id' => $this->user->school_location_id,
            'school_student_id' => $student->id,
            'school_parent_id' => $parent->id,
            'primary' => $request->primary,
            'relationship' => $request->relationship
        ]);
        if($request->primary == true){
            $parentStudents = ParentStudent::where('student_id', $student->id)->where('id', '<>', $parent_student->id);
            if($parentStudents->count() > 0){
                foreach($parentStudents->get() as $p_student){
                    $p_student->primary = false;
                    $p_student->save();
                }
            }
        }
        $school = School::find($this->user->school_id);
        if(!empty($parent->email) and filter_var($parent->email. FILTER_VALIDATE_EMAIL)){
            $student_name = $student->first_name.' '.$student->last_name;
            $parent->name = $parent->first_name.' '.$parent->last_name;
            Mail::to($parent)->send(new AddStudentToParentMail($parent->name, $school->name, $student_name));
            unset($parent->name);
            unset($student_name);
        }

        $student->registration_stage = 3;
        $student->save();

        return response([
            'status' => 'success',
            'message' => 'Parent added successfully',
            'data' => $this->student($student)
        ], 200);
    }

    public function skip_add_parent($uuid){
        $student = SchoolStudent::where('school_location_id', $this->user->school_location_id)->where('uuid', $uuid)->first();
        if(empty($student)){
            return response([
                'status' => 'failed',
                'message' => 'No Student was fetched'
            ], 404);
        }

        $student->registration_stage = ($student->registration_stage < 3) ? 3 : $student->registration_stage;
        $student->save();
        
        return response([
            'status' => 'success',
            'message' => 'Parent\'s addition skipped successfully',
            'data' => $this->student($student)
        ], 200);
    }

    public function index(){
        $search = !empty($_GET['search']) ? (string)$_GET['search'] : "";
        $filter = isset($_GET['filter']) ? (int)$_GET['filter'] : NULL;
        $limit = !empty($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $sort = !empty($_GET['sort']) ? (string)$_GET['sort'] : 'asc';

        $students = SchoolStudent::where('school_id', $this->user->school_id)->where('school_location_id', $this->user->school_location_id);
        if(!empty($search)){
            $names = explode(' ', $search);
            foreach($names as $name){
                $name = trim($name);
                $students = $students->where(function($query) use ($name){
                    $query->where("first_name", "like", '%'.$name.'%')
                        ->orWhere("last_name", "like", '%'.$name.'%')
                        ->orWhere("middle_name", "like", '%'.$name.'%');
                });
            }
        }
        if($filter !== NULL){
            $students = $students->where('status', $filter);
        }
        if($filter != 2){
            $students = $students->where('status', '<>', 2);
        }
        $students = $students->orderBy('class_level', $sort);

        if($students->count() < 1){
            return response([
                'status' => 'failed',
                'message' => 'No Student was fetched',
                'data' => null
            ], 200);
        }

        $students = $students->get();
        foreach($students as $student){
            $student = $this->student($student);
        }

        return response([
            'status' => 'success',
            'message' => 'Students fetched successfully',
            'data' => $students
        ], 200);
    }

    public function show($uuid){
        $student = SchoolStudent::where('school_location_id', $this->user->school_location_id)->where('uuid', $uuid)->first();
        if(empty($student)){
            return response([
                'status' => 'failed',
                'message' => 'No Student found'
            ], 404);
        }
        
        return response([
            'status' => 'success',
            'message' => 'Student fetched successfully',
            'data' => $this->student($student)
        ], 200);
    }
}
