<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddSubClassRequest;
use App\Http\Requests\AssignTeacherToSubClassRequest;
use App\Http\Requests\AutoLoadClassRequest;
use App\Http\Requests\ImportClassesRequest;
use App\Http\Requests\SortClassLevelRequest;
use App\Http\Requests\StoreClassRequest;
use App\Http\Requests\UpdateClassRequest;
use App\Http\Requests\UpdateSubClassRequest;
use App\Models\MainClass;
use App\Models\School;
use App\Models\SchoolLocation;
use App\Models\SchoolStudent;
use App\Models\SchoolTeacher;
use App\Models\SubClass;
use App\Models\Subject;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    private $user;

    public function __construct()
    {
        $this->middleware('auth:user-api');
        $this->user = AuthController::user();
    }

    public function index(){
        $classes = MainClass::where('school_id', $this->user->school_id)->where('school_location_id', $this->user->school_location_id)->orderBy('class_level', 'asc');
        if($classes->count() > 0){
            $classes = $classes->get();

            foreach($classes as $class){
                if(!empty($class->school_teacher_id)){
                    $class->teacher = SchoolTeacher::find($class->school_teacher_id);
                }
                $sub_classes = SubClass::where('school_id', $class->school_id)->where('school_location_id', $class->school_location_id)->where('main_class_id', $class->id)->get();
                if(!empty($sub_classes)){
                    foreach($sub_classes as $sub_class){
                        if(!empty($sub_class->school_teacher_id)){
                            $sub_class->teacher = SchoolTeacher::find($sub_class->school_teacher_id);
                        }
                    }
                }
                $class->sub_classes = $sub_classes;
            }

            return response([
                'status' => 'success',
                'message' => 'Classes fetched successfully',
                'data' => $classes
            ], 200);
        } else {
            return response([
                'status' => 'failed',
                'message' => 'No Class was fetched',
                'data' => null
            ], 200);
        }
    }

    public function all_sub_classes(){
        $sub_classes = [];

        $classes = MainClass::where('school_id', $this->user->school_id)->where('school_location_id', $this->user->school_location_id)->orderBy('class_level', 'asc');
        if($classes->count() > 0){
            foreach($classes->get() as $class){
                $subclasses = SubClass::where('main_class_id', $class->id)->get();
                foreach($subclasses as $subclass){
                    $sub_classes[] =  $this->subclass($subclass);
                }
            }
        }

        return response([
            'status' => 'success',
            'message' => 'SubClasses fetched successfully',
            'data' => $sub_classes
        ], 200);
    }

    public function store(StoreClassRequest $request){
        if(MainClass::where('school_id', $this->user->school_id)->where('school_location_id', $this->user->school_location_id)->where('name', $request->name)->count() < 1){
            if(MainClass::where('school_id', $this->user->school_id)->where('school_location_id', $this->user->school_location_id)->where('class_level', $request->class_level)->count() < 1){
                if($class = MainClass::create([
                    'school_id' => $this->user->school_id,
                    'school_location_id' => $this->user->school_location_id,
                    'class_level' => $request->class_level,
                    'name' => $request->name
                ])){
                    $sub_classes = [];
                    if($request->sub_classes > 1){
                        $subs = str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ');
                        $index = 0;
                        for($i=1; $i<=$request->sub_classes; $i++){
                            $subclass = SubClass::create([
                                'school_id' => $class->school_id,
                                'school_location_id' => $class->school_location_id,
                                'main_class_id' => $class->id,
                                'name' => $subs[$index]
                            ]);
                            $sub_classes[] = $subclass;
                            $index ++;
                        }
                    } else {
                        $subclass = SubClass::create([
                            'school_id' => $class->school_id,
                            'school_location_id' => $class->school_location_id,
                            'main_class_id' => $class->id,
                            'name' => 'A'
                        ]);
                        $sub_classes = $subclass;
                    }

                    $class->sub_classes = $sub_classes;

                    return response([
                        'status' => 'success',
                        'message' => 'Class successfully added',
                        'data' => $class
                    ], 200);
                } else {
                    return response([
                        'status' => 'failed',
                        'message' => 'Oops!! Class creation failed! Please try again later! If this persists, please reach out to us'
                    ], 500);
                }
            } else {
                return response([
                    'status' => 'failed',
                    'message' => 'You already have a Class with this Class Level'
                ], 409);
            }
        } else {
            return response([
                'status' => 'failed',
                'message' => 'You already have a Class with this Class Name'
            ], 409);
        }
    }

    public function show(MainClass $class){
        if(!empty($class)){
            if(($class->school_id == $this->user->school_id) && ($class->school_location_id == $this->user->school_location_id)){
                if(!empty($class->school_teacher_id)){
                    $class->teacher = SchoolTeacher::find($class->school_teacher_id);
                }
                $subclasses = [];
                $sub_classes = SubClass::where('school_id', $class->school_id)->where('school_location_id', $class->school_location_id)->where('main_class_id', $class->id)->get();
                if(!empty($sub_classes)){
                    foreach($sub_classes as $sub_class){
                        $subclasses[] = $this->subclass($sub_class);
                    }
                } 
                $class->sub_classes = $subclasses;

                return response([
                    'status' => 'success',
                    'message' => 'Class Fetched suceessfully',
                    'data' => $class
                ], 200);
            } else {
                return response([
                    'status' => 'failed',
                    'message' => 'No Class was fetched'
                ], 404);
            }
        } else {
            return response([
                'status' => 'failed',
                'message' => 'No Class was fetched'
            ], 404);
        }
    }

    private function subclass(SubClass $subclass) : SubClass
    {
        $class = MainClass::find($subclass->main_class_id);
        $subclass->class_name = $class->name;
        if(!empty($subclass->teacher_id)){
            $subclass->teacher = SchoolTeacher::find($subclass->teacher_id);
        } else {
            $subclass->teacher = [];
        }

        return $subclass;
    }

    public function show_subclass(SubClass $subclass){
        if(($subclass->school_id != $this->user->school_id) or ($subclass->school_location_id != $this->user->school_location_id)){
            return response([
                'status' => 'failed',
                'message' => 'No SubClass was fetched'
            ]);
        }

        return response([
            'status' => 'success',
            'message' => 'Subclass fetched successfully',
            'data' => $this->subclass($subclass)
        ], 200);
    }

    public function other_locations(){
        $school = School::find($this->user->school_id);
        if($school->type == 'group'){
            $locations = SchoolLocation::where('school_id', $this->user->school_id)->where('id', '<>', $this->user->school_location_id);
            if($locations->count() > 0){
                return response([
                    'status' => 'success',
                    'message' => 'Other School Locations fetched successfully',
                    'data' => $locations->get()
                ], 200);
            } else {
                return response([
                    'status' => 'failed',
                    'message' => 'No other School Location has been added'
                ], 404);
            }
        } else {
            return response([
                'status' => 'failed',
                'message' => 'This feature only works for Group of Schools'
            ], 404);
        }
    }

    public function import_class(ImportClassesRequest $request){
        $school = School::find($this->user->school_id);
        if($school->type == 'group'){
            if($request->school_location_id != $this->user->school_location_id){
                $present_location = SchoolLocation::find($this->user->school_location_id);
                $location = SchoolLocation::where('school_id', $this->user->school_id)->where('id', $request->school_location_id)->first();
                if(!empty($location)){
                    if($present_location->location_type != $location->location_type){
                        return response([
                            'status' => 'failed',
                            'message' => 'Not same kind of Schools'
                        ], 409);
                    }
                    $classes = MainClass::where('school_location_id', $location->id);
                    if($classes->count() > 0){
                        $count = 0;
                        $success = 0;
                        $failed = 0;

                        foreach($classes->get() as $class){
                            $count += 1;
                            $found = MainClass::where('school_location_id', $this->user->school_location_id)->where(function($query) use ($class){
                                $query->where('name', $class->name)->orWhere('class_level', $class->class_level);
                            });
                            if($found->count() < 1){
                                $new_class = MainClass::create([
                                    'school_id' => $this->user->school_id,
                                    'school_location_id' => $this->user->school_location_id,
                                    'class_level' => $class->class_level,
                                    'name' => $class->name
                                ]);

                                $sub_classes = SubClass::where('main_class_id', $class->id);
                                if($sub_classes->count() > 0){
                                    foreach($sub_classes->get() as $sub_class){
                                        $subclass = SubClass::create([
                                            'school_id' => $this->user->school_id,
                                            'school_location_id' => $this->user->school_location_id,
                                            'main_class_id' => $new_class->id,
                                            'name' => $sub_class->name,
                                            'type' => $sub_class->type
                                        ]);

                                        if($request->import_subjects == true){
                                            $subjects = FunctionController::default_subjects();
                                            if($present_location->location_type == 'primary'){
                                                foreach($subjects['primary'] as $subject){
                                                    Subject::create([
                                                        'school_id' => $school->id,
                                                        'school_location_id' => $present_location->id,
                                                        'main_class_id' => $new_class->id,
                                                        'sub_class_id' => $subclass->id,
                                                        'name' => $subject['subject'],
                                                        'compulsory' => $subject['compulsory']
                                                    ]);
                                                }
                                            } elseif($present_location->location_type == 'secondary'){
                                                if($new_class->class_level <= 3){
                                                    foreach($subjects['junior_secondary'] as $subject){
                                                        Subject::create([
                                                            'school_id' => $school->id,
                                                            'school_location_id' => $present_location->id,
                                                            'main_class_id' => $new_class->id,
                                                            'sub_class_id' => $subclass->id,
                                                            'name' => $subject['subject'],
                                                            'compulsory' => $subject['compulsory']
                                                        ]);
                                                    }
                                                } else {
                                                    if($subclass->type != 'general'){
                                                        foreach($subjects['senior_secondary'][$subclass->type] as $subject){
                                                            if(Subject::where('sub_class_id', $subclass->id)->where('name', $subject['subject'])->count() < 1){
                                                                Subject::create([
                                                                    'school_id' => $subclass->school_id,
                                                                    'school_location_id' => $subclass->school_location_id,
                                                                    'main_class_id' => $subclass->main_class_id,
                                                                    'sub_class_id' => $subclass->id,
                                                                    'name' => $subject['subject'],
                                                                    'compulsory' => $subject['compulsory']
                                                                ]);
                                                            }
                                                        }
                                                    } else {
                                                        foreach($subjects['senior_secondary']['sciences'] as $subject){
                                                            if(Subject::where('sub_class_id', $subclass->id)->where('name', $subject['subject'])->count() < 1){
                                                                Subject::create([
                                                                    'school_id' => $subclass->school_id,
                                                                    'school_location_id' => $subclass->school_location_id,
                                                                    'main_class_id' => $subclass->main_class_id,
                                                                    'sub_class_id' => $subclass->id,
                                                                    'name' => $subject['subject'],
                                                                    'compulsory' => $subject['compulsory']
                                                                ]);
                                                            }
                                                        }
                                
                                                        foreach($subjects['senior_secondary']['arts'] as $subject){
                                                            if(Subject::where('sub_class_id', $subclass->id)->where('name', $subject['subject'])->count() < 1){
                                                                Subject::create([
                                                                    'school_id' => $subclass->school_id,
                                                                    'school_location_id' => $subclass->school_location_id,
                                                                    'main_class_id' => $subclass->main_class_id,
                                                                    'sub_class_id' => $subclass->id,
                                                                    'name' => $subject['subject'],
                                                                    'compulsory' => $subject['compulsory']
                                                                ]);
                                                            }
                                                        }
                                
                                                        foreach($subjects['senior_secondary']['commerce'] as $subject){
                                                            if(Subject::where('sub_class_id', $subclass->id)->where('name', $subject['subject'])->count() < 1){
                                                                Subject::create([
                                                                    'school_id' => $subclass->school_id,
                                                                    'school_location_id' => $subclass->school_location_id,
                                                                    'main_class_id' => $subclass->main_class_id,
                                                                    'sub_class_id' => $subclass->id,
                                                                    'name' => $subject['subject'],
                                                                    'compulsory' => $subject['compulsory']
                                                                ]);
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }

                                $success += 1;
                            } else {
                                $failed += 1;
                            }
                        }

                        return response([
                            'status' => 'success',
                            'message' => $count.' attempted; '.$success.' imported; '.$failed.' failed'
                        ], 200);
                    } else {
                        return response([
                            'status' => 'failed',
                            'message' => 'No Class was fetched'
                        ], 406);
                    }
                } else {
                    return response([
                        'status' => 'failed',
                        'message' => 'School Location not fetched'
                    ], 404);
                }
            } else {
                return response([
                    'status' => 'failed',
                    'message' => 'You cannot import from the same location'
                ], 409);
            }
        } else {
            return response([
                'status' => 'failed',
                'message' => 'This feature only works for Group of Schools'
            ], 405);
        }
    }

    public function load_default_classes(AutoLoadClassRequest $request){
        $school = School::find($this->user->school_id);
        $location = SchoolLocation::find($this->user->school_location_id);
        if(strtolower($location->country) == 'nigeria'){
            $subjects = FunctionController::default_subjects();
            if($location->location_type == "primary"){
                for($i=1; $i<=6; $i++){
                    if(empty(MainClass::where('school_location_id', $location->id)->where('class_level', $i)->first())){
                        $class = MainClass::create([
                            'school_id' => $location->school_id,
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
                        
                        if($request->load_subjects == true){
                            foreach($subjects['primary'] as $subject){
                                Subject::create([
                                    'school_id' => $location->school_id,
                                    'school_location_id' => $location->id,
                                    'main_class_id' => $class->id,
                                    'sub_class_id' => $subclass->id,
                                    'name' => $subject['subject'],
                                    'compulsory' => $subject['compulsory']
                                ]);
                            }
                        }
                    }
                }
            } elseif($location->location_type == "secondary"){
                for($i=1; $i<=3; $i++){
                    if(empty(MainClass::where('school_location_id', $location->id)->where('class_level', $i)->first())){
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

                        if($request->load_subjects == true){
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
                    }
                }

                for($i=1; $i<=3; $i++){
                    $level = $i + 3;
                    if(empty(MainClass::where('school_location_id', $location->id)->where('class_level', $level)->first())){
                        $class = MainClass::create([
                            'school_id' => $school->id,
                            'school_location_id' => $location->id,
                            'class_level' => $level,
                            'name' => 'SSS '.$i
                        ]);

                        $sciences = SubClass::create([
                            'school_id' => $school->id,
                            'school_location_id' => $location->id,
                            'main_class_id' => $class->id,
                            'name' => 'A',
                            'type' => 'sciences'
                        ]);
                        if($request->load_subjects == true){
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
                        }

                        $arts = SubClass::create([
                            'school_id' => $school->id,
                            'school_location_id' => $location->id,
                            'main_class_id' => $class->id,
                            'name' => 'B',
                            'type' => 'arts'
                        ]);
                        if($request->load_subjects == true){
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
                        }

                        $commerce = SubClass::create([
                            'school_id' => $school->id,
                            'school_location_id' => $location->id,
                            'main_class_id' => $class->id,
                            'name' => 'C',
                            'type' => 'commerce'
                        ]);
                        if($request->load_subjects == true){
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
                }
            }
        }

        return response([
            'status' => 'success',
            'message' => 'Classes Loaded successfully'
        ], 200);
    }

    public function update(UpdateClassRequest $request, MainClass $class){
        if(($class->school_id == $this->user->id) && ($class->school_location_id == $this->user->school_location_id)){
            $class->name = $request->name;
            $class->class_level = $request->class_level;
            $class->teacher_id = $request->teacher_id;
            $class->save();
            $class->update_dependencies();

            return response([
                'status' => 'success',
                'message' => 'Class Updated successfully',
                'data' => $class
            ], 200);
        } else {
            return response([
                'status' => 'failed',
                'message' => 'No Class was found'
            ], 404);
        }
    }

    public function add_subclass(AddSubClassRequest $request, MainClass $class){
        if(($class->school_id != $this->user->school_id) or ($class->school_location_id != $this->user->school_location_id)){
            return response([
                'status' => 'failed',
                'message' => 'No Class was found'
            ], 404);
        }

        if(SubClass::where('main_class_id', $class->id)->where('name', $request->name)->count() > 0){
            return response([
                'status' => 'failed',
                'message' => 'There is already a Sub-Class with this name'
            ], 409);
        }

        $subclass = SubClass::create([
            'school_id' => $class->school_id,
            'school_location_id' => $class->school_location_id,
            'main_class_id' => $class->id,
            'name' => $request->name,
            'type' => !empty($request->type) ? (string)$request->type : 'general'
        ]);

        if($request->load_default == true){
            $location = SchoolLocation::find($class->school_location_id);
            if(strtolower($location->country) == 'nigeria'){
                $subjects = FunctionController::default_subjects();
                if($location->location_type == "primary"){
                    foreach($subjects['primary'] as $subject){
                        if(Subject::where('sub_class_id', $subclass->id)->where('name', $subject['subject'])->count() < 1){
                            Subject::create([
                                'school_id' => $subclass->school_id,
                                'school_location_id' => $subclass->school_location_id,
                                'main_class_id' => $subclass->main_class_id,
                                'sub_class_id' => $subclass->id,
                                'name' => $subject['subject'],
                                'compulsory' => $subject['compulsory']
                            ]);
                        }
                    }
                } elseif(strtolower($location->location_type) == 'secondary'){
                    $main_class = MainClass::find($subclass->main_class_id);
                    if(($main_class->class_level >= 1) and ($main_class->class_level <= 3)){
                        foreach($subjects['junior_secondary'] as $subject){
                            if(Subject::where('sub_class_id', $subclass->id)->where('name', $subject['subject'])->count() < 1){
                                Subject::create([
                                    'school_id' => $subclass->school_id,
                                    'school_location_id' => $subclass->school_location_id,
                                    'main_class_id' => $subclass->main_class_id,
                                    'sub_class_id' => $subclass->id,
                                    'name' => $subject['subject'],
                                    'compulsory' => $subject['compulsory']
                                ]);
                            }
                        }
                    } elseif(($main_class->class_level >=4) and ($main_class->class_level <= 6)){
                        if($subclass->type != "general"){
                            foreach($subjects['senior_secondary'][$subclass->type] as $subject){
                                if(Subject::where('sub_class_id', $subclass->id)->where('name', $subject['subject'])->count() < 1){
                                    Subject::create([
                                        'school_id' => $subclass->school_id,
                                        'school_location_id' => $subclass->school_location_id,
                                        'main_class_id' => $subclass->main_class_id,
                                        'sub_class_id' => $subclass->id,
                                        'name' => $subject['subject'],
                                        'compulsory' => $subject['compulsory']
                                    ]);
                                }
                            }
                        } else {
                            foreach($subjects['senior_secondary']['sciences'] as $subject){
                                if(Subject::where('sub_class_id', $subclass->id)->where('name', $subject['subject'])->count() < 1){
                                    Subject::create([
                                        'school_id' => $subclass->school_id,
                                        'school_location_id' => $subclass->school_location_id,
                                        'main_class_id' => $subclass->main_class_id,
                                        'sub_class_id' => $subclass->id,
                                        'name' => $subject['subject'],
                                        'compulsory' => $subject['compulsory']
                                    ]);
                                }
                            }

                            foreach($subjects['senior_secondary']['arts'] as $subject){
                                if(Subject::where('sub_class_id', $subclass->id)->where('name', $subject['subject'])->count() < 1){
                                    Subject::create([
                                        'school_id' => $subclass->school_id,
                                        'school_location_id' => $subclass->school_location_id,
                                        'main_class_id' => $subclass->main_class_id,
                                        'sub_class_id' => $subclass->id,
                                        'name' => $subject['subject'],
                                        'compulsory' => $subject['compulsory']
                                    ]);
                                }
                            }

                            foreach($subjects['senior_secondary']['commerce'] as $subject){
                                if(Subject::where('sub_class_id', $subclass->id)->where('name', $subject['subject'])->count() < 1){
                                    Subject::create([
                                        'school_id' => $subclass->school_id,
                                        'school_location_id' => $subclass->school_location_id,
                                        'main_class_id' => $subclass->main_class_id,
                                        'sub_class_id' => $subclass->id,
                                        'name' => $subject['subject'],
                                        'compulsory' => $subject['compulsory']
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
        }

        return response([
            'status' => 'success',
            'message' => 'SubClass added to Class',
            'data' => $this->subclass($subclass)
        ], 200);
    }

    public function update_subClass(UpdateSubClassRequest $request, SubClass $sub_class){
        if(($sub_class->school_id == $this->user->school_id) && ($sub_class->school_location_id == $this->user->school_location_id)){
            $sub_class->name = $request->name;
            if(!empty($request->type)){
                $sub_class->type = $request->type;
            }
            $sub_class->teacher_id = $request->school_teacher_id;
            $sub_class->save();

            return response([
                'status' => 'success',
                'message' => 'Sub Class Updated successfully',
                'data' => $sub_class
            ], 200);
        } else {
            return response([
                'status' => 'failed',
                'message' => 'No Class was found'
            ], 404);
        }
    }

    public function destroy(MainClass $class){
        if(($class->school_id == $this->user->id) && ($class->school_location_id == $this->user->school_location_id)){
            $class->delete();
            $sub_classes = SubClass::where('main_class_id', $class->id);
            if($sub_classes->count() > 0){
                foreach($sub_classes->get() as $sub_class){
                    $sub_class->delete();
                }
            }

            return response([
                'status' => 'failed',
                'message' => 'Class deleted successfully',
                'data' => $class
            ], 200);
        } else {
            return response([
                'status' => 'failed',
                'message' => 'No Class was found'
            ], 404);
        }
    }

    public function destroy_subClass(SubClass $class){
        if(($class->school_id == $this->user->id) && ($class->school_location_id == $this->user->school_location_id)){
            $class->delete();

            return response([
                'status' => 'failed',
                'message' => 'Class deleted successfully',
                'data' => $class
            ], 200);
        } else {
            return response([
                'status' => 'failed',
                'message' => 'No Sub Class was found'
            ], 404);
        }
    }

    public function sort_class_level(SortClassLevelRequest $request){
        $ids = $request->classes;

        $level = 1;
        foreach($ids as $id){
            $main_class = MainClass::find(trim($id));
            if(!empty($main_class) and ($main_class->school_id == $this->user->school_id) and ($main_class->school_location_id == $this->user->school_location_id)){
                $main_class->class_level = $level;
                $main_class->save();
                $main_class->update_dependencies();
                $level ++;
            }
        }

        return response([
            'status' => 'success',
            'message' => 'Class Levels sorted successfully'
        ], 200);
    }

    public function assign_teacher(AssignTeacherToSubClassRequest $request, SubClass $subclass){
        if(($subclass->school_id != $this->user->school_id) or ($subclass->school_location_id != $this->user->school_location_id)){
            return response([
                'status' => 'failed',
                'message' => 'No SubClass was fetched'
            ]);
        }

        $teacher = SchoolTeacher::find($request->teacher_id);
        if(($teacher->school_id != $subclass->school_id) or ($teacher->school_location_id != $subclass->school_location_id)){
            return response([
                'status' => 'failed',
                'message' => 'No Teacher was fetched'
            ], 409);
        }

        $subclass->teacher_id = $teacher->id;
        $subclass->save();

        return response([
            'status' => 'success',
            'message' => 'Teacher assigned successfully',
            'data' => $this->subclass($subclass)
        ], 200);
    }

    public function students(SubClass $class){
        $search = !empty($_GET['search']) ? (string)$_GET['search'] : "";
        $filter = isset($_GET['filter']) ? (int)$_GET['filter'] : NULL;
        $limit = !empty($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $sort = !empty($_GET['sort']) ? (string)$_GET['sort'] : 'asc';

        $students = SchoolStudent::where('school_id', $this->user->school_id)->where('school_location_id', $this->user->school_location_id)->where('sub_class_id', $class->id);
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

        $students = $students->orderBy('first_name', $sort)->orderBy('middle_name', $sort)->orderBy('last_name', $sort);
        if($students->count() < 1){
            return response([
                'status' => 'failed',
                'message' => 'No Student was fetched',
                'data' => null
            ], 200);
        }

        $students = $students->paginate($limit);
        $stud_controller = new SchoolStudentController();
        foreach($students as $student){
            $student = $stud_controller->student($student);
        }

        return response([
            'status' => 'success',
            'message' => 'Students fetched successfully',
            'data' => $students
        ], 200);
    }
}
