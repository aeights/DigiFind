<?php

namespace Database\Seeders;

use App\Models\Transaction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Transaction::factory()->count(50)->create();

        $lostReportIds = range(1, 50); // Array dari 1 hingga 50

        foreach ($lostReportIds as $lostReportId) {
            Transaction::factory()->create([
                'user_id' => fake()->numberBetween(1, 11),
                'lost_report_id' => $lostReportId,
                'publication_package_id' => fake()->numberBetween(1, 3),
                'transaction_status_id' => fake()->numberBetween(1, 5),
                'reward' => fake()->numberBetween(100000, 1000000000),
                'total' => fake()->numberBetween(100000, 1000000000),
                'expired' => fake()->dateTimeBetween('now', '1 year'),
                'transaction_date' => fake()->dateTimeBetween('now', '1 year'),
            ]);
        }
    }
}
