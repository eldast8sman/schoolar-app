<?php

use App\Models\School;
use App\Models\SchoolLocation;
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
        Schema::create('school_teachers', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique()->index();
            $table->foreignIdFor(School::class, 'school_id')->index();
            $table->foreignIdFor(SchoolLocation::class, 'school_location_id')->index();
            $table->string('first_name')->index();
            $table->string('last_name')->index();
            $table->string('mobile');
            $table->string('email');
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->default('Nigeria');
            $table->string('trcn_registration_number')->nullable();
            $table->string('profile_photo_path')->nullable();
            $table->string('profile_photo_url')->nullable();
            $table->string('file_disk')->nullable();
            $table->integer('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_teachers');
    }
};
