<?php

use App\Models\School;
use App\Models\SchoolLocation;
use App\Models\SchoolSession;
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
        Schema::create('school_terms', function (Blueprint $table) {
            $table->id();
            $table->string('uuid');
            $table->foreignIdFor(School::class, 'school_id');
            $table->foreignIdFor(SchoolLocation::class, 'school_location_id');
            $table->foreignIdFor(SchoolSession::class, 'school_session_id');
            $table->string('term_name');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('position')->nullable();
            $table->integer('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_terms');
    }
};
