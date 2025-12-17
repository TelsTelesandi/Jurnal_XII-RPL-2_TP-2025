<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class InitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Users dengan kolom 'role' sederhana
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Administrator', 'password' => Hash::make('password'), 'role' => 'Admin']
        );
        User::firstOrCreate(
            ['email' => 'driver@example.com'],
            ['name' => 'Driver Satu', 'password' => Hash::make('password'), 'role' => 'Driver']
        );
        User::firstOrCreate(
            ['email' => 'customer@example.com'],
            ['name' => 'Customer Satu', 'password' => Hash::make('password'), 'role' => 'Customer']
        );
    }
}
