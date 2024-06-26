<?php

namespace Database\Seeders;

use App\Models\LostReport;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LostReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LostReport::factory()->count(50)->create();
    }
}
