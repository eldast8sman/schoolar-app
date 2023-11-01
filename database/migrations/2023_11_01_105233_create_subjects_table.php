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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(School::class, 'school_id');
            $table->foreignIdFor(SchoolLocation::class, 'school_location_id');
            $table->foreignIdFor(MainClass::class, 'main_class_id');
            $table->foreignIdFor(SubClass::class, 'sub_class_id');
            $table->string('name');
            $table->boolean('compulsory')->default(1);
            $table->integer('primary_teacher')->nullable();
            $table->integer('support_teacher')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
