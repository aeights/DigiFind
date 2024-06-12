<?php

namespace Database\Seeders;

use App\Models\PublicComment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PublicCommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PublicComment::factory()->count(50)->create();
    }
}
