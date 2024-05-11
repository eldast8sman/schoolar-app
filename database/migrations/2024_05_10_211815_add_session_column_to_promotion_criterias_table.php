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
        Schema::table('promotion_criterias', function (Blueprint $table) {
            $table->integer('school_session_id')->nullable()->after('sub_class_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promotion_criterias', function (Blueprint $table) {
            $table->dropColumn('school_session_id');
        });
    }
};
