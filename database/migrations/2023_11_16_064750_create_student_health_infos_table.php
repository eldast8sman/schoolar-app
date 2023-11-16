<?php

use App\Models\SchoolStudent;
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
        Schema::create('student_health_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(SchoolStudent::class, 'school_student_id');
            $table->float('weight')->nullable();
            $table->string('weight_measurement')->nullable();
            $table->float('height')->nullable();
            $table->string('height_measurement')->nullable();
            $table->string('blood_group')->nullable();
            $table->string('genotype')->nullable();
            $table->text('immunizations')->nullable();
            $table->boolean('disabled')->default(false);
            $table->text('disability')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_health_infos');
    }
};
