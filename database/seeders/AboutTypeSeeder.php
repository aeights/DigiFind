<?php

namespace Database\Seeders;

use App\Models\AboutType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AboutTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'type' => 'onboarding',
            ],
            [
                'type' => 'contact',
            ],
            [
                'type' => 'about',
            ],
        ];

        foreach ($types as $key => $value) {
            AboutType::create($value);
        }
    }
}
