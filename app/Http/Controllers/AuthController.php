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

    public function login_function($email, $password){
        if($token = auth('user-api')->attempt([
            'email' => $email,
            'password' => $password
        ])){
            return $token;
        } else {
            return false;
        }
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
        if(!empty(self::user())){
            $user = User::find(self::user()->id);
            if(!empty($user)){
                if($user->email_verified == 0){
                    $decrypt = Crypt::decryptString($user->otp);
                    if($decrypt == $pin){
                        if(date('Y-m-d H:i:s') <= $user->otp_expiry){
                            $school = School::find($user->school_id);
                            $user->email_verified = 1;
                            $user->save();
                            $user->otp = null;
                            $user->otp_expiry = null;
                            $user->onboarding_status = ($school->type == 'independent') ? 3 : 2;
                            $user->save();
                            return response([
                                'status' => 'success',
                                'message' => 'Email verified successfully'
                            ], 200);
                        } else {
                            $user->otp = null;
                            $user->otp_expiry = null;
                            $user->save();
                            return response([
                                'status' => 'failed',
                                'message' => 'PIN already expired'
                            ], 400);
                        }
                    } else {
                        $user->otp = null;
                        $user->otp_expiry = null;
                        $user->save();
                        return response([
                            'status' => 'failed',
                            'message' => 'Wrong Verification PIN'
                        ], 404);
                    }
                } else {
                    return response([
                        'status' => 'failed',
                        'message' => 'Your Email is already verified'
                    ], 409);
                }
            } else {
                return response([
                    'status' => 'failed',
                    'message' => 'No User was fetched'
                ], 404);
            }
        } else {
            return response([
                'status' => 'failed',
                'message' => 'Unauthorized'
            ], 401);
        }
    }

    public function me(){
        $user = auth('user-api')->user();
        $user->school = !empty($user->school_id) ? School::find($user->school_id) : "";
        $user->school_location = !empty($user->school_location_id) ? self::school_location($user->school_location_id) : "";
        $user->schools = self::user_details($user->id);

        return response([
            'status' => 'success',
            'message' => 'User details fetched successfully',
            'data' => $user
        ], 200);
    }

    public static function user(){
        return auth('user-api')->user();
    }

    public function resend_verification_otp(){
        $user = User::find($this->user()->id);
        if($user->email_verified == 0){
            $otp = mt_rand(100000, 999999);
            $time = time();
            $new_time = $time + 60 * 30;
            $user->otp = Crypt::encryptString($otp);
            $user->otp_expiry = date('Y-m-d H:i:s', $new_time);
            $user->save();

            $user->name = $user->first_name.' '.$user->last_name;
            Mail::to($user)->send(new SendOTPMail($user->name, $otp));

            return response([
                'status' => 'success',
                'message' => 'PIN sent to '.$user->email
            ], 200);
        } else {
            return response([
                'status' => 'failed',
                'message' => 'Email already verified'
            ], 400);
        }
    }

    public function login(LoginRequest $request){
        $user = User::where('email', $request->email)->first();
        if($token = $this->login_function($request->email, $request->password)){
            $user->school = !empty($user->school_id) ? School::find($user->school_id) : "";
            $user->school_location = !empty($user->school_location_id) ? self::school_location($user->school_location_id) : "";
            $user->schools = self::user_details($user->id);
            $user->authorization = [
                'token' => $token,
                'type' => 'Bearer',
                'duration' => 1440*60
            ];

            return response([
                'status' => 'success',
                'message' => 'Login successful',
                'data' => $user
            ], 200);
        } else {
            return response([
                'status' => 'failed',
                'message' => 'Wrong Password'
            ], 401);
        }
    }

    public function forgot_password(ForgotPasswordRequest $request){
        $user = User::where('email', $request->email)->first();
        $time = time();
        $token = Str::random(20).time();
        $user->token = $token;
        $user->token_expiry = date('Y-m-d H:i:s', $time + (60 * 15));
        $user->save();

        $user->name = $user->first_name.' '.$user->last_name;
        Mail::to($user)->send(new ForgotPasswordMail($user->name, $token));

        return response([
            'status' => 'success',
            'message' => 'Password Reset Link sent to '.$user->email
        ], 200);
    }

    public function reset_password(ResetPasswordRequest $request){
        $user = User::where('token', $request->token)->first();
        if(!empty($user)){
            if($user->token_expiry >= date('Y-m-d H:i:s')){
                $user->password = Hash::make($request->password);
                $user->token = null;
                $user->token_expiry = null;
                $user->save();

                return response([
                    'status' => 'success',
                    'message' => 'Password reset successfully'
                ], 200);
            } else {
                $user->token = null;
                $user->token_expiry = null;
                $user->save();
                return response([
                    'status' => 'failed',
                    'message' => 'Expired Link'
                ], 400);
            }
        } else {
            return response([
                'status' => 'failed',
                'message' => 'Wrong Link'
            ], 404);
        }
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
        auth('user-api')->logout();

        return response([
            'status' => 'success',
            'message' => 'Logged out successfully'
        ], 200);
    }
}
