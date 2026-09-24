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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 50);

            // Intentionally a bare integer with no enum constraint: semester
            // numbering differs per university ("Semester 3", "Year 2 Term 1",
            // "Trimester 5"). No assumption is baked into the schema.
            $table->unsignedTinyInteger('semester')->nullable();

            // Exact decimal rather than float to avoid rounding drift on
            // fractional credit values (e.g. 1.5).
            $table->decimal('credit', 4, 1)->nullable();

            $table->boolean('status')->default(true);
            $table->timestamps();

            // A course code is unique within its department, not globally.
            $table->unique(['department_id', 'code']);
            $table->index('status');
            $table->index('semester');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
