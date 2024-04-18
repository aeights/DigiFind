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
                'slug' => 'jalan',
                'name' => 'Jalan'
            ],
            [
                'slug' => 'kendaraan',
                'name' => 'Kendaraan'
            ],
            [
                'slug' => 'bangunan',
                'name' => 'Bangunan'
            ],
        ];

        foreach ($categories as $key => $value) {
            PublicCategory::create($value);
        }
    }
}
