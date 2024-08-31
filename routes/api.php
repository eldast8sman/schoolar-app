<?php

use App\Http\Controllers\AssessmentTypeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\GradingSystemController;
use App\Http\Controllers\LessonPlanController;
use App\Http\Controllers\Parent\AuthController as ParentAuthController;
use App\Http\Controllers\SchoolAttendanceController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\SchoolParentController;
use App\Http\Controllers\SchoolStudentController;
use App\Http\Controllers\SchoolTeacherController;
use App\Http\Controllers\SchoolTimeTableController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\Student\AuthController as StudentAuthController;
use App\Http\Controllers\SubjectController;
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
        Route::put('/update-email', 'update_email')->name('updateEmail');
        Route::get('/logout', 'logout')->name('user.logout');
    });

    Route::controller(SchoolController::class)->group(function(){
        Route::post('/school/locations', 'add_locations')->name('school.add_locations');
        Route::get('/switch-location/{location}', 'switch_location')->name('switch_location');
    });

    Route::controller(ClassController::class)->group(function(){
        Route::post('load-default-classes', 'load_default_classes')->name('classes.loadDefault');
        Route::get('classes', 'index')->name('classes.index');
        Route::post('classes', 'store')->name('classes.store');
        Route::get('classes/{class}', 'show')->name('classes.show');
        Route::get('other-locations', 'other_locations')->name('other_locations');
        Route::post('import-classes', 'import_class')->name('classes.import');
        Route::put('classes/{class}', 'update')->name('classes.update');
        Route::post('/classes/sort-class/by-level', 'sort_class_level')->name('classes.sortClassLevel');
        Route::post('/classes/{class}/sub-classes', 'add_subclass')->name('classes.subClass.store');
        Route::post('/classes/{class}/assessment-types', 'store_class_assessment_type')->name('classes.assessmentType');
        Route::get('/sub-classes', 'all_sub_classes')->name('subClass.index');
        Route::get('/sub-classes/{subclass}', 'show_subclass')->name('subClass.show');
        Route::post('/sub-classes/{class}/assessment-types', 'store_subclass_assessment_type')->name('subclass.assessmentType');
        Route::put('classes/sub-classes/{sub_class}', 'update_subClass')->name('classes.subClass.update');
        Route::post('/classes/sub-classes/{subclass}/assign-teacher', 'assign_teacher')->name('classes.subClass.assignTeacher');
        Route::get('/classes/sub-classes/{subclass}/remove-teacher', 'remove_teacher')->name('claasses.subClass.removeTeacher');
        Route::get('/classes/sub-classes/{class}/students', 'students')->name('classes.subClass.student');
        Route::delete('classes/{class}', 'destroy')->name('classes.delete');
        Route::delete('classes/sub-classes/{class}', 'destroy_subClass')->name('classes.subClass.delete');
    });

    Route::controller(SchoolTeacherController::class)->group(function(){
        Route::get('/school-teachers', 'index')->name('schoolTacher.index');
        Route::post('/school-teachers', 'store')->name('schoolTeacher.store');
        Route::get('/school-teachers/{teacher}', 'show')->name('schoolTeacher.show');
        Route::get('/school-teachers/{teacher}/subjects', 'subjects')->name('schoolTeacher.subjects');
        Route::get('/school-teachers/{teacher}/classes', 'classes')->name('schoolTeacher.classes');
        Route::post('/school-teachers/{id}', 'update')->name('schoolTeacher.update');
        Route::delete('/school-teachers/{teacher}', 'destroy')->name('schoolTeacher.delete');
        Route::get('/school-teachers/{teacher}/certifications', 'certifications')->name('school_teacher.certification.index');
        Route::post('/teacher-certifications', 'add_certification')->name('certification.add');
        Route::post('/teacher-certifications/{id}', 'update_certification')->name('certification.update');
        Route::delete('/teacher-certifications/{certification}', 'remove_certification')->name('certification.delete');
    });

    Route::controller(SubjectController::class)->group(function(){
        Route::get('/classes/sub-classes/{class}/load-default-subjects', 'load_default_subjects')->name('classes.subClass.loadSubject');
        Route::post('/classes/sub-classes/{subclass}/subjects', 'store')->name('classes.subClass.addSubject');
        Route::post('/classes/sub-classes/{subclass}/subjects/multiple', 'store_multiple')->name('classes.subClass.addMultipleSubject');
        Route::get('/classes/sub-classes/{subclass}/subjects', 'index')->name('classes.subClass.fetchSubjects');
        Route::get('/subjects/{subject}', 'show')->name('subject.show');
        Route::put('/subjects/{subject}', 'update')->name('subjects.update');
        Route::post('/subjects/{subject}/assessment-types', 'assessment_type')->name('subject.assessmentType');
        Route::post('/subjects/{subject}/assign-primary-teacher', 'assign_primary_teacher')->name('subject.assignPrimaryTeacher');
        Route::post('/subjects/{subject}/assign-support-teacher', 'assign_secondary_teacher')->name('subject.assignSecondaryTeacher');

        Route::post('/subjects/books/{subject}', 'store_book')->name('classes.subClass.subject.addBook');
    });

    Route::controller(SchoolAttendanceController::class)->group(function(){
        Route::get('/attendance/{type}', 'index')->name('attendance.index');
        Route::get('/student-attendance/{student_id}/{type}', 'attendance_by_student')->name('student_attendance.attendance_by_student');
        Route::get('/class-attendance/{sub_class_id}', 'attendance_by_sub_class')->name('class_attendance.fetch_by_sub_class');
        Route::get('/subject-attendance/{subject_id}', 'attendance_by_subject')->name('subject_attendance.fetch_by_subject');
        Route::post('/class-attendance', 'store_class_attendance')->name('class_attendance.store_class_attendance');
        Route::post('/subject-attendance', 'store_subject_attendance')->name('class_attendance.store_subject_attendance');
        Route::get('/attendance/show/{uuid}/{type}', 'show')->name('class_attendance.show');
        
    });

    Route::controller(SchoolTimeTableController::class)->group(function(){
        Route::get('/time-table-configuration', 'time_table_configuration')->name('time_table_configuration.time_table_configuration');
        Route::post('/time-table-configuration', 'store_configuration')->name('time_table_configuration.store_configuration');
        Route::get('/time-table/{sub_class_id}', 'time_table_by_sub_class')->name('time_table.fetch_by_sub_class');
        Route::post('/time-table', 'store_time_table')->name('time_table.store_time_table');
    });

    Route::controller(LessonPlanController::class)->group(function(){
        Route::get('/lession-plans/session/{session_id}', 'lesson_plan_by_session')->name('lessonplans.lesson_plan_by_session');
        Route::get('/lession-plans/term/{term_id}', 'lesson_plan_by_term')->name('lessonplans.lesson_plan_by_term');
        Route::get('/lession-plans/teacher/{teacher_id}', 'lesson_plan_by_teacher')->name('lessonplans.lesson_plan_by_teacher');
        Route::get('/lession-plans/subject/{subject_id}', 'lesson_plan_by_subject')->name('lessonplans.lesson_plan_by_subject');
        Route::post('/lession-plans', 'store')->name('lesson_plan.store');
        Route::put('/lession-plans/{lesson_plan}', 'update')->name('lesson_plan.update');
        Route::get('/lession-plans/{lesson_plan}', 'show')->name('lesson_plan.show');
        Route::post('/lession-plans/approve/{lesson_plan}', 'approve_lesson_plan')->name('lesson_plan.approve_lesson_plan');
        Route::post('/lession-plans/decline/{lesson_plan}', 'decline_lesson_plan')->name('lesson_plan.decline_lesson_plan');
    });

    Route::controller(SchoolStudentController::class)->group(function(){
        Route::post('/school-students', 'store')->name('schoolStudent.store');
        Route::post('/school-students/{uuid}/health-records', 'store_health_info')->name('schoolStudent.healthInfo.store');
        Route::get('/school-students/{uuid}/skip-health-records', 'skip_health_info')->name('schoolStudent.healthInfo.skip');
        Route::post('/school-students/{uuid}/parents/new', 'store_new_parent')->name('schoolStudent.newParent.add');
        Route::post('/school-students/{uuid}/parents/existing', 'store_existing_parent')->name('schoolStudent.newParent.existing');
        Route::get('/school-students/{uuid}/skip-add-parents', 'skip_add_parent')->name('schoolStudent.newParent.skip');
        Route::get('/school-students', 'index')->name('schoolStudent.index');
        Route::post('/school-students/{uuid}', 'update')->name('schoolStudent.update');
        Route::get('/school-students/{uuid}', 'show')->name('schoolStudent.show');
    });

    Route::controller(SchoolParentController::class)->group(function(){
        Route::post('/school-parents', 'store')->name('schoolParent.store');
        Route::get('/school-parents', 'index')->name('schoolParent.index');
        Route::get('/school-parents/{uuid}', 'show')->name('schoolParent.show');
        Route::post('/school-parents/{uuid}/assign-student', 'assign_student')->name('schoolParent.assignStudent');
        Route::post('/school-parents/{uuid}', 'update')->name('schoolParent.update');
        Route::get('/school-parents/{uuid}/students', 'students')->name('schoolParent.student.index');
    });

    Route::controller(SessionController::class)->group(function(){
        Route::post('/school-sessions', 'store')->name('schoolSession.store');
        Route::get('/school-sessions', 'index')->name('schoolSession.index');
        Route::get('/school-session-terms/{uuid}', 'terms_by_session')->name('schoolSession.terms_by_session');
        Route::get('/school-sessions/{uuid}', 'show')->name('schoolSession.show');
        Route::post('/school-sessions/{uuid}/terms', 'store_term')->name('schoolSession.schoolTerm.store');
        Route::get('/school-terms/{uuid}', 'show_term')->name('schoolTerm.show');
        Route::put('/school-sessions/{session}', 'update')->name('schoolSession.update');
        Route::put('/school-terms/{uuid}', 'update_term')->name('schoolTerm.update');
        Route::delete('/school-sessions/{session}', 'destroy')->name('schoolSession.delete');
        Route::delete('/school-terms/{uuid}', 'destroy_term')->name('schoolTerm.delete');
    });

    Route::controller(GradingSystemController::class)->group(function(){
        Route::post('/grading-systems', 'store')->name('gradingSystem.post');
        Route::get('/grading-systems', 'index')->name('gradingSystem.index');
        Route::get('/grading-systems/{uuid}', 'show')->name('gradingSystem.show');
        Route::put('/grading-systems/{uuid}', 'update')->name('gradingSystem.update');
        Route::delete('/grading-systems/{uuid}', 'destroy')->name('gradingSystem.delete');
        Route::get('/grading-systems/load/default', 'load_default')->name('gradingSystem.loadDefault');
    });

    Route::controller(AssessmentTypeController::class)->group(function(){
        Route::get('/assessment-types/load/default', 'load_default')->name('assesmentType.loadDefault');
        Route::post('/assessment-types', 'store')->name('assessmentType.store');
        Route::get('/assessment-types', 'index')->name('assessmentType.index');
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

Route::prefix('students')->group(function(){
    Route::controller(StudentAuthController::class)->group(function(){
        Route::get('/fetch-by-token/{token}', 'fetch_by_token')->name('student.fetchByToken');
        Route::post('/activate', 'activate_account')->name('student.activate');
        Route::post('/login', 'login')->name('student.login');
        Route::post('/forgot-password', 'forgot_password')->name('student.forgotPassword');
        Route::post('/reset-password', 'reset_password')->name('student.resetPassword');
    });

    Route::middleware('auth:student-api')->group(function(){
        Route::controller(StudentAuthController::class)->group(function(){
            Route::get('/me', 'me')->name('student.me');
            Route::get('/logout', 'logout')->name('student.logout');
        });
    });
});

Route::prefix('parents')->group(function(){
    Route::controller(ParentAuthController::class)->group(function(){
        Route::get('/fetch-by-token/{token}', 'fetch_by_token')->name('student.fetchByToken');
        Route::post('/activate', 'activate_account')->name('student.activate');
        Route::post('/login', 'login')->name('student.login');
        Route::post('/forgot-password', 'forgot_password')->name('student.forgotPassword');
        Route::post('/reset-password', 'reset_password')->name('student.resetPassword');
    });

    Route::middleware('auth:parent-api')->group(function(){
        Route::controller(StudentAuthController::class)->group(function(){
            Route::get('/me', 'me')->name('student.me');
            Route::get('/logout', 'logout')->name('student.logout');
        });
    });
});
