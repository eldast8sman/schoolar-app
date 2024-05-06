<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTimeTableConfigurationRequest;
use App\Http\Requests\StoreTimeTableRequest;
use App\Models\ClassTimeTable;
use App\Models\TimeTableBreakTime;
use App\Models\TimeTableClassGroup;
use App\Models\TimeTableConfiguration;
use App\Models\TimeTableLessonPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SchoolTimeTableController extends Controller
{

    private $user;

    public function __construct()
    {
        $this->middleware('auth:user-api');
        $this->user = AuthController::user();
    }

    public function time_table_configuration(){
        if(empty($time_table = TimeTableConfiguration::where('school_location_id', $this->user->school_location_id)->first())){
            return response([
                'status' => 'failed',
                'message' => 'No time-table configuration was fetched'
            ], 404);
        }
        if ($time_table->configure_break_time) {
            if (!empty($breaks = TimeTableBreakTime::where('time_table_configuration_id', $time_table->id)->get())) {
                foreach($breaks as $break) {
                    $break->break_days = json_decode($break->break_days);
                }
            }
            $time_table->breaks = $breaks;
        }
        if ($time_table->configure_lesson) {
            if (!empty($lesson_details = TimeTableLessonPlan::find($time_table->time_table_lesson_id))) {
                $lesson_details->lesson_days = json_decode($lesson_details->lesson_days);
                $time_table->lesson_details = $lesson_details;
            }
        }
        
        return response([
            'status' => 'success',
            'message' => 'Time-table configuration fetched successfully',
            'data' => $time_table
        ], 200);
    }

    public function store_configuration(StoreTimeTableConfigurationRequest $request){
        if(!empty($configuration = TimeTableConfiguration::where('school_location_id', $this->user->school_location_id)->first())){
            $all = $request->except(['breaks', 'lesson_details']);
            if($configuration->update($all)){
                if ($request->configure_break_time) {
                    if(!empty($breaks = $request->breaks)) {
                        foreach($breaks as $break) {
                            if (!empty($break["break_start_time"]) and !empty($break["break_name"]) and !empty($break["break_end_time"]) and !empty($break["break_days"])) {
                                if (!empty($class_time_table = TimeTableBreakTime::where('time_table_configuration_id', $configuration->id)->where('break_name', $break['break_name']))) {
                                    $class_time_table->update([
                                        'break_name' => $break["break_name"],
                                        'break_start_time' => $break["break_start_time"],
                                        'break_end_time' => $break["break_end_time"],
                                        'break_days' => json_encode($break["break_days"]),
                                    ]);
                                } else {
                                    TimeTableBreakTime::create([
                                        'uuid' => Str::uuid().'-'.time(),
                                        'school_id' => $this->user->school_id,
                                        'school_location_id' => $this->user->school_location_id,
                                        'time_table_configuration_id' => $configuration->id,
                                        'break_name' => $break["break_name"],
                                        'break_start_time' => $break["break_start_time"],
                                        'break_end_time' => $break["break_end_time"],
                                        'break_days' => json_encode($break["break_days"]),
                                    ]);
                                }
                            }
                        }
                    }
                }

                if ($request->configure_lesson) {
                    if(!empty($lesson_details = $request->lesson_details)) {
                        if (!empty($lesson_plans = TimeTableLessonPlan::where('time_table_configuration_id', $configuration->id)->first())) {
                            $lesson_plans->update([
                                'lesson_start_time' => $lesson_details["lesson_start_time"],
                                'lesson_end_time' => $lesson_details["lesson_end_time"],
                                'lesson_days' => json_encode($lesson_details["lesson_days"]),
                            ]);
                        }
                        
                    }
                }

                return response([
                    'status' => 'success',
                    'message' => 'Time-table configuration successfully created',
                    'data' => $configuration
                ], 200);
            } else {
                return response([
                    'status' => 'failed',
                    'message' => 'Time-table configuration update failed'
                ], 409);
            }
        } else {
            $all = $request->except(['breaks', 'lesson_details']);
            $all['school_id'] = $this->user->school_id;
            $all['school_location_id'] = $this->user->school_location_id;
            $all['uuid'] = Str::uuid().'-'.time();
    
            if($configuration = TimeTableConfiguration::create([
                'uuid' => Str::uuid().'-'.time(),
                'school_id' => $this->user->school_id,
                'school_location_id' => $this->user->school_id,
                'assembly_start_time' => $request->assembly_start_time,
                'assembly_end_time' => $request->assembly_end_time,
                'lecture_start_time' => $request->lecture_start_time,
                'lecture_end_time' => $request->lecture_end_time,
                'configure_break_time' => $request->configure_break_time,
                'configure_lesson' => $request->configure_lesson,
                'time_table_lesson_id' => 0,
            ])){
                if ($request->configure_break_time) {
                    if(!empty($breaks = $request->breaks)) {
                        foreach($breaks as $break) {
                            if (!empty($break["break_start_time"]) and !empty($break["break_name"]) and !empty($break["break_end_time"]) and !empty($break["break_days"])) {
                                TimeTableBreakTime::create([
                                    'uuid' => Str::uuid().'-'.time(),
                                    'school_id' => $this->user->school_id,
                                    'school_location_id' => $this->user->school_location_id,
                                    'time_table_configuration_id' => $configuration->id,
                                    'break_name' => $break["break_name"],
                                    'break_start_time' => $break["break_start_time"],
                                    'break_end_time' => $break["break_end_time"],
                                    'break_days' => json_encode($break["break_days"]),
                                ]);
                            }
                        }
                    }
                }
    
                if ($request->configure_lesson) {
                    if(!empty($lesson_details = $request->lesson_details)) {
                        if (!empty($lesson_plan_saved = TimeTableLessonPlan::create([
                            'uuid' => Str::uuid().'-'.time(),
                            'school_id' => $this->user->school_id,
                            'school_location_id' => $this->user->school_location_id,
                            'time_table_configuration_id' => $configuration->id,
                            'lesson_start_time' => !empty($lesson_details["lesson_start_time"]) ? $lesson_details["lesson_start_time"] : '',
                            'lesson_end_time' => !empty($lesson_details["lesson_end_time"]) ? $lesson_details["lesson_end_time"] : '',
                            'lesson_days' => json_encode($lesson_details["lesson_days"]),
                        ]))) {
                            $configuration->time_table_lesson_id = $lesson_plan_saved->id;
                            $configuration->save();
                        }
                    }
                }
    
                return response([
                    'status' => 'success',
                    'message' => 'Time-table configuration successfully created',
                    'data' => $configuration
                ], 200);
            } else {
                return response([
                    'status' => 'failed',
                    'message' => 'Time-table configuration creation failed'
                ], 409);
            }
        }
    }

    public function store_time_table(StoreTimeTableRequest $request){
        if(!empty($time_table_class_group = TimeTableClassGroup::where('school_location_id', $this->user->school_location_id)->where('sub_class_id', $request->sub_class_id)->first())){
    
            $all = $request->except(['days']);
    
            if($time_table_class_group->update($all)){
                if(!empty($days = $request->days)) {
                    foreach($days as $day) {
                        if (!empty($class_time_table = ClassTimeTable::where('time_table_class_group_id', $time_table_class_group->id)->where('day', $day["day"]))) {
                            $class_time_table->update([
                                'time_breakdown' => json_encode($day["time_breakdown"]),
                            ]);
                        } else {
                            ClassTimeTable::create([
                                'uuid' => Str::uuid().'-'.time(),
                                'school_id' => $this->user->school_id,
                                'school_location_id' => $this->user->school_location_id,
                                'time_table_class_group_id' => $time_table_class_group->id,
                                'day' => $day["day"],
                                'time_breakdown' => json_encode($day["time_breakdown"]),
                            ]);
                        }
                        
                    }
                }

                return response([
                    'status' => 'success',
                    'message' => 'Time-table successfully updated',
                    'data' => $time_table_class_group
                ], 200);
            } else {
                return response([
                    'status' => 'failed',
                    'message' => 'Time-table configuration creation failed'
                ], 409);
            }
        } else {
            $all = $request->except(["days"]);
            $all['school_id'] = $this->user->school_id;
            $all['school_location_id'] = $this->user->school_location_id;
            $all['uuid'] = Str::uuid().'-'.time();
    
            if($time_table_class_group = TimeTableClassGroup::create($all)){
                if(!empty($days = $request->days)) {
                    foreach($days as $day) {
                        ClassTimeTable::create([
                            'uuid' => Str::uuid().'-'.time(),
                            'school_id' => $this->user->school_id,
                            'school_location_id' => $this->user->school_location_id,
                            'time_table_class_group_id' => $time_table_class_group->id,
                            'day' => $day["day"],
                            'time_breakdown' => json_encode($day["time_breakdown"] ? $day["time_breakdown"] : []),
                        ]);
                    }
                }
    
                return response([
                    'status' => 'success',
                    'message' => 'Time-table successfully created',
                    'data' => $time_table_class_group
                ], 200);
            } else {
                return response([
                    'status' => 'failed',
                    'message' => 'Time-table creation failed'
                ], 409);
            }
        }
    }

    public function time_table_by_sub_class ($sub_class_id ) {
        if(!empty($time_table = TimeTableClassGroup::where('school_location_id', $this->user->school_location_id)->where('sub_class_id', $sub_class_id)->first())){
            if (!empty($days = ClassTimeTable::where('time_table_class_group_id', $time_table->id)->get())) {
                foreach($days as $day) {
                    $day->time_breakdown = json_decode($day->time_breakdown);
                }
                $time_table->days = $days;
            } else {
                $time_table->days = [];
            }
            
            return response([
                'status' => 'success',
                'message' => 'Time-table fetched successfully',
                'data' => $time_table
            ], 200);
        } else {
            return response([
                'status' => 'failed',
                'message' => 'No time-table created for this class yet'
            ], 200);
        }
    }
}
