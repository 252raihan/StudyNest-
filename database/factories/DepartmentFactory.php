<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\University;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Department>
 */
class DepartmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'university_id' => University::factory(),
            'name' => fake()->unique()->words(3, true),
            'code' => Str::upper(Str::substr(Str::slug(fake()->unique()->words(1, true), ''), 0, 3)).fake()->unique()->numberBetween(100, 999),
            'status' => true,
        ];
    }

    /**
     * Attach the department to an existing university.
     */
    public function forUniversity(University $university): static
    {
        return $this->state(fn (array $attributes) => [
            'university_id' => $university->id,
        ]);
    }

    /**
     * Indicate that the department is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => false,
        ]);
    }
}
