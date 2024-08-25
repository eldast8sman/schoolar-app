<?php

use App\Models\School;
use App\Models\SchoolLocation;
use App\Models\SchoolParent;
use App\Models\SchoolStudent;
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
        Schema::create('parent_students', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique()->index()->default(Str::uuid());
            $table->foreignIdFor(School::class, 'school_id')->index();
            $table->foreignIdFor(SchoolLocation::class, 'school_location_id')->index();
            $table->foreignIdFor(SchoolStudent::class, 'school_student_id')->index();
            $table->foreignIdFor(SchoolParent::class, 'school_parent_id')->index();
            $table->boolean('primary');
            $table->string('relationship');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parent_students');
    }
};
