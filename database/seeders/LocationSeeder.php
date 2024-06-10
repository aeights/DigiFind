<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Path to the SQL file
        $path = database_path('seeders/sql/wilayah-indonesia-edited.sql');

        // Read the SQL file
        $sql = File::get($path);

        // Execute the SQL file
        DB::unprepared($sql);
    }
}
