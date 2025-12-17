<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Google Maps API Key
    |--------------------------------------------------------------------------
    |
    | Dapatkan API key dari Google Cloud Console:
    | 1. Buka https://console.cloud.google.com/
    | 2. Buat project baru atau pilih yang sudah ada
    | 3. Enable APIs: Maps JavaScript API, Places API, Geocoding API
    | 4. Buat API Key di Credentials section
    | 5. (Opsional) Restrict API key untuk keamanan
    |
    */
    
    'api_key' => env('GOOGLE_MAPS_API_KEY', ''),

    // Provider peta yang digunakan: 'google' atau 'osm'. Default: 'osm' (gratis)
    'provider' => env('MAPS_PROVIDER', 'osm'),
    
    /*
    |--------------------------------------------------------------------------
    | Default Map Settings
    |--------------------------------------------------------------------------
    */
    
    'default_location' => [
        'lat' => env('GOOGLE_MAPS_DEFAULT_LAT', -6.2088), // Jakarta
        'lng' => env('GOOGLE_MAPS_DEFAULT_LNG', 106.8456), // Jakarta
    ],
    
    'default_zoom' => env('GOOGLE_MAPS_DEFAULT_ZOOM', 15),
    
    /*
    |--------------------------------------------------------------------------
    | Enabled Features
    |--------------------------------------------------------------------------
    */
    
    'features' => [
        'autocomplete' => true,
        'geocoding' => true,
        'current_location' => true,
        'draggable_marker' => true,
        'click_to_select' => true,
    ],

    // Company origin location (PT Karunia Laris Abadi)
    'company_location' => [
        'lat' => env('COMPANY_LAT', -6.26928654187561),
        'lng' => env('COMPANY_LNG', 106.97783761276642),
    ],

    // Company profile used as origin of shipments
    'company' => [
        'name' => env('COMPANY_NAME', 'PT Karunia Laris Abadi'),
        'address' => env('COMPANY_ADDRESS', 'Jl. Raya Pekayon No.50, RT.004/RW.001, Jaka Setia, Kec. Bekasi Sel., Kota Bks, Jawa Barat 17147'),
    ],
];
