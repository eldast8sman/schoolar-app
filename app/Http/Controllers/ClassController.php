<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\SubClass;
use App\Models\MainClass;
use Illuminate\Http\Request;
use App\Models\SchoolTeacher;
use App\Models\SchoolLocation;
use App\Http\Requests\StoreClassRequest;
use App\Http\Requests\UpdateClassRequest;
use App\Http\Requests\ImportClassesRequest;
use App\Http\Requests\SortClassLevelRequest;
use App\Http\Requests\UpdateSubClassRequest;

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
                $sub_classes = SubClass::where('school_id', $class->school_id)->where('school_location_id', $class->school_location_id)->where('main_class_id', $class->id)->get();
                if(!empty($sub_classes)){
                    foreach($sub_classes as $sub_class){
                        if(!empty($sub_class->school_teacher_id)){
                            $sub_class->teacher = SchoolTeacher::find($sub_class->school_teacher_id);
                        }
                    }
                }
                $class->sub_classes = $sub_classes;

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
                $location = SchoolLocation::where('school_id', $this->user->school_id)->where('id', $request->school_location_id)->first();
                if(!empty($location)){
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
                                        SubClass::create([
                                            'school_id' => $this->user->school_id,
                                            'school_location_id' => $this->user->school_location_id,
                                            'main_class_id' => $new_class->id,
                                            'name' => $sub_class->name
                                        ]);
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

    public function update(UpdateClassRequest $request, MainClass $class){
        if(($class->school_id == $this->user->id) && ($class->school_location_id == $this->user->school_location_id)){
            $class->name = $request->name;
            $class->class_level = $request->class_level;
            $class->teacher_id = $request->teacher_id;
            $class->save();

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

    public function update_subClass(UpdateSubClassRequest $request, SubClass $sub_class){
        if(($sub_class->school_id == $this->user->school_id) && ($sub_class->school_location_id == $this->user->school_location_id)){
            $sub_class->name = $request->name;
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
                $level ++;
            }
        }

        return response([
            'status' => 'success',
            'message' => 'Class Levels sorted successfully'
        ], 200);
    }
}
