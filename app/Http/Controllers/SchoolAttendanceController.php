<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClassAttendanceRequest;
use App\Models\ClassAttendanceGroup;
use App\Models\ClassAttendanceRegister;
use App\Models\SchoolSession;
use App\Models\SchoolStudent;
use App\Models\SchoolTerm;
use App\Models\SubjectAttendanceGroup;
use App\Models\SubjectAttendanceRegister;
use Illuminate\Support\Str;

class SchoolAttendanceController extends Controller
{
    private $user;

    public function __construct(){
        $this->middleware('auth:user-api');
        $this->user = AuthController::user();
    }

    public function index($type){
        $start_date = !empty($_GET['start_date']) ? (string)$_GET['start_date'] : "";
        $end_date = !empty($_GET['end_date']) ? (string)$_GET['end_date'] : "";
        $limit = !empty($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $sort = !empty($_GET['sort']) ? (string)$_GET['sort'] : "desc";

        if ($type=='class') {
            $attendances = ClassAttendanceGroup::where('school_id', $this->user->school_id)->where('school_location_id', $this->user->school_location_id);

            $current_session = SchoolSession::where('school_location_id', $this->user->school_location_id)->where('status', 2)->where('start_date', '<=', date('Y-m-d'))->where('end_date', '>=', date('Y-m-d'))->first();
            if(!empty($current_session)){
                $attendances = $attendances->where('session_id', $current_session->id);
                $current_term = SchoolTerm::where('school_location_id', $this->user->school_location_id)->where('school_session_id', $current_session->id)->where('status', 2)->where('start_date', '<=', date('Y-m-d'))->where('end_date', '>=', date('Y-m-d'))->first();
                if(!empty($current_term)){
                    $attendances = $attendances->where('term_id', $current_term->id);
                }
            }
            
            if(!empty($start_date)){
                $start_date = trim($start_date)." 00:00:00";
                $attendances = $attendances->where('attendance_date', '>=', $start_date);
            }
            if(!empty($end_date)){
                $end_date = trim($end_date)." 23:59:59";
                $attendances = $attendances->where('attendance_date', '<=', $end_date);
            }
            $attendances = $attendances->orderBy('attendance_date', $sort);
            if($attendances->count() > 0){
                $attendances = $attendances->paginate($limit);
                foreach($attendances as $attendance) {
                    $attendance->students = ClassAttendanceRegister::where('class_attendance_group_id', $attendance->id)->get();
                    $attendance->students_present = ClassAttendanceRegister::where('class_attendance_group_id', $attendance->id)->where('attendance_status', true)->count();
                    $attendance->students_absent = ClassAttendanceRegister::where('class_attendance_group_id', $attendance->id)->where('attendance_status', false)->count();
                    $attendance->total_students = ClassAttendanceRegister::where('class_attendance_group_id', $attendance->id)->count();
                }
                return response([
                    'status' => 'success',
                    'message' => 'Attendance History fetched successfully',
                    'data' => $attendances
                ], 200);
            } else {
                return response([
                    'status' => 'failed',
                    'message' => 'Attendance History was fetched',
                    'data' => null
                ], 200);
            }
        } else {
            $attendances = SubjectAttendanceGroup::where('school_id', $this->user->school_id)->where('school_location_id', $this->user->school_location_id);

            $current_session = SchoolSession::where('school_location_id', $this->user->school_location_id)->where('status', 2)->where('start_date', '<=', date('Y-m-d'))->where('end_date', '>=', date('Y-m-d'))->first();
            if(!empty($current_session)){
                $attendances = $attendances->where('session_id', $current_session->id);
                $current_term = SchoolTerm::where('school_location_id', $this->user->school_location_id)->where('school_session_id', $current_session->id)->where('status', 2)->where('start_date', '<=', date('Y-m-d'))->where('end_date', '>=', date('Y-m-d'))->first();
                if(!empty($current_term)){
                    $attendances = $attendances->where('term_id', $current_term->id);
                }
            }
            
            if(!empty($start_date)){
                $start_date = trim($start_date)." 00:00:00";
                $attendances = $attendances->where('attendance_date', '>=', $start_date);
            }
            if(!empty($end_date)){
                $end_date = trim($end_date)." 23:59:59";
                $attendances = $attendances->where('attendance_date', '<=', $end_date);
            }
            $attendances = $attendances->orderBy('attendance_date', $sort);
            if($attendances->count() > 0){
                $attendances = $attendances->paginate($limit);
                foreach($attendances as $attendance) {
                    $attendance->students = SubjectAttendanceRegister::where('subject_attendance_group_id', $attendance->id)->get();
                    $attendance->students_present = SubjectAttendanceRegister::where('subject_attendance_group_id', $attendance->id)->where('attendance_status', true)->count();
                    $attendance->students_absent = SubjectAttendanceRegister::where('subject_attendance_group_id', $attendance->id)->where('attendance_status', false)->count();
                    $attendance->total_students = SubjectAttendanceRegister::where('subject_attendance_group_id', $attendance->id)->count();
                }
                return response([
                    'status' => 'success',
                    'message' => 'Attendance History fetched successfully',
                    'data' => $attendances
                ], 200);
            } else {
                return response([
                    'status' => 'failed',
                    'message' => 'No Attendance History was fetched',
                    'data' => null
                ], 200);
            }
        }
        
        
    }

    public function attendance_by_sub_class($sub_class_id){
        $start_date = !empty($_GET['start_date']) ? (string)$_GET['start_date'] : "";
        $end_date = !empty($_GET['end_date']) ? (string)$_GET['end_date'] : "";
        $limit = !empty($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $sort = !empty($_GET['sort']) ? (string)$_GET['sort'] : "desc";
        
        $attendances = ClassAttendanceGroup::where('school_id', $this->user->school_id)->where('school_location_id', $this->user->school_location_id)->where('sub_class_id', $sub_class_id);

        $current_session = SchoolSession::where('school_location_id', $this->user->school_location_id)->where('status', 2)->where('start_date', '<=', date('Y-m-d'))->where('end_date', '>=', date('Y-m-d'))->first();
        if(!empty($current_session)){
            $attendances = $attendances->where('session_id', $current_session->id);
            $current_term = SchoolTerm::where('school_location_id', $this->user->school_location_id)->where('school_session_id', $current_session->id)->where('status', 2)->where('start_date', '<=', date('Y-m-d'))->where('end_date', '>=', date('Y-m-d'))->first();
            if(!empty($current_term)){
                $attendances = $attendances->where('term_id', $current_term->id);
            }
        }
        
        if(!empty($start_date)){
            $start_date = trim($start_date)." 00:00:00";
            $attendances = $attendances->where('attendance_date', '>=', $start_date);
        }
        if(!empty($end_date)){
            $end_date = trim($end_date)." 23:59:59";
            $attendances = $attendances->where('attendance_date', '<=', $end_date);
        }
        $attendances = $attendances->orderBy('attendance_date', $sort);
        if($attendances->count() > 0){
            $attendances = $attendances->paginate($limit);
            foreach($attendances as $attendance) {
                $attendance->students = ClassAttendanceRegister::where('class_attendance_group_id', $attendance->id)->get();
                $attendance->students_present = ClassAttendanceRegister::where('class_attendance_group_id', $attendance->id)->where('attendance_status', true)->count();
                $attendance->students_absent = ClassAttendanceRegister::where('class_attendance_group_id', $attendance->id)->where('attendance_status', false)->count();
                $attendance->total_students = ClassAttendanceRegister::where('class_attendance_group_id', $attendance->id)->count();
            }
            return response([
                'status' => 'success',
                'message' => 'Class Attendance History fetched successfully',
                'data' => $attendances
            ], 200);
        } else {
            return response([
                'status' => 'failed',
                'message' => 'No Class Attendance History was fetched',
                'data' => null
            ], 200);
        }
    }

    public function attendance_by_subject($subject_id){
        $start_date = !empty($_GET['start_date']) ? (string)$_GET['start_date'] : "";
        $end_date = !empty($_GET['end_date']) ? (string)$_GET['end_date'] : "";
        $limit = !empty($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $sort = !empty($_GET['sort']) ? (string)$_GET['sort'] : "desc";

        $attendances = SubjectAttendanceGroup::where('school_id', $this->user->school_id)->where('school_location_id', $this->user->school_location_id)->where('subject_id', $subject_id);

        $current_session = SchoolSession::where('school_location_id', $this->user->school_location_id)->where('status', 2)->where('start_date', '<=', date('Y-m-d'))->where('end_date', '>=', date('Y-m-d'))->first();
        if(!empty($current_session)){
            $attendances = $attendances->where('session_id', $current_session->id);
            $current_term = SchoolTerm::where('school_location_id', $this->user->school_location_id)->where('school_session_id', $current_session->id)->where('status', 2)->where('start_date', '<=', date('Y-m-d'))->where('end_date', '>=', date('Y-m-d'))->first();
            if(!empty($current_term)){
                $attendances = $attendances->where('term_id', $current_term->id);
            }
        }
        if(!empty($start_date)){
            $start_date = trim($start_date)." 00:00:00";
            $attendances = $attendances->where('attendance_date', '>=', $start_date);
        }
        if(!empty($end_date)){
            $end_date = trim($end_date)." 23:59:59";
            $attendances = $attendances->where('attendance_date', '<=', $end_date);
        }
        $attendances = $attendances->orderBy('attendance_date', $sort);
        if($attendances->count() > 0){
            $attendances = $attendances->paginate($limit);
            foreach($attendances as $attendance) {
                $attendance->students = SubjectAttendanceRegister::where('subject_attendance_group_id', $attendance->id)->get();
                $attendance->students_present = SubjectAttendanceRegister::where('subject_attendance_group_id', $attendance->id)->where('attendance_status', true)->count();
                $attendance->students_absent = SubjectAttendanceRegister::where('subject_attendance_group_id', $attendance->id)->where('attendance_status', false)->count();
                $attendance->total_students = SubjectAttendanceRegister::where('subject_attendance_group_id', $attendance->id)->count();
            }
            return response([
                'status' => 'success',
                'message' => 'Subject Attendance History fetched successfully',
                'data' => $attendances
            ], 200);
        } else {
            return response([
                'status' => 'failed',
                'message' => 'No Subject Attendance History was fetched',
                'data' => null
            ], 200);
        }
    }

    public function attendance_by_student($student_id, $type){
        $start_date = !empty($_GET['start_date']) ? (string)$_GET['start_date'] : "";
        $end_date = !empty($_GET['end_date']) ? (string)$_GET['end_date'] : "";
        $limit = !empty($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $sort = !empty($_GET['sort']) ? (string)$_GET['sort'] : "desc";

        if ($type == 'subject') {
            $attendances = SubjectAttendanceRegister::where('school_id', $this->user->school_id)->where('school_location_id', $this->user->school_location_id)->where('student_id', $student_id);

            $current_session = SchoolSession::where('school_location_id', $this->user->school_location_id)->where('status', 2)->where('start_date', '<=', date('Y-m-d'))->where('end_date', '>=', date('Y-m-d'))->first();
            if(!empty($current_session)){
                $attendances = $attendances->where('session_id', $current_session->id);
                $current_term = SchoolTerm::where('school_location_id', $this->user->school_location_id)->where('school_session_id', $current_session->id)->where('status', 2)->where('start_date', '<=', date('Y-m-d'))->where('end_date', '>=', date('Y-m-d'))->first();
                if(!empty($current_term)){
                    $attendances = $attendances->where('term_id', $current_term->id);
                }
            }
            if(!empty($start_date)){
                $start_date = trim($start_date)." 00:00:00";
                $attendances = $attendances->where('attendance_date', '>=', $start_date);
            }
            if(!empty($end_date)){
                $end_date = trim($end_date)." 23:59:59";
                $attendances = $attendances->where('attendance_date', '<=', $end_date);
            }
            $attendances = $attendances->orderBy('attendance_date', $sort);
            if($attendances->count() > 0){
                $attendances = $attendances->paginate($limit);
                return response([
                    'status' => 'success',
                    'message' => 'Student Attendance History fetched successfully',
                    'data' => $attendances
                ], 200);
            } else {
                return response([
                    'status' => 'failed',
                    'message' => 'No Student Attendance History was fetched',
                    'data' => null
                ], 200);
            }
        } else {
            $attendances = ClassAttendanceRegister::where('school_id', $this->user->school_id)->where('school_location_id', $this->user->school_location_id)->where('student_id', $student_id);

            $current_session = SchoolSession::where('school_location_id', $this->user->school_location_id)->where('status', 2)->where('start_date', '<=', date('Y-m-d'))->where('end_date', '>=', date('Y-m-d'))->first();
            if(!empty($current_session)){
                $attendances = $attendances->where('session_id', $current_session->id);
                $current_term = SchoolTerm::where('school_location_id', $this->user->school_location_id)->where('school_session_id', $current_session->id)->where('status', 2)->where('start_date', '<=', date('Y-m-d'))->where('end_date', '>=', date('Y-m-d'))->first();
                if(!empty($current_term)){
                    $attendances = $attendances->where('term_id', $current_term->id);
                }
            }
            if(!empty($start_date)){
                $start_date = trim($start_date)." 00:00:00";
                $attendances = $attendances->where('attendance_date', '>=', $start_date);
            }
            if(!empty($end_date)){
                $end_date = trim($end_date)." 23:59:59";
                $attendances = $attendances->where('attendance_date', '<=', $end_date);
            }
            $attendances = $attendances->orderBy('attendance_date', $sort);
            if($attendances->count() > 0){
                $attendances = $attendances->paginate($limit);
                return response([
                    'status' => 'success',
                    'message' => 'Student Attendance History fetched successfully',
                    'data' => $attendances
                ], 200);
            } else {
                return response([
                    'status' => 'failed',
                    'message' => 'No Student Attendance History was fetched',
                    'data' => null
                ], 200);
            }
        }
    }

    public function store_class_attendance (StoreClassAttendanceRequest $request) {
        if(!empty($time_table_group = ClassAttendanceGroup::where('school_id', $this->user->school_id)->where('attendance_date', $request->attendance_date)->where('school_location_id', $this->user->school_location_id)->where('sub_class_id', $request->sub_class_id)->first())){
            $all = $request->except(['students']);
            $all['school_id'] = $this->user->school_id;
            $all['school_location_id'] = $this->user->school_location_id;
            $all['uuid'] = Str::uuid().'-'.time();
            if ($time_table_group->update([
                'students_present' => $request->students_present,
                'students_absent' => $request->students_present, 
            ])) {
                $students = $request->students;
                foreach($students as $student) {
                    if (!empty($attendance_student = ClassAttendanceRegister::where('school_id', $this->user->school_id)->where('attendance_date', $request->attendance_date)->where('class_attendance_group_id', $time_table_group->id)->where('student_id', $student['student_id'])->get())) {
                        $attendance_student->update([
                            'attendance_status' => $student['attendance_status'],
                        ]);
                    }
                }
                return response([
                    'status' => 'success',
                    'message' => 'Class attendance updated successfully'
                ], 404);
            } else {
                return response([
                    'status' => 'failed',
                    'message' => 'Class attendance update failed'
                ], 409);
            }
            
        } else {
            $all = $request->except(['students']);
            $all['school_id'] = $this->user->school_id;
            $all['school_location_id'] = $this->user->school_location_id;
            $all['uuid'] = Str::uuid().'-'.time();
            if (!empty($current_session = SchoolSession::where('school_location_id', $this->user->school_location_id)->where('status', 2)->where('start_date', '<=', date('Y-m-d'))->where('end_date', '>=', date('Y-m-d'))->first())){
                if (!empty($current_term = SchoolTerm::where('school_location_id', $this->user->school_location_id)->where('school_session_id', $current_session->id)->where('status', 2)->where('start_date', '<=', date('Y-m-d'))->where('end_date', '>=', date('Y-m-d'))->first())) {
                    $all['session_id'] = $current_session->id;
                    $all['term_id'] = $current_term->id;
                    if ($time_table_group = ClassAttendanceGroup::create($all)) {
                        $students = $request->students;
        
                        foreach($students as $student) {
                            $school_student = SchoolStudent::find($student['student_id']);
                            ClassAttendanceRegister::create([
                                'uuid' => Str::uuid().'-'.time(),
                                'school_id' => $time_table_group->school_id,
                                'school_location_id' => $time_table_group->school_location_id,
                                'main_class_id' => $time_table_group->main_class_id,
                                'sub_class_id' => $time_table_group->sub_class_id,
                                'session_id' => $time_table_group->session_id,
                                'term_id' => $time_table_group->term_id,
                                'class_attendance_group_id' => $time_table_group->id,
                                'student_id' => $school_student->id,
                                'first_name' => $school_student->first_name,
                                'last_name' => $school_student->last_name,
                                'enrolment_id' => $school_student->registration_id,
                                'attendance_date' => $student['attendance_status'],
                                'attendance_status' => $student['attendance_status'],
                            ]);
                        }
                        
                        return response([
                            'status' => 'success',
                            'message' => 'Time-table updated successfully'
                        ], 200);
                    } else {
                        return response([
                            'status' => 'failed',
                            'message' => 'Class attendance creation failed'
                        ], 409);
                    }
                } else {
                    return response([
                        'status' => 'failed',
                        'message' => 'Your school must be in a term to mark class attendance'
                    ], 409); 
                }
                    
            } else {
                return response([
                    'status' => 'failed',
                    'message' => 'Your school must be in session to mark class attendance'
                ], 409); 
            }
            
        }
    }

    public function store_subject_attendance (StoreClassAttendanceRequest $request) {
        if(!empty($time_table_group = SubjectAttendanceGroup::where('school_id', $this->user->school_id)->where('attendance_date', $request->attendance_date)->where('school_location_id', $this->user->school_location_id)->where('subject_id', $request->subject_id)->first())){
            $all = $request->except(['students']);
            $all['school_id'] = $this->user->school_id;
            $all['school_location_id'] = $this->user->school_location_id;
            $all['uuid'] = Str::uuid().'-'.time();
            if ($time_table_group->update([
                'students_present' => $request->students_present,
                'students_absent' => $request->students_absent, 
            ])) {
                $students = $request->students;
                foreach($students as $student) {
                    if (!empty($attendance_student = SubjectAttendanceRegister::where('school_id', $this->user->school_id)->where('attendance_date', $request->attendance_date)->where('subject_attendance_group_id', $time_table_group->id)->where('student_id', $student['student_id'])->get())) {
                        $attendance_student->update([
                            'attendance_status' => $student['attendance_status'],
                        ]);
                    }
                }
                return response([
                    'status' => 'success',
                    'message' => 'Subject attendance updated successfully'
                ], 200);
            } else {
                return response([
                    'status' => 'failed',
                    'message' => 'Subject attendance update failed'
                ], 409);
            }
            
        } else {
            $all = $request->except(['students']);
            $all['school_id'] = $this->user->school_id;
            $all['school_location_id'] = $this->user->school_location_id;
            $all['uuid'] = Str::uuid().'-'.time();
            if (!empty($current_session = SchoolSession::where('school_location_id', $this->user->school_location_id)->where('status', 2)->where('start_date', '<=', date('Y-m-d'))->where('end_date', '>=', date('Y-m-d'))->first())){
                if (!empty($current_term = SchoolTerm::where('school_location_id', $this->user->school_location_id)->where('school_session_id', $current_session->id)->where('status', 2)->where('start_date', '<=', date('Y-m-d'))->where('end_date', '>=', date('Y-m-d'))->first())) {
                    $all['session_id'] = $current_session->id;
                    $all['term_id'] = $current_term->id;
                    if ($time_table_group = SubjectAttendanceGroup::create($all)) {
                        $students = $request->students;

                        foreach($students as $student) {
                            $school_student = SchoolStudent::find($student['student_id']);
                            SubjectAttendanceRegister::create([
                                'uuid' => Str::uuid().'-'.time(),
                                'school_id' => $time_table_group->school_id,
                                'school_location_id' => $time_table_group->school_location_id,
                                'main_class_id' => $time_table_group->main_class_id,
                                'sub_class_id' => $time_table_group->sub_class_id,
                                'session_id' => $time_table_group->session_id,
                                'term_id' => $time_table_group->term_id,
                                'subject_id' => $time_table_group->subject_id,
                                'subject_attendance_group_id' => $time_table_group->id,
                                'student_id' => $school_student->id,
                                'first_name' => $school_student->first_name,
                                'last_name' => $school_student->last_name,
                                'enrolment_id' => $school_student->registration_id,
                                'attendance_date' => $student['attendance_status'],
                                'attendance_status' => $student['attendance_status'],
                            ]);
                        }
                        
                        return response([
                            'status' => 'success',
                            'message' => 'Subject attendance saved successfully'
                        ], 200);
                    } else {
                        return response([
                            'status' => 'failed',
                            'message' => 'Subject attendance creation failed'
                        ], 409);
                    }
                } else {
                    return response([
                        'status' => 'failed',
                        'message' => 'Your school must be in a term to mark class attendance'
                    ], 409); 
                }
            } else {
                return response([
                    'status' => 'failed',
                    'message' => 'Your school must be in session to mark class attendance'
                ], 409); 
            }
        }
    }

    public function show($uuid, $type){
        if ($type == 'subject') {
            if(empty($time_table_group = SubjectAttendanceGroup::where('school_id', $this->user->school_id)->where('uuid', $uuid)->where('school_location_id', $this->user->school_location_id)->first())){
                return response([
                    'status' => 'failed',
                    'message' => 'No time-table was fetched'
                ], 404);
            }
            $time_table_group->students = SubjectAttendanceRegister::where('subject_attendance_group_id', $time_table_group->id)->get();
            return response([
                'status' => 'success',
                'message' => 'Time-table fetched successfully',
                'data' => $time_table_group
            ], 200);
            
        } else {
            if(empty($time_table_group = ClassAttendanceGroup::where('school_id', $this->user->school_id)->where('uuid', $uuid)->where('school_location_id', $this->user->school_location_id)->first())){
                return response([
                    'status' => 'failed',
                    'message' => 'No time-table was fetched'
                ], 404);
            }
            $time_table_group->students = ClassAttendanceRegister::where('class_attendance_group_id', $time_table_group->id)->get();
            return response([
                'status' => 'success',
                'message' => 'Time-table fetched successfully',
                'data' => $time_table_group
            ], 200);
        }
        
    }
}
