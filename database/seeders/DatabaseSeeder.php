<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AboutTypeSeeder::class,
            AboutSeeder::class,
            PublicCategorySeeder::class,
            PublicReportSeeder::class,
            ReportTypeSeeder::class,
            PublicationPackageSeeder::class,
            LostCategorySeeder::class,
            LostReportSeeder::class,
            MediaTypeSeeder::class,
            MediaSeeder::class,
        ]);
    }
}
