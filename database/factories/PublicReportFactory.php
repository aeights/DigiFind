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
        $villages = ['11.01.02.2009','11.02.09.2012','11.03.10.2021','11.03.13.2007'];
        
        return [
            'user_id' => fake()->numberBetween(1,3),
            'public_category_id' => fake()->numberBetween(1,3),
            'title' => $this->faker->sentence,
            'date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'village_code' => fake()->randomElement($villages),
            'location_detail' => $this->faker->address,
            'description' => $this->faker->paragraph,
        ];
    }
}
