<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLessonPlanRequest as RequestsStoreLessonPlanRequest;
use App\Models\School;
use App\Models\SchoolSession;
use App\Models\SchoolTeacher;
use App\Models\Subject;
use App\Models\TeacherLessonPlan;

class LessonPlanController extends Controller
{
    private $user;
    private $disk = 'public';

    public function __construct(){
        $this->middleware('auth:user-api');
        $this->user = AuthController::user();
    }

    public function store(RequestsStoreLessonPlanRequest $request, SchoolTeacher $teacher){
        if(($teacher->school_id != $this->user->school_id) or ($teacher->school_location_id != $this->user->school_location_id)){
            return response([
                'status' => 'failed',
                'message' => 'No Teacher was fetched'
            ], 409);
        }

        if(TeacherLessonPlan::where('sub_class_id', $request->sub_class_id)->where('session_id', $request->session_id)->where('term_id', $request->term_id)->where('teacher_id', $request->teacher_id)->count() > 0){
            return response([
                'status' => 'failed',
                'message' => 'Lesson plan already added'
            ], 409);
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

            $path = $school->slug.'/lesson-plans';
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
            $lesson_plan = TeacherLessonPlan::create([
                'school_id' => $this->user->school_id,
                'school_location_id' => $this->user->school_location_id,
                'main_class_id' => $request->main_class_id,
                'sub_class_id' => $request->sub_class_id,
                'session_id' => $request->session_id,
                'term_id' => $request->term_id,
                'subject_id' => $request->subject_id,
                'teacher_id' => $request->teacher_id,
                'disk' => $file_disk,
                'file_path' => $file_path,
                'file_url' => $file_url,
                'file_size' => $file_size,
            ]);
            return response([
                'status' => 'success',
                'message' => 'Lesson plan added successfully',
                'data' => $lesson_plan
            ], 200);
        } else {
            return response([
                'status' => 'success',
                'message' => 'No lesson plan file provided',
                'data' => null
            ], 200);
        }
    }

    public function update(RequestsStoreLessonPlanRequest $request, TeacherLessonPlan $lesson_plan){
        if(empty($teacher_lesson_plan = TeacherLessonPlan::where('id', $lesson_plan)
            ->where('sub_class_id', $request->sub_class_id)
            ->where('session_id', $request->session_id)
            ->where('term_id', $request->term_id)
            ->where('school_location_id', $this->user->school_location_id)
            ->where('teacher_id', $request->teacher_id)->first())){
            
            return response([
                'status' => 'failed',
                'message' => 'No Lesson plan fetched'
            ], 409);
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

            $path = $school->slug.'/lesson-plans';
            $disk = !empty($request->disk) ? $request->disk : $this->disk;

            if($upload = FunctionController::uploadFile($path, $request->file('file'), $disk)){
                $file_url = $upload['file_url'];
                $file_path = $upload['file_path'];
                $file_size = $upload['file_size'];
                $file_disk = $disk;
                $teacher_lesson_plan->update([
                    'disk' => $file_disk,
                    'file_path' => $file_path,
                    'file_url' => $file_url,
                    'file_size' => $file_size,
                ]);
                return response([
                    'status' => 'success',
                    'message' => 'Lesson plan added successfully',
                    'data' => $teacher_lesson_plan
                ], 200);
            } else {
                return response([
                    'status' => 'failed',
                    'message' => 'Lesson plan upload failed'
                ], 409);
            }
        } else {
            return response([
                'status' => 'failed',
                'message' => 'No lesson plan file provided'
            ], 409);
        }

        
    }

    public function lesson_plan_by_session(SchoolSession $session_id){
        $search = !empty($_GET['search']) ? (string)$_GET['search'] : "";
        $filter = isset($_GET['filter']) ? (int)$_GET['filter'] : NULL;
        $sort = !empty($_GET['sort']) ? (string)$_GET['sort'] : "asc";
        $limit = !empty($_GET['limit']) ? (string)$_GET['limit'] : "";

        $lesson_plans = TeacherLessonPlan::where('school_id', $this->user->school_id)
            ->where('school_location_id', $this->user->school_location_id)
            ->where('session_id', $session_id);
        
        if(!empty($search)){
            $names = explode(' ', $search);
            foreach($names as $name){
                $name = trim($name);
                $lesson_plans = $lesson_plans->where(function($query) use ($name){
                    $query->where('first_name', 'like', '%'.$name.'%')
                        ->orWhere('last_name', 'like', '%'.$name.'%');
                });
            }
        }
        if($filter !== NULL){
            $lesson_plans = $lesson_plans->where('approval_status', $filter);
        }
        if($filter != 2){
            $lesson_plans = $lesson_plans->where('approval_status', '<>', 2);
        }
        $lesson_plans = $lesson_plans->orderBy('first_name', $sort)->orderBy('last_name', $sort);

        if($lesson_plans->count() < 1){
            return response([
                'status' => 'failed',
                'message' => 'No Parent was fetched',
                'data' => null
            ], 200);
        }

        $lesson_plans = $lesson_plans->paginate($limit);
        foreach($lesson_plans as $lesson_plan){
            $lesson_plan->teacher = SchoolTeacher::find($lesson_plan->teacher_id);
        }

        return response([
            'status' => 'success',
            'message' => 'lesson plans fetched successfully',
            'data' => $lesson_plans
        ], 200);
    }

    public function lesson_plan_by_term($term_id){
        $search = !empty($_GET['search']) ? (string)$_GET['search'] : "";
        $filter = isset($_GET['filter']) ? (int)$_GET['filter'] : NULL;
        $sort = !empty($_GET['sort']) ? (string)$_GET['sort'] : "asc";
        $limit = !empty($_GET['limit']) ? (string)$_GET['limit'] : "";

        $lesson_plans = TeacherLessonPlan::where('school_id', $this->user->school_id)
            ->where('school_location_id', $this->user->school_location_id)
            ->where('term_id', $term_id);
        
        if(!empty($search)){
            $names = explode(' ', $search);
            foreach($names as $name){
                $name = trim($name);
                $lesson_plans = $lesson_plans->where(function($query) use ($name){
                    $query->where('first_name', 'like', '%'.$name.'%')
                        ->orWhere('last_name', 'like', '%'.$name.'%');
                });
            }
        }
        if($filter !== NULL){
            $lesson_plans = $lesson_plans->where('approval_status', $filter);
        }
        if($filter != 2){
            $lesson_plans = $lesson_plans->where('approval_status', '<>', 2);
        }
        $lesson_plans = $lesson_plans->orderBy('first_name', $sort)->orderBy('last_name', $sort);

        if($lesson_plans->count() < 1){
            return response([
                'status' => 'failed',
                'message' => 'No Lesson plan was fetched',
                'data' => null
            ], 200);
        }

        foreach($lesson_plans as $lesson_plan){
            $lesson_plan->teacher = SchoolTeacher::find($lesson_plan->teacher_id);
        }

        return response([
            'status' => 'success',
            'message' => 'lesson plans fetched successfully',
            'data' => $lesson_plans
        ], 200);
    }

    public function lesson_plan_by_teacher($teacher_id){
        $search = !empty($_GET['search']) ? (string)$_GET['search'] : "";
        $filter = isset($_GET['filter']) ? (int)$_GET['filter'] : NULL;
        $sort = !empty($_GET['sort']) ? (string)$_GET['sort'] : "asc";
        $limit = !empty($_GET['limit']) ? (string)$_GET['limit'] : "";

        $lesson_plans = TeacherLessonPlan::where('school_id', $this->user->school_id)
            ->where('school_location_id', $this->user->school_location_id)
            ->where('teacher_id', $teacher_id);
        
        if(!empty($search)){
            $names = explode(' ', $search);
            foreach($names as $name){
                $name = trim($name);
                $lesson_plans = $lesson_plans->where(function($query) use ($name){
                    $query->where('first_name', 'like', '%'.$name.'%')
                        ->orWhere('last_name', 'like', '%'.$name.'%');
                });
            }
        }
        if($filter !== NULL){
            $lesson_plans = $lesson_plans->where('approval_status', $filter);
        }
        if($filter != 2){
            $lesson_plans = $lesson_plans->where('approval_status', '<>', 2);
        }
        $lesson_plans = $lesson_plans->orderBy('first_name', $sort)->orderBy('last_name', $sort);

        if($lesson_plans->count() < 1){
            return response([
                'status' => 'failed',
                'message' => 'No Lesson plan was fetched',
                'data' => null
            ], 200);
        }

        $lesson_plans = $lesson_plans->paginate($limit);
        foreach($lesson_plans as $lesson_plan){
            $lesson_plan->teacher = SchoolTeacher::find($lesson_plan->teacher_id);
        }

        return response([
            'status' => 'success',
            'message' => 'lesson plans fetched successfully',
            'data' => $lesson_plans
        ], 200);
    }

    public function lesson_plan_by_subject($subject_id){
        $search = !empty($_GET['search']) ? (string)$_GET['search'] : "";
        $filter = isset($_GET['filter']) ? (int)$_GET['filter'] : NULL;
        $sort = !empty($_GET['sort']) ? (string)$_GET['sort'] : "asc";
        $limit = !empty($_GET['limit']) ? (string)$_GET['limit'] : "";

        $lesson_plans = TeacherLessonPlan::where('school_id', $this->user->school_id)
            ->where('school_location_id', $this->user->school_location_id)
            ->where('subject_id', $subject_id);
        
        if(!empty($search)){
            $names = explode(' ', $search);
            foreach($names as $name){
                $name = trim($name);
                $lesson_plans = $lesson_plans->where(function($query) use ($name){
                    $query->where('first_name', 'like', '%'.$name.'%')
                        ->orWhere('last_name', 'like', '%'.$name.'%');
                });
            }
        }
        if($filter !== NULL){
            $lesson_plans = $lesson_plans->where('approval_status', $filter);
        }
        if($filter != 2){
            $lesson_plans = $lesson_plans->where('approval_status', '<>', 2);
        }
        $lesson_plans = $lesson_plans->orderBy('first_name', $sort)->orderBy('last_name', $sort);

        if($lesson_plans->count() < 1){
            return response([
                'status' => 'failed',
                'message' => 'No Lesson plan was fetched',
                'data' => null
            ], 200);
        }

        $lesson_plans = $lesson_plans->paginate($limit);
        foreach($lesson_plans as $lesson_plan){
            $lesson_plan->teacher = SchoolTeacher::find($lesson_plan->teacher_id);
        }

        return response([
            'status' => 'success',
            'message' => 'lesson plans fetched successfully',
            'data' => $lesson_plans
        ], 200);
    }

    public function show(TeacherLessonPlan $lesson_plan){
        if(($lesson_plan->school_id != $this->user->school_id) or ($lesson_plan->school_location_id != $this->user->school_location_id)){
            return response([
                'status' => 'failed',
                'message' => 'No Lesson plan was fetched'
            ], 409);
        }
        if (!empty($lesson_plan = TeacherLessonPlan::find($lesson_plan->id))) {
            $lesson_plan->teacher = SchoolTeacher::find($lesson_plan->teacher_id);
            $lesson_plan->subject = Subject::find($lesson_plan->subject_id);
        }
        return response([
            'status' => 'success',
            'message' => 'Lesson plan fetched successfully',
            'data' => $lesson_plan
        ], 200);
    }

    public function approve_lesson_plan(TeacherLessonPlan $lesson_plan){
        if(($lesson_plan->school_id != $this->user->school_id) or ($lesson_plan->school_location_id != $this->user->school_location_id)){
            return response([
                'status' => 'failed',
                'message' => 'No Lesson plan was fetched'
            ], 409);
        }
        if (!empty($lesson_plan = TeacherLessonPlan::find($lesson_plan->id))) {
            $lesson_plan->approval_status = 1;
            $lesson_plan->save();
        }
        return response([
            'status' => 'success',
            'message' => 'Lesson plan approved successfully',
            'data' => $lesson_plan
        ], 200);
    }

    public function decline_lesson_plan(TeacherLessonPlan $lesson_plan){
        if(($lesson_plan->school_id != $this->user->school_id) or ($lesson_plan->school_location_id != $this->user->school_location_id)){
            return response([
                'status' => 'failed',
                'message' => 'No Lesson plan was fetched'
            ], 409);
        }
        if (!empty($lesson_plan = TeacherLessonPlan::find($lesson_plan->id))) {
            $lesson_plan->approval_status = 2;
            $lesson_plan->save();
        }
        return response([
            'status' => 'success',
            'message' => 'Lesson plan declined successfully',
            'data' => $lesson_plan
        ], 200);
    }
}
