<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\SchoolTeacherController;
use App\Http\Controllers\Teacher\AuthController as TeacherAuthController;
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
        Route::get('/logout', 'logout')->name('user.logout');
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
        Route::post('/classes/sort-class/by-level', 'sort_class_level')->name('classes.sortClassLevel');
        Route::put('classes/sub-classes/{sub_class}', 'update_subClass')->name('classes.subClass.update');
        Route::delete('classes/{class}', 'destroy')->name('classes.delete');
        Route::delete('classes/sub-classes/{class}', 'destroy_subClass')->name('classes.subClass.delete');
    });

    Route::controller(SchoolTeacherController::class)->group(function(){
        Route::get('/school-teachers', 'index')->name('schoolTacher.index');
        Route::post('/school-teachers', 'store')->name('schoolTeacher.store');
        Route::get('/school-teachers/{teacher}', 'show')->name('schoolTeacher.show');
        Route::post('/school-teachers/{id}', 'update')->name('schoolTeacher.update');
        Route::delete('/school-teachers/{teacher}', 'destroy')->name('schoolTeacher.delete');
        Route::post('/teacher-certifications', 'add_certification')->name('certification.add');
        Route::post('/teacher-certifications/{id}', 'update_certification')->name('certification.update');
        Route::delete('/teacher-certifications/{certification}', 'remove_certification')->name('certification.delete');
    });
});

Route::prefix('teachers')->group(function(){
    Route::controller(TeacherAuthController::class)->group(function(){
        Route::get('/fetch-by-token/{token}', 'fetch_by_token')->name('teacher.fetchByToken');
        Route::post('/activate', 'activate_account')->name('teacher.activate');
        Route::post('/login', 'login')->name('teacher.login');
        Route::post('/forgot-password', 'forgot_password')->name('teacher.forgotPassword');
        Route::post('/reset-password', 'reset_password')->name('teacher.resetPassword');
    });

    Route::middleware('auth:teacher-api')->group(function(){
        Route::controller(TeacherAuthController::class)->group(function(){
            Route::get('/me', 'me')->name('teacher.me');
            Route::get('/logout', 'logout')->name('teacher.logout');
        });
    });
});
