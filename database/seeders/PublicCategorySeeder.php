<?php

namespace Database\Seeders;

use App\Models\PublicCategory;
use App\Models\PublicSubCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PublicCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'Lalu Lintas' => ['Lampu Lalu Lintas', 'Jalan Raya'],
            'Transportasi Umum' => ['Pelayanan', 'Tarif', 'Keamanan'],
            'Polusi Udara' => ['Limbah pabrik', 'Polusi Lalu Lintas'],
            'Kesehatan' => ['Penyakit Menular', 'Kesehatan Mental', 'Gizi dan Nutrisi', 'Rumah Sakit', 'Pelayanan', 'Kebersihan', 'Rumah Sakit dan Klinik', 'Tenaga Medis', 'Fasilitas', 'Aksesibilitas', 'Asuransi Kesehatan'],
            'Pendidikan' => ['Kurikulum dan Pembelajaran', 'Fasilitas Pendidikan', 'Kualitas Pengajaran', 'Akses Pendidikan', 'Inovasi'],
            'Perubahan Iklim' => ['Mitigasi dan Adaptasi', 'Kebijakan Iklim', 'Edukasi'],
            'Pencemaran Air, Tanah, dan Udara' => ['Sungai', 'Danau', 'Sumur', 'Lahan Pertanian', 'Kebun', 'Taman', 'Hutan'],
            'Kerusakan Hutan' => ['Kebakaran Liar', 'Penebangan Liar'],
            'Layanan Publik' => ['Layanan Administratif', 'Infrastruktur Publik', 'Pelayanan Sosial', 'Pelayanan Darurat', 'Transparansi', 'Listrik'],
            'Pekerjaan' => ['Gaji', 'Asuransi kerja'],
            'Privasi Data' => ['Perlindungan Data Pribadi', 'Kebijakan Privasi', 'Keamanan Data'],
            'Tindakan Kriminal' => ['Kejahatan Jalanan', 'Kejahatan Siber', 'Penegakan Hukum', 'Rehabilitasi'],
            'Pembangunan Inklusif' => ['Pembangunan Berkelanjutan', 'Kesetaraan Gender', 'Inklusi Sosial', 'Aksesibilitas Infrastruktur', 'Keterlibatan Komunitas'],
        ];

        foreach ($data as $category => $subcategories) {
            $categoryModel = PublicCategory::create(['name' => $category , 'slug' => Str::slug($category)]);

            foreach ($subcategories as $subcategory) {
                PublicSubCategory::create([
                    'public_category_id' => $categoryModel->id,
                    'name' => $subcategory,
                    'slug' => Str::slug($subcategory)
                ]);
            }
        }
    }
}
