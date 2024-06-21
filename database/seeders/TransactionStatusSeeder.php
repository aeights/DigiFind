<?php

namespace Database\Seeders;

use App\Models\TransactionStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['status' => 'Menunggu Pembayaran'],
            ['status' => 'Di Proses'],
            ['status' => 'Sedang Berlangsung'],
            ['status' => 'Selesai'],
            ['status' => 'Gagal'],
        ];

        foreach ($statuses as $status) {
            TransactionStatus::create($status);
        }
    }
}
