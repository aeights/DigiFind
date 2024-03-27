<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PublicReport>
 */
class PublicReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => fake()->numberBetween(1,3),
            'public_category_id' => fake()->numberBetween(1,3),
            'title' => $this->faker->sentence,
            'date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'location' => $this->faker->address,
            'description' => $this->faker->paragraph,
        ];
    }
}
