<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function(){
    Route::post('/signup', 'store')->name('user.signup');
});

Route::middleware('auth:user-api')->group(function(){
    Route::controller(AuthController::class)->group(function(){
        Route::get('/verify-email/{pin}', 'verify_email')->name('verify_user_email');
        Route::get('/me', 'me')->name('user_details');
        Route::get('/resend-verification-pin', 'resend_verification_otp')->name('resend_user_otp');
    });
});
