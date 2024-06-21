<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
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
            'lost_report_id' => fake()->numberBetween(1,50),
            'publication_package_id' => fake()->numberBetween(1,3),
            'transaction_status_id' => fake()->numberBetween(1,5),
            'reward' => fake()->numberBetween(100000,1000000000),
            'total' => fake()->numberBetween(100000,1000000000),
            'expired' => fake()->dateTimeBetween('now','1 year'),
            'transaction_date' => fake()->dateTimeBetween('now','1 year'),
        ];
    }
}
