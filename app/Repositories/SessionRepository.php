<?php

namespace App\Repositories;

use App\Models\SchoolSession;
use App\Models\SchoolTerm;
use App\Repositories\Interfaces\SessionRepositoryInterface;
use App\Services\AuthService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SessionRepository extends AbstractRepository implements SessionRepositoryInterface
{
    public $errors;
    private $user;
    private $time;

    public function __construct(SchoolSession $session)
    {
        parent::__construct($session);
        $auth = new AuthService('user-api');
        $this->user = $auth->logged_in_user();
        $this->time = Carbon::now();
    }

    public function store(Request $request)
    {
        $errors = [];
        $overlap_message = "This Session's timeline is overlapping another of your Session!";
        if(!empty($this->findFirstBy([
            'school_location_id' => $this->user->school_location_id,
            'session_name' => $request->session_name
        ]))){
            $errors[] = "Session Name has already been taken";
        }
        if(!empty($this->findFirstBy([
            ['school_location_id', '=', $this->user->school_location_id],
            ['start_date', '<=', $request->start_date],
            ['end_date', '>=', $request->start_date]
        ]))){
            if(!in_array($overlap_message, $errors)){
                $errors[] = $overlap_message;
            }
        }
        if(!empty($this->findFirstBy([
            ['school_location_id', '=', $this->user->school_location_id],
            ['start_date', '<=', $request->end_date],
            ['end_date', '>=', $request->end_date]
        ]))){
            if(!in_array($overlap_message, $errors)){
                $errors[] = $overlap_message;
            }
        }
        if(!empty($this->findFirstBy([
            ['school_location_id', '=', $this->user->school_location_id],
            ['start_date', '>', $request->start_date],
            ['end_date', '<', $request->end_date]
        ]))){
            if(!in_array($overlap_message, $errors)){
                $errors[] = $overlap_message;
            }
        }
        if($request->start_date > $request->end_date){
            $errors[] = "Start Date must be earlier than End date";
        }

        if(!empty($errors)){
            $this->errors = join(' ', $errors);
            return false;
        }

        $all = $request->except(['load_default']);
        $all['school_id'] = $this->user->school_id;
        $all['school_location_id'] = $this->user->school_location_id;
        $today = $this->time->format('Y-m-d');
        if($request->start_date > $today){
            $status = 0;
        } elseif($request->end_date < $today){
            $status = 1;
        } else {
            $status = 2;
        }
        $all['status'] = $status;

        if(!$session = $this->create($all)){
            $this->errors = "Session creation failed! Please try again later";
            return false;
        }

        if($request->load_default == true){
            $school = $this->user->school;
            $location = $this->user->school_location;

            if((strtolower($school->country) == 'nigeria') and ((strtolower($location->location_type) == 'primary') or strtolower($location->location_type == 'secondary'))){
                $term_start = Carbon::createFromFormat('Y-m-d', $session->start_date);

                $durations = json_decode(file_get_contents(base_path('data/json/default_terms.json')), true);
                foreach($durations as $duration){
                    $continue = true;
                    if(!empty($session->end_date) and ($term_start->format('Y-m-d') > $session->end_date)){
                        $continue = false;
                    }
                    if(!$continue){
                        break;
                    }

                    $start_date = $term_start->copy();
                    $formatted_start_date = $start_date->format('Y-m-d');
                    $end_date = $start_date->copy()->addWeeks($duration['weeks'])->subDay();
                    $formmated_end_date = $end_date->format('Y-m-d');
                    $term_end = ($formmated_end_date <= $session->end_date) ? $formmated_end_date : $session->end_date;

                    $today = $this->time->format('Y-m-d');
                    if($start_date > $today){
                        $status = 0;
                    } elseif($end_date < $today){
                        $status = 1;
                    } else {
                        $status = 2;
                    }

                    SchoolTerm::create([
                        'school_id' => $school->id,
                        'school_location_id' => $location->id,
                        'school_session_id' => $session->id,
                        'term_name' => $duration['name'],
                        'start_date' => $formatted_start_date,
                        'end_date' => $term_end,
                        'position' => $duration['position'],
                        'status' => $status
                    ]);

                    $term_start = $end_date->addDay();
                }
            }
        }

        return $session;
    }

    public function index($search = '', $limit = 10, $filter = null)
    {
        $data = [
            ['school_location_id', '=', $this->user->school_location_id]
        ];
        if(!empty($search)){
            $data[] = ['session_name', 'like', '%'.$search.'%'];
        }
        if($filter !== null){
            $data[] = ['status', '=', $filter];
        }
        if($filter != 3){
            $data[] = ['status', '<>', 3];
        }
        $orderBy = ['start_date' => 'desc'];

        $sessions = $this->findBy($data, $orderBy, $limit);
        return $sessions;
    }

    public function update_session(Request $request, SchoolSession $session)
    {
        if($session->school_location_id != $this->user->school_location_id){
            $this->errors = "Wrong School Session";
            return false;
        }
        if($session->status != 0){
            $this->errors = "You can only update an Upcoming Session";
            return false;
        }

        $errors = [];
        $overlap_message = "This Session's timeline is overlapping another of your Session!";
        if(!empty($this->findFirstBy([
            ['school_location_id', '=', $this->user->school_location_id],
            ['session_name', '=', $request->session_name],
            ['id', '<>', $session->id]
        ]))){
            $errors[] = "Session Name has already been taken";
        }
        if(!empty($this->findFirstBy([
            ['school_location_id', '=', $this->user->school_location_id],
            ['start_date', '<=', $request->start_date],
            ['end_date', '>=', $request->start_date],
            ['id', '<>', $session->id]
        ]))){
            if(!in_array($overlap_message, $errors)){
                $errors[] = $overlap_message;
            }
        }
        if(!empty($this->findFirstBy([
            ['school_location_id', '=', $this->user->school_location_id],
            ['start_date', '<=', $request->end_date],
            ['end_date', '>=', $request->end_date],
            ['id', '<>', $session->id]
        ]))){
            if(!in_array($overlap_message, $errors)){
                $errors[] = $overlap_message;
            }
        }
        if(!empty($this->findFirstBy([
            ['school_location_id', '=', $this->user->school_location_id],
            ['start_date', '>', $request->start_date],
            ['end_date', '<', $request->end_date],
            ['id', '<>', $session->id]
        ]))){
            if(!in_array($overlap_message, $errors)){
                $errors[] = $overlap_message;
            }
        }
        if($request->start_date > $request->end_date){
            $errors[] = "Start Date must be earlier than End date";
        }

        if(!empty($errors)){
            $this->errors = join(' ', $errors);
            return false;
        }

        if(!$session->update($request->all())){
            $this->errors = "Update Failed";
            return false;
        }

        $to_delete = SchoolTerm::where('school_location_id', $this->user->school_location_id)->where(function($query) use ($session){
            $query->where('start_date', '<', $session->start_date)
                ->orWhere('end_date', '>', $session->end_date);
        });
        if($to_delete->count() > 0){
            foreach($to_delete->get() as $delete){
                $delete->delete();
            }
        }

        return $session;
    }

    public function destroy(SchoolSession $session) : bool
    {
        if($session->school_location_id != $this->user->school_location_id){
            $this->errors = "Wrong Session";
            return false;
        }
        if($session->status != 0){
            $this->errors = 'You can only delete an Upcoming Session';
            return false;
        }
        $session->delete();
        $terms = SchoolTerm::where('school_session_id', $session->id);
        if($terms->count() > 0){
            foreach($terms->get() as $term){
                $term->delete();
            }
        }

        return true;
    }
}