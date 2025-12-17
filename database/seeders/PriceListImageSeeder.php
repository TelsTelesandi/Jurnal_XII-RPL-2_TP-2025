<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class PriceListImageSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // AQUA
            ['brand' => 'AQUA', 'name' => 'AQUA Galon 19L', 'size' => '19L', 'price' => 16000, 'desc' => 'Galon 19 liter', 'stock' => 100],
            ['brand' => 'AQUA', 'name' => 'AQUA Botol 1.5L (Dus)', 'size' => '1.5L', 'price' => 53000, 'desc' => 'Harga per dus 1.5L', 'stock' => 50],
            ['brand' => 'AQUA', 'name' => 'AQUA Botol 600ml (Dus)', 'size' => '600ml', 'price' => 45500, 'desc' => 'Harga per dus 600ml', 'stock' => 50],
            ['brand' => 'AQUA', 'name' => 'AQUA Botol 330ml (Dus)', 'size' => '330ml', 'price' => 37000, 'desc' => 'Harga per dus 330ml', 'stock' => 50],
            ['brand' => 'AQUA', 'name' => 'AQUA Gelas 220ml', 'size' => '220ml', 'price' => 31000, 'desc' => 'Harga per dus gelas 220ml', 'stock' => 50],

            // VIT
            ['brand' => 'VIT', 'name' => 'VIT Galon 19L', 'size' => '19L', 'price' => 13000, 'desc' => 'Galon 19 liter', 'stock' => 100],
            ['brand' => 'VIT', 'name' => 'VIT Botol 1.5L (Dus)', 'size' => '1.5L', 'price' => 33000, 'desc' => 'Harga per dus 1.5L', 'stock' => 50],
            ['brand' => 'VIT', 'name' => 'VIT Botol 600ml (Dus)', 'size' => '600ml', 'price' => 32000, 'desc' => 'Harga per dus 600ml', 'stock' => 50],
            ['brand' => 'VIT', 'name' => 'VIT Botol 330ml (Dus)', 'size' => '330ml', 'price' => 31000, 'desc' => 'Harga per dus 330ml', 'stock' => 50],
            ['brand' => 'VIT', 'name' => 'VIT Gelas 220ml', 'size' => '220ml', 'price' => 21000, 'desc' => 'Harga per dus gelas 220ml', 'stock' => 50],

            // Le Minerale
            ['brand' => 'Le Minerale', 'name' => 'Le Minerale Galon 15L', 'size' => '15L', 'price' => 17500, 'desc' => 'Galon 15 liter', 'stock' => 100],
            ['brand' => 'Le Minerale', 'name' => 'Le Minerale Botol 1.5L (Dus)', 'size' => '1.5L', 'price' => 53000, 'desc' => 'Harga per dus 1.5L', 'stock' => 50],
            ['brand' => 'Le Minerale', 'name' => 'Le Minerale Botol 600ml (Dus)', 'size' => '600ml', 'price' => 49000, 'desc' => 'Harga per dus 600ml', 'stock' => 50],
            ['brand' => 'Le Minerale', 'name' => 'Le Minerale Botol 330ml (Dus)', 'size' => '330ml', 'price' => 38500, 'desc' => 'Harga per dus 330ml', 'stock' => 50],

            // Cleo
            ['brand' => 'Cleo', 'name' => 'Cleo Galon 19L', 'size' => '19L', 'price' => 17000, 'desc' => 'Galon 19 liter', 'stock' => 100],
            ['brand' => 'Cleo', 'name' => 'Cleo Botol 1.5L (Dus)', 'size' => '1.5L', 'price' => 41000, 'desc' => 'Harga per dus 1.5L', 'stock' => 50],
            ['brand' => 'Cleo', 'name' => 'Cleo Botol 600ml (Dus)', 'size' => '600ml', 'price' => 37000, 'desc' => 'Harga per dus 600ml', 'stock' => 50],
            ['brand' => 'Cleo', 'name' => 'Cleo Botol 330ml (Dus)', 'size' => '330ml', 'price' => 36000, 'desc' => 'Harga per dus 330ml', 'stock' => 50],
            ['brand' => 'Cleo', 'name' => 'Cleo Gelas 200ml', 'size' => '200ml', 'price' => 22000, 'desc' => 'Harga per dus gelas 200ml', 'stock' => 50],
            ['brand' => 'Cleo', 'name' => 'Cleo Botol 220ml (Dus)', 'size' => '220ml', 'price' => 22000, 'desc' => 'Harga per dus 220ml', 'stock' => 50],

            // Prima
            ['brand' => 'Prima', 'name' => 'Prima Botol 1.5L (Dus)', 'size' => '1.5L', 'price' => 35000, 'desc' => 'Harga per dus 1.5L', 'stock' => 50],
            ['brand' => 'Prima', 'name' => 'Prima Botol 600ml (Dus)', 'size' => '600ml', 'price' => 31000, 'desc' => 'Harga per dus 600ml', 'stock' => 50],
            ['brand' => 'Prima', 'name' => 'Prima Botol 330ml (Dus)', 'size' => '330ml', 'price' => 29000, 'desc' => 'Harga per dus 330ml', 'stock' => 50],
        ];

        foreach ($items as $p) {
            Product::updateOrCreate(
                ['brand' => $p['brand'], 'name' => $p['name'], 'size' => $p['size']],
                [
                    'description' => $p['desc'],
                    'category' => 'air_mineral',
                    'price' => $p['price'],
                    'stock' => $p['stock'],
                    'image_url' => $p['image_url'] ?? null,
                    'is_active' => true,
                    'specifications' => null,
                ]
            );
        }
    }
}
