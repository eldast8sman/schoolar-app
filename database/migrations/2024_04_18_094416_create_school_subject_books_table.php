<?php

use App\Models\MainClass;
use App\Models\School;
use App\Models\SchoolLocation;
use App\Models\SubClass;
use App\Models\Subject;
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
        Schema::create('school_subject_books', function (Blueprint $table) {
            $table->id();
            $table->uuid()->index()->unique()->default(Str::uuid());
            $table->foreignIdFor(School::class, 'school_id');
            $table->foreignIdFor(SchoolLocation::class, 'school_location_id')->index();
            $table->foreignIdFor(MainClass::class, 'main_class_id')->index();
            $table->foreignIdFor(SubClass::class, 'sub_class_id')->index();
            $table->foreignIdFor(Subject::class, 'subject_id')->index();
            $table->string('subject_name')->index();
            $table->string('book_name');
            $table->string('authors');
            $table->string('year_published');
            $table->boolean('compulsory')->default(false);
            $table->boolean('can_purchase_externally')->default(false);
            $table->double('cost')->default(0.00);
            $table->integer('photo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_subject_books');
    }
};
