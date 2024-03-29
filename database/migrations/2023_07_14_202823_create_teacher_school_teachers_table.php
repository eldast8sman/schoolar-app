<?php

use App\Models\School;
use App\Models\SchoolLocation;
use App\Models\SchoolTeacher;
use App\Models\Teacher\Teacher;
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
        Schema::create('teacher_school_teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Teacher::class, 'teacher_id');
            $table->foreignIdFor(SchoolTeacher::class, 'school_teacher_id');
            $table->foreignIdFor(School::class, 'school_id');
            $table->foreignIdFor(SchoolLocation::class, 'school_location_id');
            $table->integer('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_school_teachers');
    }
};
