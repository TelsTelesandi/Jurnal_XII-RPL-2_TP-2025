<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="current-user" content='@json(auth()->check()
            ? auth()->user()->only(['id', 'name', 'role_id'])
            : null)'>
   <title>{{ setting('site_name', 'PT. Rekan Kinerja Abadi') }}</title>

    <link rel="icon" href="{{ setting('site_logo') 
    ? asset('storage/'.setting('site_logo')) 
    : asset('image/image.png') }}" type="image/png">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="node_modules/simplebar/dist/simplebar.css" />
    <script src="node_modules/simplebar/dist/simplebar.min.js"></script>
    <script
        src="https://maps.googleapis.com/maps/api/js?key=e0b23db48be5c0d4772a62a3c0cb8de135f6488088535630d55ddbcc14f36f83&libraries=places">
    </script>
<script type="module" src="{{ asset('js/forum-policy.js') }}"></script>

    <!-- Base URL untuk JS fetch -->
    <meta name="base-url" content="{{ url('') }}">

    <!-- Vite CSS -->
    @vite(['resources/css/app.css'])
</head>

<body class="bg-gray-100">
<!-- Toast Container (global) -->
<div id="toast-container" 
     class="fixed top-[80px] right-5 sm:right-8 z-[9999] space-y-2 pointer-events-none">
</div>

    {{-- Navbar --}}
    @include('components.navbar')

    {{-- Forum Widget --}}
    <x-forum-widget />

    {{-- Konten --}}
    <main class="pt-16">
        @yield('content')

    </main>

    {{-- JS di akhir body biar lebih cepat --}}
    @vite(['resources/js/app.js'])
    @vite(['resources/js/navbar.js'])
    @vite(['resources/js/map.js'])
    @vite(['resources/js/forum.js'])

</body>

</html>
