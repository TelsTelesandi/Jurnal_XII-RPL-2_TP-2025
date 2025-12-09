<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Laravel'))</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Tailwind & App Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100 min-h-screen flex flex-col">

    <!-- Navbar -->
    @include('components.navbar') 
    {{-- kamu bisa bikin partial navbar custom sendiri di resources/views/components/navbar.blade.php --}}

    <!-- Page Heading -->
    @hasSection('page-title')
    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <h1 class="text-xl font-semibold text-gray-800">@yield('page-title')</h1>
        </div>
    </header>
    @endif

    <!-- Page Content -->
    <main class="flex-1">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t mt-10">
        <div class="max-w-7xl mx-auto px-4 py-4 text-sm text-gray-500 text-center">
            © {{ date('Y') }} PT Rekan Kinerja Abadi. All rights reserved.
        </div>
    </footer>
</body>
</html>
