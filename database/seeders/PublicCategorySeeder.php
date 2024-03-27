<?php

namespace Database\Seeders;

use App\Models\PublicCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PublicCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Jalan',
                'slug' => 'jalan'
            ],
            [
                'name' => 'Kendaraan',
                'slug' => 'kendaraan'
            ],
            [
                'name' => 'Bangunan',
                'slug' => 'bangunan'
            ],
        ];

        foreach ($categories as $key => $value) {
            PublicCategory::create($value);
        }
    }
}
