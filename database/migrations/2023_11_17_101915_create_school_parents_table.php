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
        Schema::create('school_parents', function (Blueprint $table) {
            $table->id();
            $table->string('uuid');
            $table->foreignIdFor(School::class, 'school_id');
            $table->foreignIdFor(SchoolLocation::class, 'school_location_id');
            $table->string('title');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('mobile');
            $table->string('email')->nullable();
            $table->string('nationality');
            $table->string('occupation')->nullable();
            $table->string('address');
            $table->string('town');
            $table->string('lga');
            $table->string('state');
            $table->string('country')->default('Nigeria');
            $table->string('file_path')->nullable();
            $table->string('file_url')->nullable();
            $table->integer('file_size')->nullable();
            $table->string('file_disk')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_parents');
    }
};
