<?php

namespace Database\Seeders;

use App\Models\ReportType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReportTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'slug' => 'public_report',
                'name' => 'Public Report'
            ],
            [
                'slug' => 'lost_report',
                'name' => 'Lost Report'
            ],
        ];

        foreach ($data as $key => $value) {
            ReportType::create($value);
        }
    }
}
