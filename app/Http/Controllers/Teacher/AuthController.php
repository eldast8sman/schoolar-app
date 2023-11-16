<?php

namespace App\Http\Controllers\Teacher;

use App\Models\School;
use Illuminate\Support\Str;
use App\Mail\AddTeacherMail;
use Illuminate\Http\Request;
use App\Models\SchoolLocation;
use App\Models\Teacher\Teacher;
use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPasswordRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\Teacher\LoginRequest;
use App\Models\Teacher\TeacherSchoolTeacher;
use App\Http\Requests\Teacher\ActivateAccountRequest;
use App\Http\Requests\Teacher\ForgotPasswordRequest;
use App\Mail\Teacher\ForgotPasswordMail;

class AuthController extends Controller
{
    public static function teacher_details($teacher_id){
        $school_details = [];
        $school_teachers = TeacherSchoolTeacher::where('teacher_id', $teacher_id);
        if($school_teachers->count() > 0){
            foreach($school_teachers->get() as $teacher){
                $school = School::find($teacher->school_id);
                $location = SchoolLocation::find($teacher->school_location_id);
                $teacher->school = $school->name;
                $teacher->school_location = $location->address.', '.$location->town.', '.$location->lga.' '.$location->state;
                $school_details[] = $teacher;
            }
        }

        return $school_details;
    }
    
    public function fetch_by_token($token){
        $teacher = Teacher::where('token', $token)->first();
        if(empty($teacher)){
            return response([
                'status' => 'failed',
                'message' => 'No Teacher was fetched'
            ], 404);
            exit;
        }

        if($teacher->token_expiry < date('Y-m-d H:i:s')){
            $token = Str::random(20).time();
            $expiry = date('Y-m-d H:i:s', time() + (60 * 60 * 24));
            $teacher->token = $token;
            $teacher->token_expiry = $expiry;
            $teacher->save();

            $teacher->name = $teacher->first_name.' '.$teacher->last_name;
            Mail::to($teacher)->send(new AddTeacherMail($teacher->name, $token));
            unset($teacher->name);

            return response([
                'status' => 'failed',
                'message' => 'Link Expired. Another registration link has been sent to '.$teacher->email.'.'
            ], 400);
            exit;
        }

        return response([
            'status' => 'success',
            'message' => 'Teacher successfully fetched',
            'data' => $teacher
        ], 200);
    }

    public function activate_account(ActivateAccountRequest $request){
        $teacher = Teacher::where('token', $request->token)->first();
        if(empty($teacher)){
            return response([
                'status' => 'failed',
                'message' => 'No Teacher was fetched'
            ], 404);
            exit;
        }
        if($teacher->token_expiry < date('Y-m-d H:i:s')){
            return response([
                'status' => 'failed',
                'message' => 'Expired Link'
            ], 409);
        }
        
        $teacher->password = Hash::make($request->password);
        $teacher->token = null;
        $teacher->token_expiry = null;
        $teacher->save();

        return response([
            'status' => 'success',
            'message' => 'Account successfully validated',
            'data' => $teacher
        ], 200);
    }

    public function login(LoginRequest $request){
        if($token = auth('teacher-api')->attempt([
            'email' => $request->email,
            'password' => $request->password
        ])){
            $teacher = Teacher::where('email', $request->email)->first();
            $teacher->schools = self::teacher_details($teacher->id);
            $teacher->authorization = [
                'token' => $token,
                'type' => 'Bearer',
                'duration' => 1440 * 60
            ];
            
            return response([
                'status' => 'success',
                'message' => 'Login successful',
                'data' => $teacher
            ], 200);
        } else {
            return response([
                'status' => 'failed',
                'message' => 'Login failed due to wrong credentials'
            ], 404);
        }
    }

    public static function user(){
        return auth('teacher-api')->user();
    }

    public function me(){
        $teacher = auth('teacher-api')->user();
        $teacher->schools = self::teacher_details($teacher->id);

        return response([
            'status' => 'success',
            'message' => 'Teacher details fetched successfully',
            'data' => $teacher
        ], 200);
    }

    public function forgot_password(ForgotPasswordRequest $request){
        $teacher = Teacher::where('email', $request->email)->first();
        $time = time();
        $token = Str::random(20).time();
        $teacher->token = $token;
        $teacher->token_expiry = date('Y-m-d H:i:s', $time + (60 * 15));
        $teacher->save();

        $teacher->name = $teacher->first_name.' '.$teacher->last_name;
        Mail::to($teacher)->send(new ForgotPasswordMail($teacher->name, $token));

        return response([
            'status' => 'success',
            'message' => 'Password Reset Link sent to '.$teacher->email
        ], 200);
    }

    public function reset_password(ResetPasswordRequest $request){
        $user = Teacher::where('token', $request->token)->first();
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

    public function logout(){
        auth('teacher-api')->logout();

        return response([
            'status' => 'success',
            'message' => 'Logged out successfully'
        ], 200);
    }
}
