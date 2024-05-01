<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGradingSystemRequest;
use App\Models\GradingSystem;
use App\Models\SchoolLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GradingSystemController extends Controller
{
    private $user;

    public function __construct()
    {
        $this->middleware('auth:user-api');
        $this->user = AuthController::user();
    }

    public function load_default(){
        $location = SchoolLocation::find($this->user->school_location_id);
        $grades = FunctionController::default_grading_systems()[$location->location_type];
        if(empty($grades)){
            return response([
                'status' => 'failed',
                'message' => 'No Default Grading System for your School Type'
            ], 404);
        }

        $old_systems = GradingSystem::where('school_location_id', $this->user->school_location_id);
        if($old_systems->count() > 0){
            foreach($old_systems->get() as $old_system){
                $old_system->delete();
            }
        }

        $saved_grades = [];
        foreach($grades as $grade){
            $grade['school_id'] = $this->user->school_id;
            $grade['school_location_id'] = $this->user->school_location_id;
            $uuid = Str::uuid().'-'.time();
            $grade['uuid'] = $uuid;

            $saved_grades[] = GradingSystem::create($grade);
        }

        return response([
            'status' => 'success',
            'message' => 'Grading System successfully created',
            'data' => $saved_grades
        ], 200);
    }

    public function store(StoreGradingSystemRequest $request){
        $errors = [];
        if(($request->minumum < 0) or ($request->maximum > 100)){
            $errors[] = "The Score range must be within 0-100";
        }
        if($request->minimum > $request->maximum){
            $errors[] = "Minimum Score CANNOT be greater than the Maximum Score!";
        }
        if(GradingSystem::where('school_location_id', $this->user->school_location_id)->where('grade', $request->grade)->count() > 0){
            $errors[] = "Grades CANNOT be duplicated!";
        }
        if(GradingSystem::where('school_location_id', $this->user->school_location_id)->where('minimum', '<=', $request->minimum)->where('maximum', '>=', $request->minimum)->count() > 0){
            $errors[] = "This Score is overlapping with another Grade!";
        }
        if(GradingSystem::where('school_location_id', $this->user->school_location_id)->where('minimum', '<=', $request->maximum)->where('maximum', '>=', $request->maximum)->count() > 0){
            if(!in_array("This Score is overlapping with another Grade!", $errors)){
                $errors[] = "This Score is overlapping with another Grade!";
            }
        }
        if(GradingSystem::where('school_location_id', $this->user->school_location_id)->where('minimum', '>', $request->minimum)->where('maximum', '<', $request->maximum)->count() > 0){
            if(!in_array("This Score is overlapping with another Grade!", $errors)){
                $errors[] = "This Score is overlapping with another Grade!";
            }
        }
        if(!empty($errors)){
            return response([
                'status' => 'failed',
                'message' => join(' ', $errors)
            ], 409);
        }

        $all = $request->all();
        $all['school_id'] = $this->user->school_id;
        $all['school_location_id'] = $this->user->school_location_id;
        $all['uuid'] = Str::uuid().'-'.time();

        if(!$grading = GradingSystem::create($all)){
            return response([
                'status' => 'success',
                'message' => 'Grading System successfully added',
                'data' => $grading
            ], 200);
        }
    }

    public function index(){
        $grades = GradingSystem::where('school_location_id', $this->user->school_location_id)->orderBy('minimum', 'asc');
        if($grades->count() < 1){
            return response([
                'status' => 'failed',
                'message' => 'No grade was fetched',
                'data' => null
            ], 200);
        }

        return response([
            'status' => 'success',
            'message' => 'Grades successfully fetched',
            'data' => $grades
        ], 200);
    }

    public function show($uuid){
        if(empty($grade = GradingSystem::where('uuid', $uuid)->where('school_location_id', $this->user->school_location_id)->first())){
            return response([
                'status' => 'failed',
                'message' => 'No Grade was fetched'
            ], 404);
        }

        return response([
            'status' => 'success',
            'message' => ' Grade fetched successfully',
            'data' => $grade
        ], 200);
    }

    public function update(StoreGradingSystemRequest $request, $uuid){
        if(empty($grade = GradingSystem::where('uuid', $uuid)->where('school_location_id', $this->user->school_location_id)->first())){
            return response([
                'status' => 'failed',
                'message' => 'No Grade was fetched'
            ], 404);
        }

        $errors = [];
        if(($request->minumum < 0) or ($request->maximum > 100)){
            $errors[] = "The Score range must be within 0-100";
        }
        if($request->minimum > $request->maximum){
            $errors[] = "Minimum Score CANNOT be greater than the Maximum Score!";
        }
        if(GradingSystem::where('school_location_id', $this->user->school_location_id)->where('grade', $request->grade)->where('id', '<>', $grade->id)->count() > 0){
            $errors[] = "Grades CANNOT be duplicated!";
        }
        if(GradingSystem::where('school_location_id', $this->user->school_location_id)->where('minimum', '<=', $request->minimum)->where('maximum', '>=', $request->minimum)->where('id', '<>', $grade->id)->count() > 0){
            $errors[] = "This Score is overlapping with another Grade!";
        }
        if(GradingSystem::where('school_location_id', $this->user->school_location_id)->where('minimum', '<=', $request->maximum)->where('maximum', '>=', $request->maximum)->where('id', '<>', $grade->id)->count() > 0){
            if(!in_array("This Score is overlapping with another Grade!", $errors)){
                $errors[] = "This Score is overlapping with another Grade!";
            }
        }
        if(GradingSystem::where('school_location_id', $this->user->school_location_id)->where('minimum', '>', $request->minimum)->where('maximum', '<', $request->maximum)->where('id', '<>', $grade->id)->count() > 0){
            if(!in_array("This Score is overlapping with another Grade!", $errors)){
                $errors[] = "This Score is overlapping with another Grade!";
            }
        }
        if(!empty($errors)){
            return response([
                'status' => 'failed',
                'message' => join(' ', $errors)
            ], 409);
        }

        $all = $request->all();
        if(!$grade->update($all)){
            return response([
                'status' => 'failed',
                'message' => 'Grading System update failed'
            ], 500);
        }

        return response([
            'status' => 'failed',
            'message' => 'Grading System updated successfully',
            'data' => $grade
        ], 200);
    }

    public function destroy($uuid){
        if(empty($grade = GradingSystem::where('uuid', $uuid)->where('school_location_id', $this->user->school_location_id)->first())){
            return response([
                'status' => 'failed',
                'message' => 'No Grade was fetched'
            ], 404);
        }

        $grade->delete();

        return response([
            'status' => 'success',
            'message' => 'Grade deleted successfully'
        ], 200);
    }
}
