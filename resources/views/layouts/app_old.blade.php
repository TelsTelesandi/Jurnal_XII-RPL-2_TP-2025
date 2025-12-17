<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Aplikasi Manajemen Distribusi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <h1 class="text-xl font-bold text-gray-900">
                            💧 Karunia Laris - Toko Air Mineral
                        </h1>
                    </div>
                    <div class="hidden md:ml-6 md:flex md:space-x-4">
                        <a href="{{ route('products.index') }}" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                            🏪 Produk
                        </a>
                        <a href="{{ route('cart.index') }}" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium transition-colors relative">
                            🛒 Keranjang
                            <span id="cart-count" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center hidden">0</span>
                        </a>
                        @auth
                            @if(auth()->user()->role === 'Admin')
                                <a href="{{ route('products.create') }}" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                                    ➕ Tambah Produk
                                </a>
                                <a href="/analytics" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                                    📊 Analytics
                                </a>
                            @endif
                            <a href="/orders" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                                📋 Orders
                            </a>
                        @endauth
                    </div>
                </div>

                <!-- Right side -->
                <div class="flex items-center space-x-4">
                    @auth
                        <div class="flex items-center space-x-3">
                            <span class="text-sm text-gray-600">
                                👋 {{ auth()->user()->name }}
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ auth()->user()->role ?? 'User' }}
                                </span>
                            </span>
                            <form method="post" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                                    Logout
                                </button>
                            </form>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                            Login
                        </a>
                    @endauth
                </div>

                <!-- Mobile menu button -->
                @auth
                <div class="md:hidden flex items-center">
                    <button type="button" class="mobile-menu-button text-gray-600 hover:text-gray-900 focus:outline-none focus:text-gray-900 p-2">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
                @endauth
            </div>
        </div>

        <!-- Mobile menu -->
        <div class="mobile-menu hidden md:hidden">
            <div class="px-2 pt-2 pb-3 space-y-1 bg-gray-50 border-t border-gray-200">
                <a href="{{ route('products.index') }}" class="block text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-base font-medium">
                    🏪 Produk
                </a>
                <a href="{{ route('cart.index') }}" class="block text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-base font-medium">
                    🛒 Keranjang
                </a>
                @auth
                    @if(auth()->user()->role === 'Admin')
                        <a href="{{ route('products.create') }}" class="block text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-base font-medium">
                            ➕ Tambah Produk
                        </a>
                        <a href="/analytics" class="block text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-base font-medium">
                            📊 Analytics
                        </a>
                    @endif
                    <a href="/orders" class="block text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-base font-medium">
                        📋 Orders
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Mobile menu toggle script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.querySelector('.mobile-menu-button');
            const mobileMenu = document.querySelector('.mobile-menu');
            
            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
            }
        });
    </script>

    @stack('scripts')
    
    <!-- Cart Count Update Script -->
    <script>
        function updateCartCount() {
            fetch('{{ route("cart.count") }}')
                .then(response => response.json())
                .then(data => {
                    const cartCount = document.getElementById('cart-count');
                    if (data.count > 0) {
                        cartCount.textContent = data.count;
                        cartCount.classList.remove('hidden');
                    } else {
                        cartCount.classList.add('hidden');
                    }
                })
                .catch(error => console.error('Error updating cart count:', error));
        }

        // Update cart count on page load
        document.addEventListener('DOMContentLoaded', updateCartCount);
        
        // Update cart count after adding to cart
        document.addEventListener('submit', function(e) {
            if (e.target.action && e.target.action.includes('/cart/add/')) {
                setTimeout(updateCartCount, 500);
            }
        });
    </script>
</body>
</html>
