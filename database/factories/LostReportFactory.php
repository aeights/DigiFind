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
        $villages = ['11.01.02.2009','11.02.09.2012','11.03.10.2021','11.03.13.2007','12.06.16.2012','14.06.06.2004','35.09.12.2003'];
        
        return [
            'user_id' => fake()->numberBetween(1,11),
            'lost_category_id' => fake()->numberBetween(1,4),
            'name' => fake()->sentence,
            'unique_number' => fake()->randomElement([null,fake()->randomLetter()]),
            'description' => fake()->paragraph,
            'date' => fake()->dateTimeBetween('-1 year', 'now'),
            'village_code' => fake()->randomElement($villages),
            'location_detail' => fake()->address,
        ];
    }
}
