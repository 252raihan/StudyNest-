<?php

namespace Database\Factories;

use App\Models\University;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<University>
 */
class UniversityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company().' University';

        return [
            'name' => $name,
            'code' => Str::upper(Str::substr(Str::slug($name, ''), 0, 4)).fake()->unique()->numberBetween(10, 99),
            'status' => true,
        ];
    }

    /**
     * Indicate that the university is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => false,
        ]);
    }
}
