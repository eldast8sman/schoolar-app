<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\School;
use App\Models\Subject;
use App\Models\SubClass;
use App\Mail\SendOTPMail;
use App\Models\MainClass;
use App\Models\UserSchool;
use Illuminate\Support\Str;
use App\Models\SchoolLocation;
use App\Mail\ForgotPasswordMail;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\UpdateEmailRequest;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public static function user_details($user_id){
        $details = [];
        $user_schools = UserSchool::where('user_id', $user_id);
        if($user_schools->count() > 0){
            foreach($user_schools->get() as $user_school){
                $school = School::find($user_school->school_id);
                if(!empty($school)){
                    $school->locations = SchoolLocation::where('school_id', $school->id)->get();
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
    public function store(StoreUserRequest $request)
    {
        if($user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'onboarding_status' => 1,
            'email_verified' => 0
        ])){
            if($school = School::create([
                'name' => $request->school_name,
                'type' => $request->school_type,
                'country' => !empty($request->country) ? (string)$request->country : ""
            ])){
                if($location = SchoolLocation::create([
                    'location_type' => !empty($request->location_type) ? (string)$request->location_type : "secondary",
                    'syllabus' => !empty($request->syllabus) ? (string)$request->syllabus : 'waec',
                    'school_id' => $school->id,
                    'state' => $request->state,
                    'country' => !empty($request->country) ? (string)$request->country : "Nigeria",
                    'address' => $request->address
                ])) {
                    if($request->load_default == true){
                        if(strtolower($location->country) == 'nigeria'){
                            $subjects = FunctionController::default_subjects();
                            if($location->location_type == "primary"){
                                for($i=1; $i<=6; $i++){
                                    $class = MainClass::create([
                                        'school_id' => $school->id,
                                        'school_location_id' => $location->id,
                                        'class_level' => $i,
                                        'name' => 'Primary '.$i
                                    ]);
                                    $subclass = SubClass::create([
                                        'school_id' => $class->school_id,
                                        'school_location_id' => $class->school_location_id,
                                        'main_class_id' => $class->id,
                                        'name' => 'A'
                                    ]);
    
                                    foreach($subjects['primary'] as $subject){
                                        Subject::create([
                                            'school_id' => $school->id,
                                            'school_location_id' => $location->id,
                                            'main_class_id' => $class->id,
                                            'sub_class_id' => $subclass->id,
                                            'name' => $subject['subject'],
                                            'compulsory' => $subject['compulsory']
                                        ]);
                                    }
                                }
                            } elseif($location->location_type == "secondary"){
                                for($i=1; $i<=3; $i++){
                                    $class = MainClass::create([
                                        'school_id' => $school->id,
                                        'school_location_id' => $location->id,
                                        'class_level' => $i,
                                        'name' => 'JSS '.$i
                                    ]);
    
                                    $subclass = SubClass::create([
                                        'school_id' => $school->id,
                                        'school_location_id' => $location->id,
                                        'main_class_id' => $class->id,
                                        'name' => 'A'
                                    ]);
    
                                    foreach($subjects['junior_secondary'] as $subject){
                                        Subject::create([
                                            'school_id' => $school->id,
                                            'school_location_id' => $location->id,
                                            'main_class_id' => $class->id,
                                            'sub_class_id' => $subclass->id,
                                            'name' => $subject['subject'],
                                            'compulsory' => $subject['compulsory']
                                        ]);
                                    }
                                }
    
                                for($i=1; $i<=3; $i++){
                                    $class = MainClass::create([
                                        'school_id' => $school->id,
                                        'school_location_id' => $location->id,
                                        'class_level' => $i + 3,
                                        'name' => 'SSS '.$i
                                    ]);
    
                                    $sciences = SubClass::create([
                                        'school_id' => $school->id,
                                        'school_location_id' => $location->id,
                                        'main_class_id' => $class->id,
                                        'name' => 'A',
                                        'type' => 'sciences'
                                    ]);
                                    foreach($subjects['senior_secondary']['sciences'] as $subject){
                                        Subject::create([
                                            'school_id' => $school->id,
                                            'school_location_id' => $location->id,
                                            'main_class_id' => $class->id,
                                            'sub_class_id' => $sciences->id,
                                            'name' => $subject['subject'],
                                            'compulsory' => $subject['compulsory']
                                        ]);
                                    }
    
                                    $arts = SubClass::create([
                                        'school_id' => $school->id,
                                        'school_location_id' => $location->id,
                                        'main_class_id' => $class->id,
                                        'name' => 'B',
                                        'type' => 'arts'
                                    ]);
                                    foreach($subjects['senior_secondary']['arts'] as $subject){
                                        Subject::create([
                                            'school_id' => $school->id,
                                            'school_location_id' => $location->id,
                                            'main_class_id' => $class->id,
                                            'sub_class_id' => $arts->id,
                                            'name' => $subject['subject'],
                                            'compulsory' => $subject['compulsory']
                                        ]);
                                    }
    
                                    $commerce = SubClass::create([
                                        'school_id' => $school->id,
                                        'school_location_id' => $location->id,
                                        'main_class_id' => $class->id,
                                        'name' => 'C',
                                        'type' => 'commerce'
                                    ]);
                                    foreach($subjects['senior_secondary']['commerce'] as $subject){
                                        Subject::create([
                                            'school_id' => $school->id,
                                            'school_location_id' => $location->id,
                                            'main_class_id' => $class->id,
                                            'sub_class_id' => $commerce->id,
                                            'name' => $subject['subject'],
                                            'compulsory' => $subject['compulsory']
                                        ]);
                                    }
                                }
                            }
                        }                        
                    }
                    UserSchool::create([
                        'user_id' => $user->id,
                        'school_id' => $school->id
                    ]);
                    $user->school_id = $school->id;
                    $user->school_location_id = $location->id;

                    $otp = mt_rand(100000, 999999);
                    $time = time();
                    $new_time = $time + 60 * 30;
                    $user->otp = Crypt::encryptString($otp);
                    $user->otp_expiry = date('Y-m-d H:i:s', $new_time);
                    $user->save();

                    $user->name = $user->first_name.' '.$user->last_name;
                    Mail::to($user)->send(new SendOTPMail($user->name, $otp));

                    $user->schools = self::user_details($user->id);

                    $token = $this->login_function($user->email, $request->password);
                    $user->authorization = [
                        'token' => $token,
                        'type' => 'Bearer',
                        'duration' => 1440*60
                    ];
                    return response([
                        'status' => 'success',
                        'message' => 'Account successfully created',
                        'data' => $user
                    ], 200);
                } else {
                    $user->delete();
                    $school->delete();
                    return response([
                        'status' => 'failed',
                        'message' => 'School Location not created! Please try again later!'
                    ], 500);
                }
            } else {
                $user->delete();
                return response([
                    'status' => 'failed',
                    'message' => 'School Account not created'
                ], 500);
            }
        } else {
            return response([
                'status' => 'failed',
                'message' => 'Oops! Account creation failed! Please try again later'
            ], 500);
        }
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
