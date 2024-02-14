<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignStudentToParentRequest;
use App\Http\Requests\StoreSchoolParentRequest;
use App\Mail\AddParentMail;
use App\Mail\AddStudentToParentMail;
use App\Models\MainClass;
use App\Models\Parent\Parents;
use App\Models\Parent\ParentSchoolParent;
use App\Models\ParentStudent;
use App\Models\School;
use App\Models\SchoolParent;
use App\Models\SchoolStudent;
use App\Models\SubClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SchoolParentController extends Controller
{
    private $user;
    private $disk = 'public';

    public function __construct()
    {
        $this->middleware('auth:user-api');
        $this->user = AuthController::user();
    }

    public static function parent(SchoolParent $parent) : SchoolParent
    {
        $par_students = ParentStudent::where('school_parent_id', $parent->id)->get();
        if(!empty($par_students)){
            $students = [];
            foreach($par_students as $par_student){
                $student = SchoolStudent::find($par_student->school_student_id);
                $student->main_class = MainClass::find($student->main_class_id)->name;
                $student->sub_class = SubClass::find($student->sub_class_id)->name;
                $students[] = $student;
            }
            $parent->students = $students;
        } else {
            $parent->students = [];
        }
        return $parent;
    }

    public function index(){
        $search = !empty($_GET['search']) ? (string)$_GET['search'] : "";
        $filter = isset($_GET['filter']) ? (int)$_GET['filter'] : NULL;
        $sort = !empty($_GET['sort']) ? (string)$_GET['sort'] : "asc";
        $limit = !empty($_GET['limit']) ? (string)$_GET['limit'] : "";

        $parents = SchoolParent::where('school_id', $this->user->school_id)->where('school_location_id', $this->user->school_location_id);
        if(!empty($search)){
            $names = explode(' ', $search);
            foreach($names as $name){
                $name = trim($name);
                $parents = $parents->where(function($query) use ($name){
                    $query->where('first_name', 'like', '%'.$name.'%')
                        ->orWhere('last_name', 'like', '%'.$name.'%');
                });
            }
        }
        if($filter !== NULL){
            $parents = $parents->where('status', $filter);
        }
        if($filter != 2){
            $parents = $parents->where('status', '<>', 2);
        }
        $parents = $parents->orderBy('first_name', $sort)->orderBy('last_name', $sort);

        if($parents->count() < 1){
            return response([
                'status' => 'failed',
                'message' => 'No Parent was fetched',
                'data' => null
            ], 200);
        }

        $parents = $parents->paginate($limit);
        foreach($parents as $parent){
            $parent = $this->parent($parent);
        }

        return response([
            'status' => 'success',
            'message' => 'Parents fetched successfully',
            'data' => $parents
        ], 200);
    }

    public function store(StoreSchoolParentRequest $request){
        if(SchoolParent::where('school_location_id', $this->user->school_location_id)->where('mobile', $request->mobile)
        ->where('status', '<>', 2)->count() > 0){
            return response([
                'status' => 'failed',
                'message' => 'Phone Number already exist'
            ], 409);
        }

        if(isset($request->file) and !empty($request->fiie)){
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

        $all = $request->except(['file']);
        for($i=1; $i<=20; $i++){
            $uuid = Str::uuid();
            if(SchoolParent::where('school_location_id', $this->user->school_location_id)->where('uuid', $uuid)->count() < 1){
                $all['uuid'] = $uuid;
                break;
            } else {
                continue;
            }
        }
        $all['school_id'] = $this->user->school_id;
        $all['school_location_id'] = $this->user->school_location_id;
        $all['disk'] = $file_disk;
        $all['file_path'] = $file_path;
        $all['file_url'] = $file_url;
        $all['file_size'] = $file_size;
        $all['status'] = 1;

        if(!$school_parent = SchoolParent::create($all)){
            return response([
                'status' => 'failed',
                'message' => 'Parent Upload failed'
            ], 409);
        }

        $parent_user = Parents::where('mobile', $school_parent->mobile)->first();
        if(empty($parent_user)){
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
        } else {
            ParentSchoolParent::create([
                'parents_id' => $parent_user->id,
                'school_parent_id' => $school_parent->id
            ]);
        }

        return response([
            'status' => 'success',
            'message' => 'Parent added successfully',
            'data' => $this->parent($school_parent)
        ], 200);
    }

    public function show($uuid){
        $parent = SchoolParent::where('school_location_id', $this->user->school_location_id)->where('uuid', $uuid)->first();
        if(empty($parent)){
            return response([
                'status' => 'failed',
                'message' => 'No Parent was found'
            ], 404);
        }

        return response([
            'status' => 'success',
            'message' => 'Parent fethced successfully',
            'data' => $this->parent($parent)
        ], 200);
    }

    public function assign_student(AssignStudentToParentRequest $request, $uuid){
        $parent = SchoolParent::where('school_location_id', $this->user->school_location_id)->where('uuid', $uuid)->first();
        if(empty($parent)){
            return response([
                'status' => 'failed',
                'message' => 'No Parent was fetched'
            ], 409);
        }
        $student = SchoolStudent::where('school_location_id', $this->user->school_location_id)->where('uuid', $request->student_uuid)->first();
        if(empty($student)){
            return response([
                'status' => 'failed',
                'message' => 'No Student was fetched'
            ], 409);
        }
        if(ParentStudent::where('school_student_id', $student->id)->where('school_parent_id', $parent->id)->count() > 0){
            return response([
                'status' => 'failed',
                'message' => 'This Parent already added to this Student'
            ], 409);
        }
        if(ParentStudent::where('school_student_id', $student->id)->count() > 1){
            return response([
                'status' => 'failed',
                'message' => 'A Student can only have maximum of two(2) Parents/Guardians'
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
            $parentStudents = ParentStudent::where('school_student_id', $student->id)->where('id', '<>', $parent_student->id);
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

        return response([
            'status' => 'success',
            'message' => 'Student assigned to Parent/Guardian added successfully',
            'data' => $this->parent($parent)
        ], 200);
    }

    public function update(StoreSchoolParentRequest $request, $uuid){
        $parent = SchoolParent::where('uuid', $uuid)->first();
        if(empty($parent)){
            return response([
                'status' => 'failed',
                'message' => 'No Parent was fetched'
            ], 404);
        }

        $all = $request->except(['file']);
        if(!empty($request->file)){
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
                $all['file_url'] = $upload['file_url'];
                $all['file_path'] = $upload['file_path'];
                $all['file_size'] = $upload['file_size'];
                $all['disk'] = $disk;
            }
        }

        if(!$parent->update($all)){
            return response([
                'status' => 'failed',
                'message' => 'Parent update failed'
            ],500);
        }

        return response([
            'status' => 'success',
            'message' => 'Parent updated successfully',
            'data' => $this->parent($parent)
        ], 200);
    }
}
