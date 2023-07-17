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
        Schema::table('main_classes', function (Blueprint $table) {
            $table->renameColumn('teacher_id', 'school_teacher_id');
        });
        Schema::table('sub_classes', function (Blueprint $table) {
            $table->renameColumn('teacher_id', 'school_teacher_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('main_classes', function (Blueprint $table) {
            $table->renameColumn('school_teacher_id', 'teacher_id');
        });
        Schema::table('sub_classes', function (Blueprint $table) {
            $table->renameColumn('school_teacher_id', 'teacher_id');
        });
    }
};
