<?php

namespace App\Http\Controllers\Parent;

use App\Models\School;
use App\Models\SubClass;
use App\Models\MainClass;
use App\Mail\AddParentMail;
use Illuminate\Support\Str;
use App\Models\SchoolParent;
use Illuminate\Http\Request;
use App\Models\ParentStudent;
use App\Models\SchoolStudent;
use App\Models\SchoolLocation;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\Parent\ForgotPasswordMail;
use App\Models\Parent\ParentSchoolParent;
use App\Http\Requests\Parent\LoginRequest;
use App\Models\Parent\Parents as ParentModel;
use App\Http\Requests\Parent\ResetPasswordRequest;
use App\Http\Requests\Parent\ForgotPasswordRequest;
use App\Http\Requests\Parent\ActivateAccountRequest;

class AuthController extends Controller
{
    public function username(){
        return 'mobile';
    }

    public static function parent_details(ParentModel $parent) : ParentModel
    {
        $students = [];
        $sch_parents = ParentSchoolParent::where('parent_id', $parent->id)->get();
        foreach($sch_parents as $sch_parent){
            $school_parent = SchoolParent::find($sch_parent->school_parent_id);
            $stu_parents = ParentStudent::where('school_parent_id', $school_parent->id)->get();
            foreach($stu_parents as $stu_parent){
                $student = SchoolStudent::find($stu_parent->school_student_id);
                $school = School::find($student->school_id);
                $location = SchoolLocation::find($student->school_location_id);

                $students[] = [
                    'uuid' => $student->uuid,
                    'first_name' => $student->first_name,
                    'middle_name' => $student->middle_name,
                    'last_name' => $student->last_name,
                    'school' => $school->name,
                    'school_location' => $location,
                    'main_class' => MainClass::find($student->main_class_id)->name,
                    'sub_class_id' => SubClass::find($student->sub_class_id)->name,
                    'profiile_photo' => $student->file_url
                ];
            }
        }

        $parent->students = $students;
        
        return $parent;
    }

    public function fetch_by_token($token){
        $parent = ParentModel::where('token', $token)->first();
        if(empty($parent)){
            return response([
                'status' => 'failed',
                'message' => 'No Parent was fetched'
            ], 404);
            exit;
        }

        if($parent->token_expiry < date('Y-m-d H:i:s')){
            $token = Str::random(20).time();
            $expiry = date('Y-m-d H:i:s', time() + (60 * 60 * 24));
            $parent->token = $token;
            $parent->token_expiry = $expiry;
            $parent->save();

            $parent->name = $parent->first_name.' '.$parent->last_name;
            Mail::to($parent)->send(new AddParentMail($parent->name, $token));
            unset($parent->name);

            return response([
                'status' => 'failed',
                'message' => 'Link Expired. Another registration link has been sent to '.$parent->email.'.'
            ], 400);
            exit;
        }

        return response([
            'status' => 'success',
            'message' => 'Teacher successfully fetched',
            'data' => $parent
        ], 200);
    }

    public function activate_account(ActivateAccountRequest $request){
        $parent = ParentModel::where('token', $request->token)->first();
        if(empty($parent)){
            return response([
                'status' => 'failed',
                'message' => 'No Parent was fetched'
            ], 404);
            exit;
        }
        if($parent->token_expiry < date('Y-m-d H:i:s')){
            return response([
                'status' => 'failed',
                'message' => 'Expired Link'
            ], 409);
        }
        
        $parent->password = Hash::make($request->password);
        $parent->token = null;
        $parent->token_expiry = null;
        $parent->save();

        return response([
            'status' => 'success',
            'message' => 'Account successfully validated',
            'data' => $parent
        ], 200);
    }

    public function login(LoginRequest $request){
        if(!$token = auth('parent-api')->attempt([
            'mobile' => $request->mobile,
            'password' => $request->password
        ])){
            return response([
                'status' => 'failed',
                'message' => 'Login failed due to wrong credentials'
            ], 404);
        }
        $parent = ParentModel::where('mobile', $request->mobile)->first();
        $parent = self::parent_details($parent);
        $parent->authorization = [
            'token' => $token,
            'type' => 'Bearer',
            'duration' => 1440 * 60
        ];
        
        return response([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => $parent
        ], 200);
    }

    public static function user(){
        return auth('parent-api')->user();
    }

    public function me(){
        $parent = ParentModel::find(auth('teacher-api')->user()->id);
        $parent = self::parent_details($parent);

        return response([
            'status' => 'success',
            'message' => 'Parent details fetched successfully',
            'data' => $parent
        ], 200);
    }

    public function forgot_password(ForgotPasswordRequest $request){
        $parent = ParentModel::where('email', $request->email)->first();
        $time = time();
        $token = Str::random(20).time();
        $parent->token = $token;
        $parent->token_expiry = date('Y-m-d H:i:s', $time + (60 * 15));
        $parent->save();

        $parent->name = $parent->first_name.' '.$parent->last_name;
        Mail::to($parent)->send(new ForgotPasswordMail($parent->name, $token));

        return response([
            'status' => 'success',
            'message' => 'Password Reset Link sent to '.$parent->email
        ], 200);
    }

    public function reset_password(ResetPasswordRequest $request){
        $user = ParentModel::where('token', $request->token)->first();
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
        auth('parent-api')->logout();

        return response([
            'status' => 'success',
            'message' => 'Logged out successfully'
        ], 200);
    }
}

