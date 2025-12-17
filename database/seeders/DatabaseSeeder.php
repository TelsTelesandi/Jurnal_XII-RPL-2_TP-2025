<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\InitSeeder;
use Database\Seeders\DemoDataSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Jalankan seeder inisialisasi roles & users
        $this->call([
            InitSeeder::class,
            DemoDataSeeder::class,
            PriceListImageSeeder::class,
        ]);
    }
}
