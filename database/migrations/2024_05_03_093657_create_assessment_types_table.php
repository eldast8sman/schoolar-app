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
        Schema::create('assessment_types', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(School::class, 'school_id');
            $table->foreignIdFor(SchoolLocation::class, 'school_location_id');
            $table->text('assessment_scores');
            $table->double('minimum_pass_score')->default(50);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_types');
    }
};
