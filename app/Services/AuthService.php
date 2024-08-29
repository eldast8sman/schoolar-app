<?php

namespace App\Services;

use App\Mail\Parent\ForgotPasswordMail;
use App\Mail\SendOTPMail;
use App\Models\Parent\Parents;
use App\Models\School;
use App\Models\Student\Student;
use App\Models\Teacher\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PHPUnit\Event\Code\Test;

class AuthService
{
    private $guard;
    public $errors = "";
    private $time;

    public function __construct($guard='user-api')
    {
        $this->guard = $guard;
        $this->time = Carbon::now();
    }

    public function attempt($data) : array|bool
    {
        if(!$token = auth($this->guard)->attempt($data)){
            return false;
        }

        return [
            'token' => $token,
            'type' => 'Bearer',
            'expires' => $this->time->addMinutes(env('JWT_TTL'))->format('Y-m-d H:i:s')
        ];
    }

    public function logout() : void
    {
        auth($this->guard)->logout();
    }

    public function logged_in_user() : User|Parents|Teacher|Student
    {
        return auth($this->guard)->user();
    }

    public function login($user) : array|bool
    {
        if(!$token = auth($this->guard)->login($user)){
            return false;
        }

        return [
            'token' => $token,
            'type' => 'Bearer',
            'expires' => $this->time->addMinutes(env('JWT_TTL'))->format('Y-m-d H:i:s')
        ];
    }

    public function refresh_token() : array|bool
    {
        try {
            $token = auth($this->guard)->refresh();

            $data = [
                'token' => $token,
                'type' => 'Bearer',
                'expires' => env('JWT_TTL') * 60
            ];

            return $data;
        } catch(Exception $e){
            return false;
        }
    }

    public function forgot_password(Request $request){
        if($this->guard == 'user-api'){
            $user = User::where('email', $request->email)->first();
        } elseif($this->guard == 'teacher-api'){
            $user = Teacher::where('email', $request->email)->first();
        } elseif($this->guard == 'parent-api'){
            $user = Parents::where('email', $request->email)->first();
        } elseif($this->guard == 'student-api'){
            $user = Student::where('email', $request->email)->first();
        }

        if(empty($user)){
            $this->errors = "Wrong Email";
            return false;
        }

        $time = Carbon::now();

        $user->token = Str::random(20).time();
        $user->token_expiry = $time->addMinutes(15)->format('Y-m-d H:i:s');
        $user->save();

        $user->name = $user->first_name.' '.$user->last_name;
        Mail::to($user)->send(new ForgotPasswordMail($user->name, $user->token));

        return $user;
    }

    public function reset_password(Request $request):bool
    {
        if($this->guard == 'user-api'){
            $user = User::where('token', $request->token)->first();
        } elseif($this->guard == 'teacher-api'){
            $user = Teacher::where('token', $request->token)->first();
        } elseif($this->guard == 'parent-api'){
            $user = Parents::where('token', $request->token)->first();
        } elseif($this->guard == 'student-api'){
            $user = Student::where('token', $request->token)->first();
        }
        if(empty($user)){
            $this->errors = "Wrong Link";
            return false;
        }

        if($user->token_expiry < Carbon::now()->format('Y-m-d H:i:s')){
            $this->errors = "Expired Link";
            return false;
        }

        $user->update([
            'token' => null,
            'token_expiry' => null,
            'password' => bcrypt($request->password)
        ]);

        return true;
    }

    public function change_password(Request $request) : bool
    {
        if($this->guard == 'user-api'){
            $user = User::find($this->logged_in_user()->id);
        } elseif($this->guard == 'teacher-api'){
            $user = Teacher::find($this->logged_in_user()->id);
        } elseif($this->guard == 'student-api'){
            $user = Student::find($this->logged_in_user()->id);
        } elseif($this->guard == 'parent-api'){
            $user = Parents::find($this->logged_in_user()->id);
        }

        if(!Hash::check($request->old_password, $user->password)){
            $this->errors = "Incorrect Old Password";
            return false;
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return true;
    }

    public function resend_verification_otp(){
        if($this->guard == 'user-api'){
            $user = User::find($this->logged_in_user()->id);
        } elseif($this->guard == 'teacher-api'){
            $user = Teacher::find($this->logged_in_user()->id);
        } elseif($this->guard == 'parent-api'){
            $user = Parents::find($this->logged_in_user()->id);
        }

        if($user->email_verified == 1){
            $this->errors = "Email is already verified";
            return false;
        }

        $otp = mt_rand(100000, 999999);
        $user->otp = Crypt::encryptString($otp);
        $user->otp_expiry = Carbon::now()->addMinutes(30)->format('Y-m-d H:i:s');
        $user->save();

        $user->name = $user->first_name.' '.$user->last_name;
        Mail::to($user)->send(new SendOTPMail($user->name, $otp));
        
        return $user;
    }

    public function verify_email($otp){
        if($this->guard == 'user-api'){
            $user = User::find($this->logged_in_user()->id);
        } elseif($this->guard == 'teacher-api'){
            $user = Teacher::find($this->logged_in_user()->id);
        } elseif($this->guard == 'parent-api'){
            $user = Parents::find($this->logged_in_user()->id);
        }
        if(Carbon::now()->format('Y-m-d') > $user->otp_expiry){
            $this->errors = "Expired Link";
            return false;
        }

        $decrypt = Crypt::decryptString($user->otp);
        if($decrypt != $otp){
            $this->errors = "Wrong OTP";
            return false;
        }

        $school = School::find($user->school_id);
        $user->email_verified = 1;
        $user->otp = null;
        $user->otp_expiry = null;
        $user->onboarding_status = ($school->type == 'independent') ? 3 : 2;
        $user->save();

        return $user;
    }
}