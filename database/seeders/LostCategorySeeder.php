<?php

namespace Database\Seeders;

use App\Models\LostCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LostCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Kendaraan', 'slug' => Str::slug('Kendaraan')],
            ['name' => 'Dompet', 'slug' => Str::slug('Dompet')],
            ['name' => 'Elektronik', 'slug' => Str::slug('Elektronik')],
            ['name' => 'Lainnya', 'slug' => Str::slug('Lainnya')],
        ];

        foreach ($types as $key => $value) {
            LostCategory::create($value);
        }
    }
}
