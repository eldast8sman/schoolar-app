<?php

use App\Models\School;
use App\Models\SchoolLocation;
use App\Models\TimeTableConfiguration;
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
        Schema::create('time_table_break_times', function (Blueprint $table) {
            $table->id();
            $table->string('uuid');
            $table->foreignIdFor(School::class, 'school_id');
            $table->foreignIdFor(SchoolLocation::class, 'school_location_id');
            $table->foreignIdFor(TimeTableConfiguration::class, 'time_table_configuration_id');
            $table->string('break_name');
            $table->string('break_start_time')->nullable();
            $table->string('break_end_time')->nullable();
            $table->text('break_days');//array
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_table_break_times');
    }
};
