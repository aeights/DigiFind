<?php

namespace Database\Seeders;

use App\Models\PublicationPackage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PublicationPackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $package = [
            [
                'name' => 'Paket 1',
                'amount' => '5',
                'duration' => '7',
                'price' => '5000',
            ],
            [
                'name' => 'Paket 2',
                'amount' => '20',
                'duration' => '30',
                'price' => '15000',
            ],
            [
                'name' => 'Paket 3',
                'amount' => '40',
                'duration' => '30',
                'price' => '25000',
            ],
            [
                'name' => 'Paket 4',
                'amount' => '50',
                'duration' => '90',
                'price' => '35000',
            ],
        ];

        foreach ($package as $key => $value) {
            PublicationPackage::create($value);
        }
    }
}
