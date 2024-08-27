<?php

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
        Schema::create('parents', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique()->index();
            $table->string('first_name')->index();
            $table->string('last_name')->index();
            $table->string('mobile');
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('token')->nullable();
            $table->dateTime('token_expiry')->nullable();
            $table->string('nationality');
            $table->string('occupation')->nullable();
            $table->string('address');
            $table->string('town');
            $table->string('lga');
            $table->string('state');
            $table->string('country')->default('Nigeria');
            $table->integer('photo_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parents');
    }
};
