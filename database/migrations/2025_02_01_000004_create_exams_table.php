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
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')
                ->constrained()
                ->cascadeOnDelete();

            // Stored as a string rather than a MySQL ENUM so that exam naming
            // systems beyond "midterm"/"final" (quiz, viva, practical) can be
            // added without a schema migration. Validated at the app layer.
            $table->string('type', 50);

            $table->string('title');
            $table->timestamps();

            // One exam of a given type per course.
            $table->unique(['course_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
