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
        $villages = ['11.01.02.2009','11.02.09.2012','11.03.10.2021','11.03.13.2007','12.06.16.2012','14.06.06.2004','35.09.12.2003'];
        
        return [
            'user_id' => fake()->numberBetween(1,13),
            'public_category_id' => fake()->numberBetween(1,13),
            'public_sub_category_id' => fake()->numberBetween(1,20),
            'title' => fake()->sentence,
            'date' => fake()->dateTimeBetween('-1 year', 'now'),
            'village_code' => fake()->randomElement($villages),
            'location_detail' => fake()->address,
            'description' => fake()->paragraph,
        ];
    }
}
