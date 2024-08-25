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
        Schema::create('main_classes', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique()->index()->default(Str::uuid());
            $table->foreignIdFor(School::class, 'school_id')->index();
            $table->foreignIdFor(SchoolLocation::class, 'school_location_id')->index();
            $table->integer('class_level');
            $table->string('name');
            $table->integer('teacher_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('main_classes');
    }
};
