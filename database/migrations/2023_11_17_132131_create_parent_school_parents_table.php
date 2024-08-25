<?php

use App\Models\Parent\Parents;
use App\Models\SchoolParent;
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
        Schema::create('parent_school_parents', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique()->default(Str::uuid());
            $table->foreignIdFor(Parents::class, 'parents_id')->index();
            $table->foreignIdFor(SchoolParent::class, 'school_parent_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parent_school_parents');
    }
};
