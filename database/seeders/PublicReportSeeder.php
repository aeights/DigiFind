<?php

namespace Database\Seeders;

use App\Models\PublicReport;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PublicReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PublicReport::factory()->count(50)->create();
    }
}
