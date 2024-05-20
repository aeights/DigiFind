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
            ['name' => 'Lalu lintas', 'slug' => Str::slug('Lalu lintas')],
            ['name' => 'Transportasi umum', 'slug' => Str::slug('Transportasi umum')],
            ['name' => 'Polusi udara', 'slug' => Str::slug('Polusi udara')],
            ['name' => 'Kesehatan', 'slug' => Str::slug('Kesehatan')],
            ['name' => 'Layanan kesehatan', 'slug' => Str::slug('Layanan kesehatan')],
            ['name' => 'Pendidikan', 'slug' => Str::slug('Pendidikan')],
            ['name' => 'Perubahan iklim', 'slug' => Str::slug('Perubahan iklim')],
            ['name' => 'Pencemaran air, tanah, dan udara', 'slug' => Str::slug('Pencemaran air, tanah, dan udara')],
            ['name' => 'Kerusakan hutan', 'slug' => Str::slug('Kerusakan hutan')],
            ['name' => 'Layanan publik', 'slug' => Str::slug('Layanan publik')],
            ['name' => 'Pekerjaan', 'slug' => Str::slug('Pekerjaan')],
            ['name' => 'Privasi data', 'slug' => Str::slug('Privasi data')],
            ['name' => 'Tindakan kriminal', 'slug' => Str::slug('Tindakan kriminal')],
            ['name' => 'Pembangunan inklusif', 'slug' => Str::slug('Pembangunan inklusif')],
        ];

        foreach ($types as $key => $value) {
            LostCategory::create($value);
        }
    }
}
