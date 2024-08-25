<?php

use App\Models\School;
use App\Models\SchoolLocation;
use App\Models\TimeTableConfiguration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('time_table_lesson_plans', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique()->index()->default(Str::uuid());
            $table->foreignIdFor(School::class, 'school_id');
            $table->foreignIdFor(SchoolLocation::class, 'school_location_id')->index();
            $table->foreignIdFor(TimeTableConfiguration::class, 'time_table_configuration_id')->index();
            $table->string('lesson_start_time')->nullable();
            $table->string('lesson_end_time')->nullable();
            $table->text('lesson_days');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_table_lesson_plans');
    }
};
