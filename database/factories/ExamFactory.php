<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Exam;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Exam>
 */
class ExamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'type' => Exam::TYPE_MIDTERM,
            'title' => 'Midterm Examination',
        ];
    }

    /**
     * Attach the exam to an existing course.
     */
    public function forCourse(Course $course): static
    {
        return $this->state(fn (array $attributes) => [
            'course_id' => $course->id,
        ]);
    }

    /**
     * Indicate that the exam is a midterm.
     */
    public function midterm(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Exam::TYPE_MIDTERM,
            'title' => 'Midterm Examination',
        ]);
    }

    /**
     * Indicate that the exam is a final.
     */
    public function final(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Exam::TYPE_FINAL,
            'title' => 'Final Examination',
        ]);
    }
}
