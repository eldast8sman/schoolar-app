<?php

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
        Schema::table('school_locations', function (Blueprint $table) {
            $table->string('location_type')->default('secondary')->after('school_id');
            $table->string('syllabus')->default('waec')->after('location_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_locations', function (Blueprint $table) {
            $table->dropColumn('location_type');
            $table->dropColumn('syllabus');
        });
    }
};
