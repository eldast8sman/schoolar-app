<?php

use App\Models\FileManager;
use App\Models\MainClass;
use App\Models\School;
use App\Models\SchoolLocation;
use App\Models\SchoolSession;
use App\Models\SchoolTeacher;
use App\Models\SchoolTerm;
use App\Models\SubClass;
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
        Schema::create('teacher_lesson_plans', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique()->index();
            $table->foreignIdFor(School::class, 'school_id');
            $table->foreignIdFor(SchoolLocation::class, 'school_location_id');
            $table->foreignIdFor(MainClass::class, 'main_class_id');
            $table->foreignIdFor(SubClass::class, 'sub_class_id');
            $table->foreignIdFor(SchoolSession::class, 'session_id');
            $table->foreignIdFor(SchoolTerm::class, 'term_id');
            $table->foreignIdFor(Subject::class, 'subject_id')->index();
            $table->foreignIdFor(SchoolTeacher::class, 'teacher_id')->index();
            $table->foreignIdFor(FileManager::class, 'document_id')->nullable();
            $table->boolean('approval_status')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_lesson_plans');
    }
};
