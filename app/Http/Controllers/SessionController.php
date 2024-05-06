<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSchoolTermRequest;
use App\Http\Requests\StoreSesssionRequest;
use App\Http\Requests\UpdateSessionRequest;
use App\Models\School;
use App\Models\SchoolLocation;
use App\Models\SchoolSession;
use App\Models\SchoolTerm;
use Carbon\Carbon;
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
        $terms = SchoolTerm::where('school_session_id', $session->id)->orderBy('position', 'asc')->get();
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
        $sch_sessions = $sch_sessions->orderBy('start_date', 'desc');

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

    public function terms_by_session($uuid){
        $search = !empty($_GET['search']) ? (string)$_GET['search'] : "";
        $limit = !empty($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $filter = isset($_GET['filter']) ? (int)$_GET['filter'] : NULL;

        if(empty($session = SchoolSession::where('uuid', $uuid)->where('school_location_id', $this->user->school_location_id)->first())){
            return response([
                'status' => 'failed',
                'message' => 'No School Session was found for this term'
            ], 200);
        }

        $sch_terms = SchoolTerm::where('school_id', $this->user->school_id)->where('school_location_id', $this->user->school_location_id)->where('school_session_id', $session->id);
        if(!empty($search)){
            $sch_terms = $sch_terms->where('term_name', 'like', '%'.$search.'%');
        }
        if($filter !== NULL){
            $sch_terms = $sch_terms->where('status', $filter);
        }
        if($filter != 3){
            $sch_terms = $sch_terms->where('status', '<>', 3);
        }
        if($sch_terms->count() < 1){
            return response([
                'status' => 'failed',
                'message' => 'No School Term has been added yet',
                'data' => []
            ], 200);
        }
        $sch_terms = $sch_terms->orderBy('start_date', 'desc');

        $sch_terms = $sch_terms->paginate($limit);

        return response([
            'status' => 'success',
            'message' => 'Terms fetched successfully',
            'data' => $sch_terms
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
            $school_location = SchoolLocation::find($this->user->school_location_id);
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
                        if($start_date > date('Y-m-d')){
                            $status = 0;
                        } elseif($end_date < date('Y-m-d')){
                            $status = 1;
                        } else {
                            $status = 2;
                        }
                        if(!empty($uuid)){
                            SchoolTerm::create([
                                'uuid' => $uuid,
                                'school_id' => $school->id,
                                'school_location_id' => $school_location->id,
                                'school_session_id' => $sch_session->id,
                                'term_name' => $duration['name'],
                                'start_date' => date('Y-m-d', strtotime($start_date)),
                                'end_date' => date('Y-m-d', strtotime($term_end)),
                                'position' => $duration['position'],
                                'status' => $status
                            ]);
                        }
                        $term_start = $end_date->addDays(1);
                    } else {
                        break;
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

    public function store_term(StoreSchoolTermRequest $request, $uuid){
        $errors = [];
        $overlap_message = "This Term's timeline is overlapping another of your Term!";
        $notInSession = "The School Term must be within the selected Session!";
        $sch_session = SchoolSession::where('uuid', $uuid)->where('school_location_id', $this->user->school_location_id)->first();
        if(empty($sch_session)){
            return response([
                'status' => 'failed',
                'message' => 'No School Session was fetched'
            ], 409);
        }
        if(SchoolTerm::where('school_session_id', $sch_session->id)->where('term_name', $request->term_name)->count() > 0){
            $errors[] = "Duplicate Term Name! ";
        }
        if(SchoolTerm::where('school_session_id', $sch_session->id)->where('start_date', '<=', $request->start_date)->where('end_date', '>=', $request->start_date)->count() > 0){
            if(!in_array($overlap_message, $errors)){
                $errors[] = $overlap_message;
            }
        }
        if(SchoolTerm::where('school_session_id', $sch_session->id)->where('start_date', '<=', $request->end_date)->where('end_date', '>=', $request->end_date)->count() > 0){
            if(!in_array($overlap_message, $errors)){
                $errors[] = $overlap_message;
            }
        }
        if(SchoolTerm::where('school_session_id', $sch_session->id)->where('position', $request->position)->count() > 0){
            $errors[] = "There is already a Term with this Position in the Session!";
        }
        if($request->start_date > $request->end_date){
            $errors[] = "Start Date must be earlier than End Date!";
        }
        if($request->start_date < $sch_session->start_date){
            if(!in_array($notInSession, $errors)){
                $errors[] = $notInSession;
            }
        }
        if($request->end_date > $sch_session->end_date){
            if(!in_array($notInSession, $errors)){
                $errors[] = $notInSession;
            }
        }

        if(!empty($errors)){
            return response([
                'status' => 'failed',
                'message' => join(' ', $errors)
            ], 409);
        }

        $all = $request->all();
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
        $all['uuid'] = $uuid;
        $all['school_id'] = $this->user->school_id;
        $all['school_location_id'] = $this->user->school_location_id;
        $all['school_session_id'] = $sch_session->id;
        if(!$term = SchoolTerm::create($all)){
            return response([
                'status' => 'failed',
                'message' => 'Could not add Term'
            ], 500);
        }

        return response([
            'status' => 'success',
            'message' => 'Term added successfully',
            'data' => $term
        ], 200);
    }

    public function show($uuid){
        if(empty($session = SchoolSession::where('uuid', $uuid)->where('school_location_id', $this->user->school_location_id)->first())){
            return response([
                'status' => 'failed',
                'message' => 'No School Session was fetched'
            ], 404);
        }

        return response([
            'status' => 'success',
            'message' => 'School Session fetched sucessfully',
            'data' => self::sch_session($session)
        ], 200);
    }

    public function show_term($uuid){
        if(empty($term = SchoolTerm::where('uuid', $uuid)->where('school_location_id', $this->user->school_location_id)->first())){
            return response([
                'status' => 'failed',
                'message' => 'No Term was fetched'
            ], 404);
        }

        return response([
            'status' => 'success',
            'message' => 'Term fetched sucessfully',
            'data' => $term
        ], 200);
    }

    public function update(UpdateSessionRequest $request, $uuid){
        $sch_session = SchoolSession::where('uuid', $uuid)->where('school_location_id', $this->user->school_location_id)->first();
        if(empty($sch_session)){
            return response([
                'status' => 'failed',
                'message' => 'No School Session was fetched'
            ], 409);
        }
        if($sch_session->status != 0){
            return response([
                'status' => 'failed',
                'message' => 'You can only update an Upcoming Session'
            ], 409);
        }

        $errors = [];
        $overlap_message = "This Session's timeline is overlapping another of your Session!";
        if(SchoolSession::where('school_location_id', $this->user->school_location_id)->where('session_name', $request->session_name)->where('id', '<>', $sch_session->id)->count() > 0){
            $errors[] = "Session Name has already been taken!";
        }
        if(SchoolSession::where('school_location_id', $this->user->school_location_id)->where('start_date', '<=', $request->start_date)->where('end_date', '>=', $request->start_date)->where('id', '<>', $sch_session->id)->count() > 0){
            if(!in_array($overlap_message, $errors)){
                $errors[] = $overlap_message;
            }
        }
        if(SchoolSession::where('school_location_id', $this->user->school_location_id)->where('start_date', '<=', $request->end_date)->where('end_date', '>=', $request->end_date)->where('id', '<>', $sch_session->id)->count() > 0){
            if(!in_array($overlap_message, $errors)){
                $errors[] = $overlap_message;
            }
        }
        if(SchoolSession::where('school_location_id', $this->user->school_location_id)->where('start_date', '>', $request->start_date)->where('end_date', '<', $request->end_date)->where('id', '<>', $sch_session->id)->count() > 0){
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

        $all = $request->all();
        if(!$sch_session->update($all)){
            return response([
                'status' => 'failed',
                'message' => 'Update Failed'
            ], 500);
        }

        $to_delete = SchoolTerm::where('school_location_id', $this->user->school_location_id)->where(function($query) use ($sch_session){
            $query->where('start_date', '<', $sch_session->start_date)
                ->orWhere('end_date', '>', $sch_session->end_date);
        });
        if($to_delete->count() > 0){
            foreach($to_delete->get() as $delete){
                $delete->delete();
            }
        }

        return response([
            'status' => 'success',
            'message' => 'School Session updated successfully',
            'data' => self::sch_session($sch_session)
        ], 200);
    }

    public function update_term(StoreSchoolTermRequest $request, $uuid){
        $term = SchoolTerm::where('uuid', $uuid)->where('school_location_id', $this->user->school_location_id)->first();
        if(empty($term)){
            return response([
                'status' => 'failed',
                'message' => 'No Term was fetched'
            ], 404);
        }
        if($term->status != 0){
            return response([
                'status' => 'failed',
                'message' => 'You can only update an Upcoming School Term'
            ], 409);
        }
        $sch_session = SchoolSession::find($term->school_session_id);

        $errors = [];
        $overlap_message = "This Term's timeline is overlapping another of your Term!";
        $notInSession = "The School Term must be within the selected Session!";

        if(SchoolTerm::where('school_session_id', $sch_session->id)->where('term_name', $request->term_name)->where('id', '<>', $term->id)->count() > 0){
            $errors[] = "Duplicate Term Name! ";
        }
        if(SchoolTerm::where('school_session_id', $sch_session->id)->where('start_date', '<=', $request->start_date)->where('end_date', '>=', $request->start_date)->where('id', '<>', $term->id)->count() > 0){
            if(!in_array($overlap_message, $errors)){
                $errors[] = $overlap_message;
            }
        }
        if(SchoolTerm::where('school_session_id', $sch_session->id)->where('start_date', '<=', $request->end_date)->where('end_date', '>=', $request->end_date)->where('id', '<>', $term->id)->count() > 0){
            if(!in_array($overlap_message, $errors)){
                $errors[] = $overlap_message;
            }
        }
        if(SchoolTerm::where('school_session_id', $sch_session->id)->where('position', $request->position)->where('id', '<>', $term->id)->count() > 0){
            $errors[] = "There is already a Term with this Position in the Session";
        }
        if($request->start_date > $request->end_date){
            $errors[] = "Start Date must be earlier than End Date!";
        }
        if($request->start_date < $sch_session->start_date){
            if(!in_array($notInSession, $errors)){
                $errors[] = $notInSession;
            }
        }
        if($request->end_date > $sch_session->end_date){
            if(!in_array($notInSession, $errors)){
                $errors[] = $notInSession;
            }
        }

        if(!empty($errors)){
            return response([
                'status' => 'failed',
                'message' => join(' ', $errors)
            ], 409);
        }

        $all = $request->all();
        if(!$term->update($all)){
            return response([
                'status' => 'failed',
                'message' => 'Term update failed'
            ], 409);
        }

        return response([
            'status' => 'success',
            'message' => 'Term updated successfully',
            'data' => $term
        ], 200);
    }

    public function destroy($uuid){
        if(empty($session = SchoolSession::where('uuid', $uuid)->where('school_location_id', $this->user->school_location_id)->first())){
            return response([
                'status' => 'failed',
                'message' => 'No School Session was fetched'
            ], 409);
        }
        if($session->status != 0){
            return response([
                'status' => 'failed',
                'message' => 'You can only delete an Upcoming Session'
            ], 409);
        }
        $session->delete();
        $terms = SchoolTerm::where('school_session_id', $session->id);
        if($terms->count() > 0){
            foreach($terms->get() as $term){
                $term->delete();
            }
        }

        return response([
            'status' => 'success',
            'message' => 'School Session deleted successfully alongside it\'s Terms'
        ], 200);
    }

    public function destroy_term($uuid){
        $term = SchoolTerm::where('uuid', $uuid)->where('school_location_id', $this->user->school_location_id)->first();
        if(empty($term)){
            return response([
                'status' => 'failed',
                'message' => 'No Term was fetched'
            ], 404);
        }
        if($term->status != 0){
            return response([
                'status' => 'failed',
                'message' => 'You can only delete an Upcoming School Term'
            ], 409);
        }

        $term->delete();
        return response([
            'status' => 'success',
            'message' => 'School Term deleted successfully'
        ], 200);
    }
}
