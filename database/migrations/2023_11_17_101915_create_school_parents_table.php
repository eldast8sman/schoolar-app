<?php

use App\Models\School;
use App\Models\SchoolLocation;
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
        Schema::create('school_parents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->index()->unique()->default(Str::uuid());
            $table->foreignIdFor(School::class, 'school_id')->index();
            $table->foreignIdFor(SchoolLocation::class, 'school_location_id')->index();
            $table->string('title');
            $table->string('first_name')->index();
            $table->string('last_name')->index();
            $table->string('mobile');
            $table->string('email')->nullable();
            $table->string('nationality');
            $table->string('occupation')->nullable();
            $table->string('address');
            $table->string('town');
            $table->string('lga');
            $table->string('state');
            $table->string('country')->default('Nigeria');
            $table->integer('photo')->nullable();
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
