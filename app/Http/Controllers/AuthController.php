<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\School;
use App\Mail\SendOTPMail;
use App\Models\UserSchool;
use Illuminate\Support\Str;
use App\Models\SchoolLocation;
use App\Mail\ForgotPasswordMail;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\UpdateEmailRequest;
use App\Http\Resources\LoggedInUserResource;
use App\Models\SchoolSession;
use App\Models\SchoolTerm;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\AuthService;
use App\Traits\APIResponseTrait;

class AuthController extends Controller
{
    use APIResponseTrait;

    private $auth;
    private $interface;

    public function __construct(UserRepositoryInterface $interface)
    {
        $this->auth = new AuthService('user-api');
        $this->interface = $interface;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public static function school_location($id){
        $location  =  SchoolLocation::find($id);
        $current_session = SchoolSession::where('school_location_id', $location->id)->where('status', 2)->where('start_date', '<=', date('Y-m-d'))->where('end_date', '>=', date('Y-m-d'))->first();
        $location->current_session = $current_session;
        if(!empty($current_session)){
            $current_term = SchoolTerm::where('school_location_id', $location->id)->where('school_session_id', $current_session->id)->where('status', 2)->where('start_date', '<=', date('Y-m-d'))->where('end_date', '>=', date('Y-m-d'))->first();
            $location->current_term = $current_term;
        } else {
            $location->current_term = null;
        }

        return $location;
    }

    public static function user_details($user_id){
        $details = [];
        $user_schools = UserSchool::where('user_id', $user_id);
        if($user_schools->count() > 0){
            foreach($user_schools->get() as $user_school){
                $school = School::find($user_school->school_id);
                if(!empty($school)){
                    $locations = SchoolLocation::where('school_id', $school->id)->get();
                    
                    $school->locations = $locations;
                }
                $details[] = $school;
            }
        }

        return $details;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request){
        if(!$user = $this->interface->store($request)){
            return $this->failed_response($this->interface->errors, 400);
        }
        $login = $this->auth->login($user);

        $user->authorization = $login;
        $user = new LoggedInUserResource($user);

        return $this->success_response("Registration Successful", $user);
    }

    public function verify_email($pin){
        if(!$user = $this->auth->verify_email($pin)){
            return $this->failed_response($this->auth->errors);
        }
        return $this->success_response("Email successfully verified", new LoggedInUserResource($user));
    }

    public function me(){
        return $this->success_response("User Details fetched", new LoggedInUserResource($this->auth->logged_in_user()));
    }

    public static function user(){
        return auth('user-api')->user();
    }

    public function resend_verification_otp(){
        if(!$user = $this->auth->resend_verification_otp()){
            return $this->failed_response($this->auth->errors, 400);
        }

        return $this->success_response("OTP resent to your Mail ".$user->email);
    }

    public function login(LoginRequest $request){
        $user = User::where('email', $request->email)->first();
        if(!$token = $this->auth->attempt($request->all())){
            return $this->failed_response("Wrong Credentials", 400);
        }
        $user->authorization = $token;

        return $this->success_response("Login successful", new LoggedInUserResource($user));
    }

    public function forgot_password(ForgotPasswordRequest $request){
        if(!$user = $this->auth->forgot_password($request)){
            return $this->failed_response($this->auth->errors, 400);
        }

        return $this->success_response("Reset Password Link sent to ".$user->email);
    }

    public function reset_password(ResetPasswordRequest $request){
        if(!$this->auth->reset_password($request)){
            return $this->failed_response($this->auth->errors, 400);
        }
        return $this->success_response("Password reset successful");
    }

    public function update_email(UpdateEmailRequest $request){
        $user = User::find(self::user()->id);

        $others = User::where('email', $request->email)->where('id', '<>', $user->id);
        if($others->count() > 0){
            return response([
                'status' => 'failed',
                'message' => 'Invalid Email'
            ], 409);
        }

        $user->email = $request->email;
        $user->save();

        return response([
            'status' => 'success',
            'message' => 'Email updated successfully',
            'data' => $user
        ], 200);
    }

    public function skip_add_location(){
       
    }

    public function logout(){
        $this->auth->logout();
        return $this->success_response('Logged out successfully');
    }
}
