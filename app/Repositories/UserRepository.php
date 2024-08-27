<?php

namespace App\Repositories;

use App\Events\LoadDefaultModules;
use App\Events\UserRegistered;
use App\Models\School;
use App\Models\SchoolLocation;
use App\Models\User;
use App\Models\UserSchool;
use App\Repositories\Interfaces\SchoolRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class UserRepository extends AbstractRepository implements UserRepositoryInterface
{
    public $errors;
    private $time;

    public function __construct(User $user)
    {
        parent::__construct($user);
        $this->time = Carbon::now();
    }

    public function store(Request $request)
    {
        if(!$user = $this->create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'onboarding_status' => 1,
            'email_verified' => 0
        ])){
            $this->errors = "Accout creation failed. Try again later";
            return false;
        }
        if(!$school = School::create([
            'name' => $request->school_name,
            'type' => $request->school_type,
            'country' => !empty($request->country) ? (string)$request->country : "Nigeria"
        ])){
            $user->delete();
            $this->errors = "School Account not created";
            return false;
        }
        if(!$location = SchoolLocation::create([
            'location_type' => !empty($request->location_type) ? (string)$request->location_type : "secondary",
            'syllabus' => !empty($request->syllabus) ? (string)$request->syllabus : 'waec',
            'school_id' => $school->id,
            'state' => $request->state,
            'country' => !empty($request->country) ? (string)$request->country : "Nigeria",
            'address' => $request->address
        ])){
            $user->delete();
            $school->delete();
            $this->errors = "School Location not created! Please try again later";
            return false;
        }

        UserSchool::create([
            'user_id' => $user->id,
            'school_id' => $school->id
        ]);
        $user->school_id = $school->id;
        $user->school_location_id = $location->id;
        $otp = mt_rand(100000, 999999);
        $user->otp = Crypt::encryptString($otp);
        $user->otp_expiry = $this->time->addMinutes(30)->format('Y-m-d H:i:s');
        $user->save();

        UserRegistered::dispatch($user, $otp);
        if($request->load_default){
            LoadDefaultModules::dispatch($location);
        }

        return $user;
    }
}