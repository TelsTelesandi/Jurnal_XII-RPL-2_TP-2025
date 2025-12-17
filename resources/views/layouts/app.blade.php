<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Karunia Laris - Toko Air Mineral Terpercaya')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes scaleIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        @keyframes shimmer {
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
        }
        .slide-down { animation: slideDown 0.3s ease-out; }
        .fade-in { animation: fadeIn 0.5s ease-out; }
        .scale-in { animation: scaleIn 0.3s ease-out; }
        .shimmer {
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            background-size: 1000px 100%;
            animation: shimmer 2s infinite;
        }
        .glass-morphism {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        .gradient-border {
            position: relative;
            background: linear-gradient(white, white) padding-box,
                        linear-gradient(135deg, #3b82f6, #06b6d4) border-box;
            border: 2px solid transparent;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 via-blue-50/30 to-cyan-50/30 min-h-screen">
    <!-- Navigation -->
    <nav class="glass-morphism shadow-lg sticky top-0 z-50 border-b border-blue-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo & Brand -->
                <div class="flex items-center">
                    <a href="{{ route('products.index') }}" class="flex items-center space-x-3 group">
                        <div class="relative">
                            <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-cyan-600 rounded-xl blur-md opacity-50 group-hover:opacity-75 transition-opacity"></div>
                            <div class="relative bg-gradient-to-br from-blue-600 via-blue-500 to-cyan-500 p-2.5 rounded-xl group-hover:scale-110 transition-all duration-300 shadow-lg">
                                <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/>
                                    <circle cx="12" cy="12" r="3" fill="white" opacity="0.8"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-2xl font-black bg-gradient-to-r from-blue-600 via-blue-700 to-cyan-600 bg-clip-text text-transparent tracking-tight">
                                Karunia Laris
                            </span>
                            <span class="text-[10px] font-medium text-gray-500 tracking-wider uppercase">Air Mineral Terpercaya</span>
                        </div>
                    </a>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex md:items-center md:space-x-2">
                    <a href="{{ route('products.index') }}" 
                       class="group px-4 py-2.5 rounded-xl text-sm font-semibold transition-all duration-300 {{ request()->routeIs('products.index') ? 'bg-gradient-to-r from-blue-600 to-cyan-600 text-white shadow-lg shadow-blue-500/30' : 'text-gray-700 hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 hover:text-blue-600' }}">
                        <i class="fas fa-store mr-2 group-hover:scale-110 transition-transform inline-block"></i>Produk
                    </a>
                    @if(!auth()->check() || auth()->user()->role === 'Customer')
                        <a href="{{ route('cart.index') }}" 
                           class="group px-4 py-2.5 rounded-xl text-sm font-semibold transition-all duration-300 relative {{ request()->routeIs('cart.index') ? 'bg-gradient-to-r from-blue-600 to-cyan-600 text-white shadow-lg shadow-blue-500/30' : 'text-gray-700 hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 hover:text-blue-600' }}">
                            <i class="fas fa-shopping-cart mr-2 group-hover:scale-110 transition-transform inline-block"></i>Keranjang
                            <span id="cart-count" class="absolute -top-1.5 -right-1.5 bg-gradient-to-r from-red-500 to-pink-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center hidden font-bold shadow-lg shadow-red-500/50 animate-pulse">0</span>
                        </a>
                    @endif
                    
                    @auth
                        <a href="{{ route('orders.index') }}" 
                           class="group px-4 py-2.5 rounded-xl text-sm font-semibold transition-all duration-300 {{ request()->routeIs('orders.*') ? 'bg-gradient-to-r from-blue-600 to-cyan-600 text-white shadow-lg shadow-blue-500/30' : 'text-gray-700 hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 hover:text-blue-600' }}">
                            <i class="fas fa-clipboard-list mr-2 group-hover:scale-110 transition-transform inline-block"></i>Orders
                        </a>
                        
                        @if(auth()->user()->role === 'Admin')
                            <a href="{{ route('vehicles.index') }}" 
                               class="group px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gradient-to-r hover:from-sky-50 hover:to-cyan-50 hover:text-sky-600 transition-all duration-300">
                                <i class="fas fa-truck mr-2 group-hover:scale-110 transition-transform inline-block"></i>Vehicles
                            </a>
                            <div class="h-6 w-px bg-gradient-to-b from-transparent via-gray-300 to-transparent mx-2"></div>
                            <a href="{{ route('products.create') }}" 
                               class="group px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gradient-to-r hover:from-emerald-50 hover:to-teal-50 hover:text-emerald-600 transition-all duration-300">
                                <i class="fas fa-plus-circle mr-2 group-hover:rotate-90 transition-transform inline-block"></i>Tambah
                            </a>
                            <a href="{{ route('admin.users.index') }}" 
                               class="group px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gradient-to-r hover:from-purple-50 hover:to-pink-50 hover:text-purple-600 transition-all duration-300">
                                <i class="fas fa-users mr-2 group-hover:scale-110 transition-transform inline-block"></i>Users
                            </a>
                            <a href="/analytics" 
                               class="group px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gradient-to-r hover:from-orange-50 hover:to-amber-50 hover:text-orange-600 transition-all duration-300">
                                <i class="fas fa-chart-line mr-2 group-hover:scale-110 transition-transform inline-block"></i>Analytics
                            </a>
                            <a href="{{ route('reports.completed') }}" 
                               class="group px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gradient-to-r hover:from-emerald-50 hover:to-teal-50 hover:text-emerald-700 transition-all duration-300">
                                <i class="fas fa-file-invoice-dollar mr-2 group-hover:scale-110 transition-transform inline-block"></i>Reports
                            </a>
                        @endif
                    @endauth
                </div>

                <!-- Right Side -->
                <div class="flex items-center space-x-3">
                    @auth
                        <!-- User Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" 
                                    class="flex items-center space-x-2 px-3 py-2 rounded-xl hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 transition-all duration-300 group">
                                <div class="relative">
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-cyan-600 rounded-full blur-sm opacity-50 group-hover:opacity-75 transition-opacity"></div>
                                    <div class="relative w-10 h-10 rounded-full bg-gradient-to-br from-blue-600 via-blue-500 to-cyan-500 flex items-center justify-center text-white font-bold text-sm shadow-lg ring-2 ring-white">
                                        {{ substr(auth()->user()->name, 0, 1) }}
                                    </div>
                                </div>
                                <div class="hidden md:block text-left">
                                    <p class="text-sm font-semibold text-gray-900">{{ auth()->user()->name }}</p>
                                    <p class="text-xs font-medium text-gray-500">{{ auth()->user()->role }}</p>
                                </div>
                                <svg class="w-4 h-4 text-gray-500 group-hover:text-blue-600 transition-colors" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <!-- Dropdown Menu -->
                            <div x-show="open" 
                                 @click.away="open = false"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 mt-3 w-64 bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden"
                                 style="display: none;">
                                <div class="px-4 py-4 bg-gradient-to-r from-blue-50 to-cyan-50 border-b border-blue-100">
                                    <p class="text-sm font-bold text-gray-900">{{ auth()->user()->name }}</p>
                                    <p class="text-xs font-medium text-gray-600 mt-0.5">{{ auth()->user()->email }}</p>
                                    <span class="inline-block mt-2 px-2.5 py-1 text-xs font-semibold rounded-full bg-gradient-to-r from-blue-600 to-cyan-600 text-white shadow-sm">{{ auth()->user()->role }}</span>
                                </div>
                                <div class="py-1">
                                    <a href="{{ route('orders.index') }}" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gradient-to-r hover:from-blue-50 hover:to-cyan-50 hover:text-blue-600 transition-all duration-200 group">
                                        <i class="fas fa-clipboard-list mr-3 text-blue-500 group-hover:scale-110 transition-transform"></i>
                                        <span class="font-medium">Pesanan Saya</span>
                                    </a>
                                    <a href="{{ route('account.show') }}" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gradient-to-r hover:from-purple-50 hover:to-pink-50 hover:text-purple-600 transition-all duration-200 group">
                                        <i class="fas fa-user mr-3 text-purple-500 group-hover:scale-110 transition-transform"></i>
                                        <span class="font-medium">Akun Saya</span>
                                    </a>
                                </div>
                                <div class="border-t border-gray-100">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="w-full flex items-center px-4 py-3 text-sm font-medium text-red-600 hover:bg-red-50 transition-all duration-200 group">
                                            <i class="fas fa-sign-out-alt mr-3 group-hover:scale-110 transition-transform"></i>
                                            Logout
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" 
                           class="px-5 py-2.5 text-sm font-semibold text-gray-700 hover:text-blue-600 rounded-xl hover:bg-blue-50 transition-all duration-300">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login
                        </a>
                        <a href="{{ route('register') }}" 
                           class="relative px-5 py-2.5 text-sm font-bold text-white rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl group overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-r from-blue-600 via-blue-500 to-cyan-500 group-hover:scale-105 transition-transform"></div>
                            <div class="absolute inset-0 bg-gradient-to-r from-cyan-500 to-blue-600 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            <span class="relative flex items-center">
                                <i class="fas fa-user-plus mr-2"></i>Register
                            </span>
                        </a>
                    @endauth

                    <!-- Mobile Menu Button -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen" 
                            class="md:hidden p-2 rounded-lg text-gray-700 hover:bg-gray-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div x-show="mobileMenuOpen" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-1"
             class="md:hidden border-t border-gray-200 bg-white"
             style="display: none;">
            <div class="px-4 py-3 space-y-1">
                <a href="{{ route('products.index') }}" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 font-medium">
                    <i class="fas fa-store mr-2"></i>Produk
                </a>
                @if(!auth()->check() || auth()->user()->role === 'Customer')
                    <a href="{{ route('cart.index') }}" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 font-medium">
                        <i class="fas fa-shopping-cart mr-2"></i>Keranjang
                    </a>
                @endif
                @auth
                    <a href="{{ route('orders.index') }}" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 font-medium">
                        <i class="fas fa-clipboard-list mr-2"></i>Orders
                    </a>
                    @if(auth()->user()->role === 'Admin')
                        <a href="{{ route('vehicles.index') }}" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 font-medium">
                            <i class="fas fa-truck mr-2"></i>Vehicles
                        </a>
                        <a href="{{ route('products.create') }}" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 font-medium">
                            <i class="fas fa-plus-circle mr-2"></i>Tambah Produk
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 font-medium">
                            <i class="fas fa-users mr-2"></i>Users
                        </a>
                        <a href="/analytics" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 font-medium">
                            <i class="fas fa-chart-line mr-2"></i>Analytics
                        </a>
                    @endif
                @endauth
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg shadow-sm slide-down">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-green-800 font-medium">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg shadow-sm slide-down">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-red-800 font-medium">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="relative bg-gradient-to-br from-gray-900 via-blue-900 to-cyan-900 mt-20 overflow-hidden">
        <!-- Decorative Elements -->
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 left-0 w-96 h-96 bg-blue-500 rounded-full filter blur-3xl"></div>
            <div class="absolute bottom-0 right-0 w-96 h-96 bg-cyan-500 rounded-full filter blur-3xl"></div>
        </div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
                <!-- Brand Section -->
                <div class="md:col-span-2">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="bg-gradient-to-br from-blue-500 to-cyan-500 p-3 rounded-xl shadow-xl">
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/>
                                <circle cx="12" cy="12" r="3" fill="white" opacity="0.8"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-2xl font-black text-white">Karunia Laris</h3>
                            <p class="text-xs text-cyan-300 font-medium">Air Mineral Terpercaya</p>
                        </div>
                    </div>
                    <p class="text-gray-300 text-sm leading-relaxed mb-6">Toko air mineral online terpercaya dengan produk berkualitas dari brand ternama. Kami berkomitmen memberikan pelayanan terbaik dan pengiriman cepat untuk kepuasan pelanggan.</p>
                    <div class="flex space-x-3">
                        <a href="#" class="w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white hover:text-cyan-300 transition-all duration-300 backdrop-blur-sm">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white hover:text-cyan-300 transition-all duration-300 backdrop-blur-sm">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white hover:text-cyan-300 transition-all duration-300 backdrop-blur-sm">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white hover:text-cyan-300 transition-all duration-300 backdrop-blur-sm">
                            <i class="fab fa-twitter"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div>
                    <h4 class="text-white font-bold text-sm uppercase tracking-wider mb-6 flex items-center">
                        <span class="w-8 h-0.5 bg-gradient-to-r from-cyan-500 to-blue-500 mr-3"></span>
                        Link Cepat
                    </h4>
                    <ul class="space-y-3">
                        <li>
                            <a href="{{ route('products.index') }}" class="text-gray-300 hover:text-cyan-300 text-sm transition-colors duration-300 flex items-center group">
                                <i class="fas fa-chevron-right text-xs mr-2 text-cyan-500 group-hover:translate-x-1 transition-transform"></i>
                                Produk
                            </a>
                        </li>
                        @if(!auth()->check() || auth()->user()->role === 'Customer')
                            <li>
                                <a href="{{ route('cart.index') }}" class="text-gray-300 hover:text-cyan-300 text-sm transition-colors duration-300 flex items-center group">
                                    <i class="fas fa-chevron-right text-xs mr-2 text-cyan-500 group-hover:translate-x-1 transition-transform"></i>
                                    Keranjang
                                </a>
                            </li>
                        @endif
                        @auth
                            <li>
                                <a href="{{ route('orders.index') }}" class="text-gray-300 hover:text-cyan-300 text-sm transition-colors duration-300 flex items-center group">
                                    <i class="fas fa-chevron-right text-xs mr-2 text-cyan-500 group-hover:translate-x-1 transition-transform"></i>
                                    Orders
                                </a>
                            </li>
                        @endauth
                        <li>
                            <a href="#" class="text-gray-300 hover:text-cyan-300 text-sm transition-colors duration-300 flex items-center group">
                                <i class="fas fa-chevron-right text-xs mr-2 text-cyan-500 group-hover:translate-x-1 transition-transform"></i>
                                Tentang Kami
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- Contact -->
                <div>
                    <h4 class="text-white font-bold text-sm uppercase tracking-wider mb-6 flex items-center">
                        <span class="w-8 h-0.5 bg-gradient-to-r from-cyan-500 to-blue-500 mr-3"></span>
                        Kontak
                    </h4>
                    <ul class="space-y-4">
                        <li class="flex items-start space-x-3 text-sm group">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                                <i class="fas fa-envelope text-white text-xs"></i>
                            </div>
                            <div>
                                <p class="text-gray-400 text-xs">Email</p>
                                <p class="text-gray-200 font-medium">info@karunialaris.com</p>
                            </div>
                        </li>
                        <li class="flex items-start space-x-3 text-sm group">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                                <i class="fas fa-phone text-white text-xs"></i>
                            </div>
                            <div>
                                <p class="text-gray-400 text-xs">Telepon</p>
                                <p class="text-gray-200 font-medium">+62 812-3456-7890</p>
                            </div>
                        </li>
                        <li class="flex items-start space-x-3 text-sm group">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                                <i class="fas fa-map-marker-alt text-white text-xs"></i>
                            </div>
                            <div>
                                <p class="text-gray-400 text-xs">Alamat</p>
                                <p class="text-gray-200 font-medium">Jakarta, Indonesia</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Bottom Bar -->
            <div class="border-t border-white/10 pt-8">
                <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                    <p class="text-gray-400 text-sm">
                        &copy; {{ date('Y') }} <span class="text-cyan-300 font-semibold">Karunia Laris</span>. All rights reserved.
                    </p>
                    <div class="flex items-center space-x-6 text-sm">
                        <a href="#" class="text-gray-400 hover:text-cyan-300 transition-colors">Privacy Policy</a>
                        <span class="text-gray-600">•</span>
                        <a href="#" class="text-gray-400 hover:text-cyan-300 transition-colors">Terms of Service</a>
                        <span class="text-gray-600">•</span>
                        <a href="#" class="text-gray-400 hover:text-cyan-300 transition-colors">FAQ</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Alpine.js for dropdowns -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Mobile Menu State -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('app', () => ({
                mobileMenuOpen: false
            }))
        })
    </script>

    @stack('scripts')
    
    <!-- Cart Count Update Script -->
    <script>
        function updateCartCount() {
            fetch('{{ route("cart.count") }}')
                .then(response => response.json())
                .then(data => {
                    const cartCount = document.getElementById('cart-count');
                    if (!cartCount) return; // cart link hidden for non-customer
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
