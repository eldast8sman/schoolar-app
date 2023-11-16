<?php

namespace App\Http\Controllers\Student;

use App\Models\School;
use App\Models\SubClass;
use App\Models\MainClass;
use Illuminate\Support\Str;
use App\Mail\AddStudentMail;
use Illuminate\Http\Request;
use App\Models\SchoolStudent;
use App\Models\SchoolLocation;
use App\Models\Student\Student;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\Student\ForgotPasswordMail;
use App\Http\Requests\Student\LoginRequest;
use App\Http\Requests\Student\ForgotPasswordRequest;
use App\Http\Requests\Student\ActivateAccountRequest;
use App\Http\Requests\Student\ResetPasswordRequest;

class AuthController extends Controller
{
    public function username(){
        return 'username';
    }

    public function student(Student $student) : Student
    {
        if(!empty($student->school_id)){
            $school = School::find($student->school_id);
            unset($school->logo_path);
            unset($school->id);
            $student->school = $school;
        }
        if(!empty($student->school_location_id)){
            $location = SchoolLocation::find($student->school_location_id);
            unset($location->id);
            $student->school_location = $location;
        }
        $student->profile_photo = SchoolStudent::find($student->school_student_id)->file_url;
        $student->main_class = MainClass::find($student->main_class_id)->name;
        $student->sub_class_id = SubClass::find($student->sub_class_id)->name;
        return $student;
    }

    public function fetch_by_token($token){
        $student = Student::where('token', $token)->first();
        if(empty($student)){
            return response([
                'status' => 'failed',
                'message' => 'No Student was fetched'
            ], 404);
        }

        if($student->token_expiry < date('Y-m-d H:i:s')){
            $token = Str::random(20).time();
            $expiry = date('Y-m-d H:i:s', time() + (60 * 60 * 24));
            $student->token = $token;
            $student->token_expiry = $expiry;
            $student->save();

            $school = School::find($student->school_id);
            $student->name = $student->first_name.' '.$student->last_name;
            Mail::to($student)->send(new AddStudentMail($student->name, $token, $school->name));
            unset($student->name);

            return response([
                'status' => 'failed',
                'message' => 'Link Expired. Another registration link has been sent to '.$student->email.'.'
            ], 400);
            exit;
        }

        return response([
            'status' => 'success',
            'message' => 'Student fetched successfully',
            'data' => $this->student($student)
        ], 200);
    }

    public function activate_account(ActivateAccountRequest $request){
        $student = Student::where('token', $request->token)->first();
        if(empty($student)){
            return response([
                'status' => 'faied',
                'message' => 'No Student was fetched'
            ], 404);
        }
        if($student->token_expiry < date('Y-m-d H:i:s')){
            return response([
                'status' => 'failed',
                'message' => 'Expired Link'
            ], 409);
        }

        $student->password = Hash::make($request->password);
        $student->token = null;
        $student->token_expiry = null;
        $student->save();

        return response([
            'status' => 'success',
            'message' => 'Account successfully activated',
            'data' => $this->student($student)
        ], 200);
    }

    public function login(LoginRequest $request){
        if(!$token = auth('student-api')->attempt([
            'username' => $request->username,
            'password' => $request->password
        ])){
            return response([
                'status' => 'failed',
                'message' => 'Login failed due to wrong credentials'
            ], 404);
        }

        $student = $this->student(Student::where('username', $request->username)->first());
        $student->authorization = [
            'token' => $token,
            'type' => 'Bearer',
            'duration' => 1440 * 60
        ];

        return response([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => $student
        ], 200);
    }

    public static function user(){
        return auth('student-api')->user();
    } 

    public function me(){
        $student = $this->student(Student::find(auth('student-api')->user()->id));

        return response([
            'status' => 'success',
            'message' => 'Student details fetched successfully',
            'data' => $student
        ], 200);
    }

    public function forgot_password(ForgotPasswordRequest $request){
        $student = Student::where('username', $request->username)->first();
        $time = time();
        $token = Str::random(20).time();
        $student->token = $token;
        $student->token_expiry = date('Y-m-d H:i:s', $time + (60 * 15));
        $student->save();

        $student->name = $student->first_name.' '.$student->last_name;
        Mail::to($student)->send(new ForgotPasswordMail($student->name, $token));

        return response([
            'status' => 'success',
            'message' => 'Password Reset Link sent to '.$student->email
        ], 200);
    }

    public function reset_password(ResetPasswordRequest $request){
        $student = Student::where('token', $request->token)->first();
        if(empty($student)){
            return response([
                'status' => 'failed',
                'message' => 'Wrong Link'
            ], 404);
        }
        if($student->token_expiry < date('Y-m-d H:i:s')){
            $student->token = null;
            $student->token_expiry = null;
            $student->save();
            return response([
                'status' => 'failed',
                'message' => 'Expired Link'
            ], 400);
        }

        $student->password = Hash::make($request->password);
        $student->token = null;
        $student->token_expiry = null;
        $student->save();

        return response([
            'status' => 'success',
            'message' => 'Password reset successfully'
        ], 200);
    }

    public function logout(){
        auth('student-api')->logout();

        return response([
            'status' => 'success',
            'message' => 'Logged out successfully'
        ], 200);
    }
}
