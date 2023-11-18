<?php

use App\Models\SchoolParent;
use App\Models\Parent\Parents;
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
        Schema::create('parent_school_parents', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Parents::class, 'parents_id');
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
