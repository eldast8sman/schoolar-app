<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\SchoolController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function(){
    Route::post('/signup', 'store')->name('user.signup');
    Route::post('/login', 'login')->name('user.login');
    Route::post('/forgot-password', 'forgot_password')->name('user.forgot-password');
    Route::post('/reset-password', 'reset_password')->name('user.reset-password');
});

Route::middleware('auth:user-api')->group(function(){
    Route::controller(AuthController::class)->group(function(){
        Route::get('/verify-email/{pin}', 'verify_email')->name('verify_user_email');
        Route::get('/me', 'me')->name('user_details');
        Route::get('/resend-verification-pin', 'resend_verification_otp')->name('resend_user_otp');
    });

    Route::controller(SchoolController::class)->group(function(){
        Route::post('/school/locations', 'add_locations')->name('school.add_locations');
        Route::get('/switch-location/{location}', 'switch_location')->name('switch_location');
    });

    Route::controller(ClassController::class)->group(function(){
        Route::get('classes', 'index')->name('classes.index');
        Route::post('classes', 'store')->name('classes.store');
        Route::get('classes/{class}', 'show')->name('classes.show');
        Route::get('other-locations', 'other_locations')->name('other_locations');
        Route::post('import-classes', 'import_class')->name('classes.import');
        Route::put('classes/{class}', 'update')->name('classes.update');
        Route::put('classes/sub-classes/{sub_class}', 'update_subClass')->name('classes.subClass.update');
        Route::delete('classes/{class}', 'destroy')->name('classes.delete');
        Route::delete('classes/sub-classes/{class}', 'destroy_subClass')->name('classes.subClass.delete');
    });
});
