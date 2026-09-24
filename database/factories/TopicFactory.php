<?php

namespace Database\Factories;

use App\Models\Exam;
use App\Models\Topic;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Topic>
 */
class TopicFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'exam_id' => Exam::factory(),
            'name' => fake()->unique()->sentence(3),
            'description' => fake()->optional()->sentence(),
            'order' => fake()->numberBetween(1, 50),
        ];
    }

    /**
     * Attach the topic to an existing exam.
     */
    public function forExam(Exam $exam): static
    {
        return $this->state(fn (array $attributes) => [
            'exam_id' => $exam->id,
        ]);
    }
}
