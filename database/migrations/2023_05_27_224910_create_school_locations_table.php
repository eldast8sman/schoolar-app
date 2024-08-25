<?php

use App\Models\School;
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
        Schema::create('school_locations', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique()->index()->default(Str::uuid());
            $table->foreignIdFor(School::class, 'school_id');
            $table->string('address')->default('');
            $table->string('town')->nullable();
            $table->string('lga')->nullable();
            $table->string('state');
            $table->string('country')->default('Nigeria');
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
