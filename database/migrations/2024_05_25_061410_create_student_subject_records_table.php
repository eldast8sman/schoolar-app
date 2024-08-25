<?php

use App\Models\School;
use App\Models\SchoolLocation;
use App\Models\SchoolSession;
use App\Models\SchoolStudent;
use App\Models\SchoolTerm;
use App\Models\StudentSubject;
use App\Models\Subject;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_subject_records', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(School::class, 'school_id');
            $table->foreignIdFor(SchoolLocation::class, 'school_location_id');
            $table->foreignIdFor(SchoolSession::class, 'school_session_id');
            $table->foreignIdFor(SchoolTerm::class, 'school_term_id');
            $table->foreignIdFor(SchoolStudent::class, 'school_student_id');
            $table->foreignIdFor(Subject::class, 'subject_id');
            $table->foreignIdFor(StudentSubject::class, 'student_subject_id');
            $table->text('records')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_subject_records');
    }
};
