<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vehicle;
use App\Models\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Vehicles demo
        $vehicles = [
            ['plate' => 'B 1234 XX', 'name' => 'Engkel 1', 'capacity_weight' => 3000, 'capacity_volume' => 12, 'active' => true],
            ['plate' => 'B 2345 YY', 'name' => 'Engkel 2', 'capacity_weight' => 2500, 'capacity_volume' => 10, 'active' => true],
            ['plate' => 'B 3456 ZZ', 'name' => 'CDD 1',    'capacity_weight' => 5000, 'capacity_volume' => 20, 'active' => true],
        ];
        foreach ($vehicles as $v) {
            Vehicle::firstOrCreate(['plate' => $v['plate']], $v);
        }

        // Orders demo around Jakarta (destination coords)
        $destinations = [
            ['Monas',        -6.175392, 106.827153],
            ['FX Sudirman',  -6.225251, 106.807483],
            ['Kota Tua',     -6.135200, 106.813301],
            ['Blok M',       -6.244680, 106.800430],
            ['Kelapa Gading',-6.164540, 106.907700],
        ];

        foreach ($destinations as $idx => [$name, $lat, $lng]) {
            Order::firstOrCreate(
                ['code' => 'ORD-DEMO-'.($idx+1)],
                [
                    'customer_id' => null,
                    'origin_name' => 'Gudang Pusat',
                    'origin_address' => 'Jl. Gudang No. 1, Jakarta',
                    'origin_lat' => -6.200000,
                    'origin_lng' => 106.816666,
                    'destination_name' => $name,
                    'destination_address' => $name.' - Jakarta',
                    'destination_lat' => $lat,
                    'destination_lng' => $lng,
                    'window_from' => Carbon::now()->startOfDay()->addHours(8),
                    'window_to' => Carbon::now()->startOfDay()->addHours(17),
                    'weight' => rand(50, 300),
                    'volume' => rand(1, 10),
                    'status' => 'pending',
                    'notes' => 'Demo order',
                    // Ecommerce required fields
                    'total_amount' => rand(100000, 500000),
                    'customer_name' => 'Demo Customer',
                    'customer_phone' => '081234567890',
                    'customer_email' => 'demo@example.com',
                    'shipping_address' => $name.' - Jakarta',
                    'payment_method' => 'cod',
                    'payment_status' => 'pending',
                    'validated_at' => null,
                    'validated_by' => null,
                    'assigned_driver_id' => null,
                    'assigned_at' => null,
                    'shipping_lat' => null,
                    'shipping_lng' => null,
                ]
            );
        }

        // Seed products berdasarkan gambar air mineral
        $products = [
            // AQUA
            [
                'name' => 'AQUA Air Mineral Galon',
                'brand' => 'AQUA',
                'description' => 'Air mineral AQUA dalam kemasan galon 19 liter, cocok untuk kebutuhan sehari-hari keluarga.',
                'size' => '19L',
                'price' => 16000,
                'stock' => 50,
                'image_url' => '/images/products/aqua-galon-19l.jpg'
            ],
            [
                'name' => 'AQUA Air Mineral Botol 1.5L',
                'brand' => 'AQUA',
                'description' => 'Air mineral AQUA dalam kemasan botol 1500ml, praktis untuk dibawa kemana-mana.',
                'size' => '1.5L',
                'price' => 5300,
                'stock' => 100,
                'image_url' => '/images/products/aqua-botol-1500ml.jpg'
            ],
            [
                'name' => 'AQUA Air Mineral Botol 600ml',
                'brand' => 'AQUA',
                'description' => 'Air mineral AQUA dalam kemasan botol 600ml, ukuran pas untuk konsumsi personal.',
                'size' => '600ml',
                'price' => 4550,
                'stock' => 200,
                'image_url' => '/images/products/aqua-botol-600ml.jpg'
            ],
            [
                'name' => 'AQUA Air Mineral Botol 330ml',
                'brand' => 'AQUA',
                'description' => 'Air mineral AQUA dalam kemasan botol 330ml, praktis dan ekonomis.',
                'size' => '330ml',
                'price' => 3700,
                'stock' => 150,
                'image_url' => '/images/products/aqua-botol-330ml.jpg'
            ],
            [
                'name' => 'AQUA Air Mineral Gelas 220ml',
                'brand' => 'AQUA',
                'description' => 'Air mineral AQUA dalam kemasan gelas 220ml, cocok untuk acara dan event.',
                'size' => '220ml',
                'price' => 3100,
                'stock' => 300,
                'image_url' => '/images/products/aqua-gelas-220ml.jpg'
            ],

            // VIT
            [
                'name' => 'VIT Air Mineral Galon',
                'brand' => 'VIT',
                'description' => 'Air mineral VIT dalam kemasan galon 19 liter, kualitas terjamin untuk keluarga.',
                'size' => '19L',
                'price' => 13000,
                'stock' => 40,
                'image_url' => '/images/products/vit-galon-19l.jpg'
            ],
            [
                'name' => 'VIT Air Mineral Botol 1.5L',
                'brand' => 'VIT',
                'description' => 'Air mineral VIT dalam kemasan botol 1500ml, segar dan berkualitas.',
                'size' => '1.5L',
                'price' => 3300,
                'stock' => 80,
                'image_url' => '/images/products/vit-botol-1500ml.jpg'
            ],
            [
                'name' => 'VIT Air Mineral Botol 600ml',
                'brand' => 'VIT',
                'description' => 'Air mineral VIT dalam kemasan botol 600ml, praktis untuk aktivitas sehari-hari.',
                'size' => '600ml',
                'price' => 3200,
                'stock' => 120,
                'image_url' => '/images/products/vit-botol-600ml.jpg'
            ],
            [
                'name' => 'VIT Air Mineral Botol 330ml',
                'brand' => 'VIT',
                'description' => 'Air mineral VIT dalam kemasan botol 330ml, ekonomis dan berkualitas.',
                'size' => '330ml',
                'price' => 3100,
                'stock' => 100,
                'image_url' => '/images/products/vit-botol-330ml.jpg'
            ],
            [
                'name' => 'VIT Air Mineral Gelas 220ml',
                'brand' => 'VIT',
                'description' => 'Air mineral VIT dalam kemasan gelas 220ml, cocok untuk berbagai acara.',
                'size' => '220ml',
                'price' => 2100,
                'stock' => 250,
                'image_url' => '/images/products/vit-gelas-220ml.jpg'
            ],

            // Le Minerale
            [
                'name' => 'Le Minerale Air Mineral Galon',
                'brand' => 'Le Minerale',
                'description' => 'Air mineral Le Minerale dalam kemasan galon 15 liter, dengan kandungan mineral alami.',
                'size' => '15L',
                'price' => 17500,
                'stock' => 30,
                'image_url' => '/images/products/leminerale-galon-15l.jpg'
            ],
            [
                'name' => 'Le Minerale Air Mineral Botol 1.5L',
                'brand' => 'Le Minerale',
                'description' => 'Air mineral Le Minerale dalam kemasan botol 1500ml, kaya mineral alami.',
                'size' => '1.5L',
                'price' => 5300,
                'stock' => 70,
                'image_url' => '/images/products/leminerale-botol-1500ml.jpg'
            ],
            [
                'name' => 'Le Minerale Air Mineral Botol 600ml',
                'brand' => 'Le Minerale',
                'description' => 'Air mineral Le Minerale dalam kemasan botol 600ml, segar dan menyehatkan.',
                'size' => '600ml',
                'price' => 4900,
                'stock' => 90,
                'image_url' => '/images/products/leminerale-botol-600ml.jpg'
            ],
            [
                'name' => 'Le Minerale Air Mineral Botol 330ml',
                'brand' => 'Le Minerale',
                'description' => 'Air mineral Le Minerale dalam kemasan botol 330ml, praktis dan sehat.',
                'size' => '330ml',
                'price' => 3850,
                'stock' => 110,
                'image_url' => '/images/products/leminerale-botol-330ml.jpg'
            ],

            // Cleo
            [
                'name' => 'Cleo Air Mineral Galon',
                'brand' => 'Cleo',
                'description' => 'Air mineral Cleo dalam kemasan galon 19 liter, murni dan berkualitas tinggi.',
                'size' => '19L',
                'price' => 17000,
                'stock' => 35,
                'image_url' => '/images/products/cleo-galon-19l.jpg'
            ],
            [
                'name' => 'Cleo Air Mineral Botol 1.5L',
                'brand' => 'Cleo',
                'description' => 'Air mineral Cleo dalam kemasan botol 1500ml, jernih dan menyegarkan.',
                'size' => '1.5L',
                'price' => 4100,
                'stock' => 85,
                'image_url' => '/images/products/cleo-botol-1500ml.jpg'
            ],
            [
                'name' => 'Cleo Air Mineral Botol 600ml',
                'brand' => 'Cleo',
                'description' => 'Air mineral Cleo dalam kemasan botol 600ml, cocok untuk gaya hidup aktif.',
                'size' => '600ml',
                'price' => 3700,
                'stock' => 130,
                'image_url' => '/images/products/cleo-botol-600ml.jpg'
            ],
            [
                'name' => 'Cleo Air Mineral Botol 330ml',
                'brand' => 'Cleo',
                'description' => 'Air mineral Cleo dalam kemasan botol 330ml, ekonomis dan berkualitas.',
                'size' => '330ml',
                'price' => 3600,
                'stock' => 140,
                'image_url' => '/images/products/cleo-botol-330ml.jpg'
            ],
            [
                'name' => 'Cleo Air Mineral Gelas 200ml',
                'brand' => 'Cleo',
                'description' => 'Air mineral Cleo dalam kemasan gelas 200ml, praktis untuk berbagai kebutuhan.',
                'size' => '200ml',
                'price' => 2200,
                'stock' => 200,
                'image_url' => '/images/products/cleo-gelas-200ml.jpg'
            ],
            [
                'name' => 'Cleo Air Mineral Botol 220ml',
                'brand' => 'Cleo',
                'description' => 'Air mineral Cleo dalam kemasan botol 220ml, ukuran mini yang praktis.',
                'size' => '220ml',
                'price' => 2200,
                'stock' => 180,
                'image_url' => '/images/products/cleo-botol-220ml.jpg'
            ],

            // Prima
            [
                'name' => 'Prima Air Mineral Botol 1.5L',
                'brand' => 'Prima',
                'description' => 'Air mineral Prima dalam kemasan botol 1500ml, kualitas terpercaya.',
                'size' => '1.5L',
                'price' => 3500,
                'stock' => 60,
                'image_url' => '/images/products/prima-botol-1500ml.jpg'
            ],
            [
                'name' => 'Prima Air Mineral Botol 600ml',
                'brand' => 'Prima',
                'description' => 'Air mineral Prima dalam kemasan botol 600ml, segar dan terjangkau.',
                'size' => '600ml',
                'price' => 3100,
                'stock' => 95,
                'image_url' => '/images/products/prima-botol-600ml.jpg'
            ],
            [
                'name' => 'Prima Air Mineral Botol 330ml',
                'brand' => 'Prima',
                'description' => 'Air mineral Prima dalam kemasan botol 330ml, pilihan ekonomis.',
                'size' => '330ml',
                'price' => 2900,
                'stock' => 120,
                'image_url' => '/images/products/prima-botol-330ml.jpg'
            ]
        ];

        foreach ($products as $product) {
            \App\Models\Product::firstOrCreate(
                ['name' => $product['name'], 'brand' => $product['brand'], 'size' => $product['size']],
                $product
            );
        }

        $this->command->info('Demo products seeded successfully.');
    }
}
