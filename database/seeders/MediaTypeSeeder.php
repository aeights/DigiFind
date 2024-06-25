<?php

namespace Database\Seeders;

use App\Models\MediaType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MediaTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => 'Asset',
                'slug' => Str::slug('Asset')
            ],
            [
                'name' => 'Profile',
                'slug' => Str::slug('Profile')
            ],
            [
                'name' => 'Public Report',
                'slug' => Str::slug('Public Report')
            ],
            [
                'name' => 'Lost Report',
                'slug' => Str::slug('Lost Report')
            ],
            [
                'name' => 'Discovered Item',
                'slug' => Str::slug('Discovered Item')
            ],
        ];

        foreach ($types as $key => $value) {
            MediaType::create($value);
        }
    }
}
