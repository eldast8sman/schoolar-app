<?php

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
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('mobile')->unique();
            $table->string('password')->nullable();
            $table->string('token')->nullable();
            $table->dateTime('token_expiry')->nullable();
            $table->integer('school_id')->nullable();
            $table->integer('school_location_id')->nullable();
            $table->string('profile_photo_path')->nullable();
            $table->string('profile_photo_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
