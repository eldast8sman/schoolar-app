<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\School;
use App\Models\Subject;
use App\Models\SubClass;
use App\Models\MainClass;
use Illuminate\Http\Request;
use App\Models\SchoolLocation;
use App\Http\Requests\AddLocationToSchoolRequest;
use App\Http\Resources\LocationResource;
use App\Http\Resources\LoggedInUserResource;
use App\Repositories\Interfaces\SchoolLocationRepositoryInterface;
use App\Traits\APIResponseTrait;

class SchoolController extends Controller
{
    use APIResponseTrait;

    private $interface;

    public function __construct(SchoolLocationRepositoryInterface $interface)
    {
        $this->middleware('auth:user-api');
        $this->interface = $interface;
    }    

    // public function add_locations(AddLocationToSchoolRequest $request){
    //     $school = School::find($this->user->school_id);
    //     if($school->type == "group"){
    //         $locations = $request->locations;

    //         $count = 0;
    //         $success = 0;
    //         $failed = 0;
    //         $data = [];
    //         foreach($locations as $location){
    //             $count += 1;
    //             if($added = SchoolLocation::create([
    //                 'school_id' => $this->user->school_id,
    //                 'address' => $location['address'],
    //                 'town' =>$location['town'],
    //                 'lga' => !empty($location['lga']) ? $location['lga'] : "",
    //                 'state' => $location['state'],
    //                 'country' => !empty($location['country']) ? $location['country'] : "Nigeria",
    //                 'syllabus' => !empty($location['syllabus']) ? $location['syllabus'] : "",
    //                 'location_type' => $location['location_type']
    //             ])){
    //                 if($location['load_default'] == true){
    //                     FunctionController::load_default($added->id);
    //                 }
    //                 $success += 1;
    //                 $data[] = $added;
    //             } else {
    //                 $failed += 1;
    //             }
    //         }

    //         if($success > 0){
    //             if($this->user->onboarding_status == 2){
    //                 $user = User::find($this->user->id);
    //                 $user->onboarding_status = 3;
    //                 $user->save();
    //             }
    //             return response([
    //                 'status' => 'success',
    //                 'message' => 'Locations Added to School',
    //                 'data' => [
    //                     'attempted' => $count,
    //                     'success' => $success,
    //                     'failed' => $failed,
    //                     'locations' => $data
    //                 ]
    //             ], 200);
    //         } else {
    //             return response([
    //                 'status' => 'failed',
    //                 'message' => 'Errors encountered in adding locations'
    //             ], 500);
    //         }
    //     } else {
    //         return response([
    //             'status' => 'failed',
    //             'message' => 'You can only add more schools to a group of Schools'
    //         ], 409);
    //     }
    // }

    public function add_locations(AddLocationToSchoolRequest $request){
        if(!$added = $this->interface->store($request)){
            return $this->failed_response($this->interface->errors);
        }                
        $data = [];
        foreach($added as $location){
            $data[] = new LocationResource($location);
        }
        return $this->success_response("Locations added successfully", $data);
    }

    public function switch_location(SchoolLocation $location){
        if(!$user = $this->interface->switch_location($location)){
            return $this->failed_response($this->interface->errors);   
        }

        return $this->success_response("Location switched successfully", new LoggedInUserResource($user));
    }
}