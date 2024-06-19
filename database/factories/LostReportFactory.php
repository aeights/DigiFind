<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LostReport>
 */
class LostReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => fake()->numberBetween(1,10),
            'lost_category_id' => fake()->numberBetween(1,4),
            'name' => $this->faker->sentence,
            'unique_number' => fake()->randomElement([null,fake()->randomLetter()]),
            'description' => $this->faker->paragraph,
            'date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'village_code' => $this->faker->buildingNumber,
            'location_detail' => $this->faker->address,
        ];
    }
}
