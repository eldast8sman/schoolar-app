<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\School;
use Illuminate\Http\Request;
use App\Models\SchoolLocation;
use App\Http\Requests\AddLocationToSchoolRequest;

class SchoolController extends Controller
{
    private $user;

    public function __construct()
    {
        $this->middleware('auth:user-api');
        $this->user = AuthController::user();
    }

    public function add_locations(AddLocationToSchoolRequest $request){
        $school = School::find($this->user->school_id);
        if($school->type == "group"){
            $locations = json_decode($request->locations);

            $count = 0;
            $success = 0;
            $failed = 0;
            $data = [];
            foreach($locations as $location){
                $count += 1;
                if($added = SchoolLocation::create([
                    'school_id' => $this->user->school_id,
                    'address' => !empty($location->address) ? htmlentities(strip_tags($location->address)) : "",
                    'town' => !empty($location->town) ? htmlentities(strip_tags($location->town)) : "",
                    'lga' => !empty($location->lga) ? htmlentities(strip_tags($location->lga)) : "",
                    'state' => !empty($location->state) ? htmlentities(strip_tags($location->state)) : "",
                    'country' => !empty($location->country) ? htmlentities(strip_tags($location->country)) : "Nigeria"
                ])){
                    $success += 1;
                    $data[] = $added;
                } else {
                    $failed += 1;
                }
            }

            if($success > 0){
                if($this->user->onboarding_status == 2){
                    $user = User::find($this->user->id);
                    $user->onboarding_status = 3;
                    $user->save();
                }
                return response([
                    'status' => 'success',
                    'message' => 'Locations Added to School',
                    'data' => [
                        'attempted' => $count,
                        'success' => $success,
                        'failed' => $failed,
                        'locations' => $data
                    ]
                ], 200);
            } else {
                return response([
                    'status' => 'failed',
                    'message' => 'Errors encountered in adding locations'
                ], 500);
            }
        } else {
            return response([
                'status' => 'failed',
                'message' => 'You can only add more schools to a group of Schools'
            ], 409);
        }
    }

    public function switch_location(SchoolLocation $location){
        $school = School::find($this->user->school_id);
        if($school->type == 'group'){
            if($location->school_id == $this->user->school_id){
                $user = User::find($this->user->id);
                $user->school_location_id = $location->id;
                $user->save();

                $user->schools = AuthController::user_details($user->id);
                return response([
                    'status' => 'success',
                    'message' => 'Location switched successfully',
                    'data' => $user
                ], 200);
            } else {
                return response([
                    'status' => 'failed',
                    'message' => 'No Location was fetched'
                ], 404);
            }
        } else {
            return response([
                'status' => 'failed',
                'message' => 'This feature is only available for a Group of Schools'
            ], 409);
        }
    }
}