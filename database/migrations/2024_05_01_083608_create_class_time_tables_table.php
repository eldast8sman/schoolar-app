<?php

use App\Models\MainClass;
use App\Models\School;
use App\Models\SchoolLocation;
use App\Models\SubClass;
use App\Models\TimeTableClassGroup;
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
        Schema::create('class_time_tables', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique()->index()->default(Str::uuid());
            $table->foreignIdFor(School::class, 'school_id');
            $table->foreignIdFor(SchoolLocation::class, 'school_location_id');
            $table->foreignIdFor(TimeTableClassGroup::class, 'time_table_class_group_id');
            $table->string('day');//Monday, Tuesday, Wednesday, Thursday, Friday
            $table->text('time_breakdown');//array
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_time_tables');
    }
};
