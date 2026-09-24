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
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();

            // `order` is a reserved SQL keyword. Laravel quotes it as `order`
            // in generated SQL, which is valid MySQL. It drives topic ordering
            // (1, 2, 3 ...) when listing topics under an exam.
            $table->integer('order')->default(0);

            $table->timestamps();

            // Ordering must be unambiguous within a single exam.
            $table->unique(['exam_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topics');
    }
};
