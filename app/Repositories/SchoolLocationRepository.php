<?php

namespace App\Repositories;

use App\Events\LoadDefaultModules;
use App\Models\SchoolLocation;
use App\Models\User;
use App\Repositories\Interfaces\SchoolLocationRepositoryInterface;
use App\Services\AuthService;
use Illuminate\Http\Request;

class SchoolLocationRepository extends AbstractRepository implements SchoolLocationRepositoryInterface
{
    public $errors;
    private $user;

    public function __construct(SchoolLocation $location)
    {
        parent::__construct($location);
        $auth = new AuthService('user-api');
        $this->user = $auth->logged_in_user();
    }

    public function store(Request $request)
    {
        $school = $this->user->school;
        if($school->type != 'group'){
            $this->errors = "This feature is only available for a Group of Schools";
            return false;
        }
        $data = [];

        foreach($request->locations as $location){
            if(!$added = SchoolLocation::create([
                'school_id' => $this->user->school_id,
                'address' => $location['address'],
                'town' =>$location['town'],
                'lga' => !empty($location['lga']) ? $location['lga'] : "",
                'state' => $location['state'],
                'country' => !empty($location['country']) ? $location['country'] : "Nigeria",
                'syllabus' => !empty($location['syllabus']) ? $location['syllabus'] : "",
                'location_type' => $location['location_type']
            ])){
                continue;
            }
            if($location['load_default'] == true){
                LoadDefaultModules::dispatch($added);
            }
            $data[] = $added;
        }
        return $data;
    }

    public function switch_location(SchoolLocation $location)
    {
        $school = $this->user->school;
        if($school->type != 'group'){
            $this->errors = "This feature is only available for a Group of Schools";
            return false;
        }
        if($location->school_id != $this->user->school_id){
            $this->errors = "No Locaion was fetched";
            return false;
        }

        $user = User::find($this->user->id);
        $user->update([
            'school_location_id' => $location->id
        ]);

        return $user;
    }
}