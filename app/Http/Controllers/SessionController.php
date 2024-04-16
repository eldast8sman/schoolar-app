<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSesssionRequest;
use App\Models\School;
use App\Models\SchoolLocation;
use App\Models\SchoolSession;
use App\Models\SchoolTerm;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SessionController extends Controller
{
    private $user;

    public function __construct()
    {
        $this->middleware('auth:user-api');
        $this->user = AuthController::user();
    }

    public static function sch_session(SchoolSession $session) : SchoolSession
    {
        $terms = SchoolTerm::where('school_session_id', $session->id)->get();
        $session->terms = $terms;
        return $session;
    }

    public function index(){
        $search = !empty($_GET['search']) ? (string)$_GET['search'] : "";
        $limit = !empty($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $filter = isset($_GET['filter']) ? (int)$_GET['filter'] : NULL;
        
        $sch_sessions = SchoolSession::where('school_id', $this->user->school_id)->where('school_location_id', $this->user->school_location_id);
        if(!empty($search)){
            $sch_sessions = $sch_sessions->where('session_name', 'like', '%'.$search.'%');
        }
        if($filter !== NULL){
            $sch_sessions = $sch_sessions->where('status', $filter);
        }
        if($filter != 3){
            $sch_sessions = $sch_sessions->where('status', '<>', 3);
        }
        if($sch_sessions->count() < 1){
            return response([
                'status' => 'failed',
                'message' => 'No School Session has been added yet',
                'data' => []
            ], 200);
        }

        $sch_sessions = $sch_sessions->paginate($limit);
        foreach($sch_sessions as $session){
            $session = self::sch_session($session);
        }

        return response([
            'status' => 'success',
            'message' => 'Sessions fetched successfully',
            'data' => $sch_sessions
        ], 200);
    }

    public function store(StoreSesssionRequest $request){
        $errors = [];
        $overlap_message = "This Session's timeline is overlapping another of your Session!";
        if(SchoolSession::where('school_location_id', $this->user->school_location_id)->where('session_name', $request->session_name)->count() > 0){
            $errors[] = "Session Name has already been taken!";
        }
        if(SchoolSession::where('school_location_id', $this->user->school_location_id)->where('start_date', '<=', $request->start_date)->where('end_date', '>=', $request->start_date)->count() > 0){
            if(!in_array($overlap_message, $errors)){
                $errors[] = $overlap_message;
            }
        }
        if(SchoolSession::where('school_location_id', $this->user->school_location_id)->where('start_date', '<=', $request->end_date)->where('end_date', '>=', $request->end_date)->count() > 0){
            if(!in_array($overlap_message, $errors)){
                $errors[] = $overlap_message;
            }
        }
        if(SchoolSession::where('school_location_id', $this->user->school_location_id)->where('start_date', '>', $request->start_date)->where('end_date', '<', $request->end_date)->count() > 0){
            if(!in_array($overlap_message, $errors)){
                $errors[] = $overlap_message;
            }
        }
        if($request->start_date > $request->end_date){
            $errors[] = "Start Date must be earlier than End date";
        }

        if(!empty($errors)){
            return response([
                'status' => 'failed',
                'message' => join(' ', $errors)
            ], 409);
        }

        $uuid = "";
        for($i=1; $i<=40; $i++){
            $s_uuid = Str::uuid();
            if(empty(SchoolSession::where('uuid', $s_uuid)->first())){
                $uuid = $s_uuid;
                break;
            } else {
                continue;
            }
        }
        if(empty($uuid)){
            return response([
                'status' => 'failed',
                'message' => 'Could not successfully create Session! Please try again'
            ], 500);
        }

        $all = $request->except(['load_default']);
        $all['uuid'] = $uuid;
        $all['school_id'] = $this->user->school_id;
        $all['school_location_id'] = $this->user->school_location_id;
        if(!$sch_session = SchoolSession::create($all)){
            return response([
                'status' => 'failed',
                'message' => 'School Session creation failed! Please try again later'
            ], 500);
        }

        if($request->load_default == true){
            $school = School::find($this->user->school_id);
            $school_location = SchoolLocation::find($this->user->school_id);
            if((strtolower($school->country) == 'nigeria') and ((strtolower($school_location->location_type) == 'primary') or (strtolower($school_location->location_type) == 'secondary'))){
                $time = strtotime($sch_session->start_date);
                
                $term_start = Carbon::createFromDate(intval(date('Y', $time)), intval(date('m', $time)), intval(date('d', $time)));

                $term_durations = [
                    [
                        'name' => 'First Term',
                        'weeks' => 16,
                        'position' => 1
                    ],
                    [
                        'name' => 'Second Term',
                        'weeks' => 16,
                        'position' => 2
                    ],
                    [
                        'name' => 'Third Term',
                        'weeks' => 19,
                        'position' => 3
                    ]
                ];

                foreach($term_durations as $duration){
                    $continue = true;
                    if(!empty($sch_session->end_date) and ($term_start > $sch_session->end_date)){
                        $continue = false;
                    }
                    if($continue){
                        $start_date = $term_start->copy();
                        $end_date = $start_date->copy()->addWeeks($duration['weeks'])->subDays(1);
                        $term_end = ($end_date <= $sch_session->end_date) ? $end_date : $sch_session->end_date;

                        $uuid = "";
                        for($i=1; $i<=40; $i++){
                            $t_uuid = Str::uuid();
                            if(empty(SchoolTerm::where('uuid', $t_uuid)->where('school_location_id', $this->user->school_location_id)->first())){
                                $uuid = $t_uuid;
                                break;
                            } else {
                                continue;
                            }
                        }
                        if(!empty($uuid)){
                            SchoolTerm::create([
                                'uuid' => $uuid,
                                'school_id' => $school->id,
                                'school_location_id' => $school_location->id,
                                'school_session_id' => $sch_session->id,
                                'term_name' => $duration['name'],
                                'start_date' => $start_date,
                                'end_date' => $term_end,
                                'position' => $duration['position'],
                                'status' => 0
                            ]);
                        }
                    }
                }
            }
        }

        return response([
            'status' => 'success',
            'message' => 'Session added successfully',
            'data' => self::sch_session($sch_session)
        ], 200);
    }

    public function store_term(){
        
    }
}
