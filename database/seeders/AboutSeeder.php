<?php

namespace Database\Seeders;

use App\Models\About;
use App\Models\Media;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

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
            // $asset->addMedia($path[$key])->preservingOriginal()->toMediaCollection('onboarding');
            $mediaPath = 'media/content';
            $extension = pathinfo($path[$key], PATHINFO_EXTENSION);
            $fileName = time().'-'.$asset->id.'.'.$extension;
            $size = File::size($path[$key]);
            Media::create([
                'model_id' => $asset->id,
                'media_type_id' => 1,
                'file_name' => $fileName,
                'path' => $mediaPath,
                'url' => $mediaPath.'/'.$fileName,
                'mime_type' => $extension,
                'size' => $size
            ]);
            copy($path[$key],public_path().'/'.$mediaPath.'/'.$fileName);
        }
    }
}
