<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class LayananController extends Controller
{
    // Data layanan yang tersedia (bisa dipindah ke config atau database)
    private $availableServices = [
        'web-development' => [
            'title' => 'Web Development',
            'description' => 'Pengembangan website profesional',
            'view' => 'layanan.tematik'
        ],
        'mobile-app' => [
            'title' => 'Mobile App Development',
            'description' => 'Pengembangan aplikasi mobile',
            'view' => 'layanan.mobile-app'
        ],
        'digital-marketing' => [
            'title' => 'Digital Marketing',
            'description' => 'Strategi pemasaran digital',
            'view' => 'layanan.digital-marketing'
        ],
        // tambah layanan lain...
    ];

    public function index()
    {
        return view('layanan.index', [
            'services' => $this->availableServices
        ]);
    }

    public function show($kategori)
    {
        // Normalisasi slug
        $slug = strtolower(str_replace([' ', '_'], '-', $kategori));

        // Cek apakah layanan exists
        if (!array_key_exists($slug, $this->availableServices)) {
            abort(404, "Layanan '$kategori' tidak tersedia");
        }

        $service = $this->availableServices[$slug];

        // Cek apakah view file exists
        if (!View::exists($service['view'])) {
            // Fallback ke template default
            return view('layanan.default', [
                'service' => $service,
                'kategori' => $kategori,
                'slug' => $slug
            ]);
        }

        return view($service['view'], [
            'service' => $service,
            'kategori' => $kategori,
            'slug' => $slug
        ]);
    }
}