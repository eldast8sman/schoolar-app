<?php

use App\Models\MainClass;
use App\Models\School;
use App\Models\SchoolLocation;
use App\Models\SchoolSession;
use App\Models\SchoolTerm;
use App\Models\SubClass;
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
        Schema::create('class_attendance_groups', function (Blueprint $table) {
            $table->id();
            $table->string('uuid');
            $table->foreignIdFor(School::class, 'school_id');
            $table->foreignIdFor(SchoolLocation::class, 'school_location_id');
            $table->foreignIdFor(MainClass::class, 'main_class_id');
            $table->foreignIdFor(SubClass::class, 'sub_class_id');
            $table->foreignIdFor(SchoolSession::class, 'session_id');
            $table->foreignIdFor(SchoolTerm::class, 'term_id');
            $table->string('attendance_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_attendance_groups');
    }
};
