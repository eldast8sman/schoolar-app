<?php

use App\Models\School;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('school_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(School::class, 'school_id');
            $table->string('address');
            $table->string('town')->nullable();
            $table->string('lga')->nullable();
            $table->string('state');
            $table->string('country')->default('Nigeria');
            $table->string('logo_url')->nullable();
            $table->string('logo_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_locations');
    }
};
