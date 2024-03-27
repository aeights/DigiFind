<?php

namespace Database\Seeders;

use App\Models\About;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AboutSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $assets = [
            [
                'about_type_id' => 1,
                'title' => 'Temukan',
                'description' => 'Temukan barang yang  kamu miliki dengan bantuan teman di sekitarmu',
            ],
            [
                'about_type_id' => 1,
                'title' => 'Laporkan Fasilitas Publik',
                'description' => 'Laporkan keresahanmu terhadap layanan publik dan dinas akan mendengarkan keluhan itu',
            ],
            [
                'about_type_id' => 1,
                'title' => 'Dapatkan Hadiah',
                'description' => 'Bantu orang disekitar anda dan dapatkan hadiah menarik serta uang tunai',
            ],
        ];
        $path = [
            public_path('assets/search-barang-biru.gif'),
            public_path('assets/siaran-biru.gif'),
            public_path('assets/gift-biru.gif'),
        ];
        foreach ($assets as $key => $value) {
            $asset = About::create($value);
            $asset->addMedia($path[$key])->preservingOriginal()->toMediaCollection('onboarding');
        }
    }
}
