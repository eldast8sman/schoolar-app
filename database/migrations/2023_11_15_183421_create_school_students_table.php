<?php

use App\Models\MainClass;
use App\Models\School;
use App\Models\SchoolLocation;
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
        Schema::create('school_students', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique()->index();
            $table->string('first_name')->index();
            $table->string('middle_name')->nullable()->index();
            $table->string('last_name')->index();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->string('registration_id')->index();
            $table->foreignIdFor(School::class, 'school_id')->index();
            $table->foreignIdFor(SchoolLocation::class, 'school_location_id')->index();
            $table->foreignIdFor(MainClass::class, 'main_class_id')->index();
            $table->integer('class_level');
            $table->foreignIdFor(SubClass::class, 'sub_class_id')->index();
            $table->integer('photo')->nullable();
            $table->string('dob');
            $table->string('gender');
            $table->integer('registration_stage');
            $table->integer('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_students');
    }
};
