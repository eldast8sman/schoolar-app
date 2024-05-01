<?php

use App\Models\School;
use App\Models\SchoolLocation;
use App\Models\TimeTableBreakTime;
use App\Models\TimeTableLessonPlan;
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
        Schema::create('time_table_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('uuid');
            $table->foreignIdFor(School::class, 'school_id');
            $table->foreignIdFor(SchoolLocation::class, 'school_location_id');
            $table->string('assembly_time')->nullable();
            $table->string('lecture_start')->nullable();
            $table->string('lecture_end')->nullable();
            $table->boolean('configure_break_time')->default(true);
            $table->boolean('configure_lesson')->default(false);
            $table->integer('time_table_lesson_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_table_configurations');
    }
};
