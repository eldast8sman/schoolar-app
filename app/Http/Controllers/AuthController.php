<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\School;
use App\Models\SchoolLocation;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Mail\SendOTPMail;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
            'password' => Hash::make($request->password)
        ])){
            if($school = School::create([
                'name' => $request->school_name,
                'type' => $request->school_type,
                'country' => !empty($request->country) ? (string)$request->country : ""
            ])){
                if($location = SchoolLocation::create([
                    'school_id' => $school->id,
                    'state' => $request->state,
                    'country' => !empty($request->country) ? (string)$request->country : ""
                ])) {
                    $user->school_id = $school->id;
                    $user->school_location_id = $location->id;

                    $otp = mt_rand(100000, 999999);
                    $user->otp = Crypt::encryptString($otp);
                    $user->save();

                    $user->name = $user->first_name.' '.$user->last_name;
                    Mail::to($user)->send(new SendOTPMail($user->name, $otp));

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

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }
}
