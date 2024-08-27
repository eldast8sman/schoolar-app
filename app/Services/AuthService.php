<?php

namespace App\Services;

use App\Mail\Parent\ForgotPasswordMail;
use App\Models\Parent\Parents;
use App\Models\Student\Student;
use App\Models\Teacher\Teacher;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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

        return true;
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
}