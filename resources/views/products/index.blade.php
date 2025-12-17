@extends('layouts.app')

@section('content')
<div class="space-y-8">
    <!-- Hero Section -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-600 via-blue-500 to-cyan-500 shadow-2xl">
        <!-- Animated Background Pattern -->
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 left-0 w-96 h-96 bg-white rounded-full filter blur-3xl animate-pulse"></div>
            <div class="absolute bottom-0 right-0 w-96 h-96 bg-cyan-300 rounded-full filter blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
        </div>
        
        <div class="relative max-w-6xl mx-auto px-8 py-16 text-center">
            <!-- Icon & Title -->
            <div class="flex justify-center mb-6">
                <div class="relative">
                    <div class="absolute inset-0 bg-white rounded-full filter blur-xl opacity-50 animate-pulse"></div>
                    <div class="relative bg-white/20 backdrop-blur-sm p-6 rounded-full border-4 border-white/30">
                        <svg class="w-16 h-16 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/>
                            <circle cx="12" cy="12" r="3" fill="white" opacity="0.8"/>
                        </svg>
                    </div>
                </div>
            </div>
            
            <h1 class="text-5xl md:text-6xl font-black text-white mb-4 tracking-tight">
                Karunia Laris
            </h1>
            <p class="text-xl md:text-2xl text-blue-50 font-medium mb-8 max-w-2xl mx-auto leading-relaxed">
                Belanja Air Mineral Berkualitas dari Brand Terpercaya dengan Harga Terbaik
            </p>
            
            <!-- Features -->
            <div class="flex flex-wrap justify-center gap-6 mb-8">
                <div class="group flex items-center space-x-3 bg-white/10 backdrop-blur-sm px-6 py-3 rounded-full border-2 border-white/20 hover:bg-white/20 transition-all duration-300">
                    <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <span class="text-white font-semibold">Kualitas Terjamin</span>
                </div>
                <div class="group flex items-center space-x-3 bg-white/10 backdrop-blur-sm px-6 py-3 rounded-full border-2 border-white/20 hover:bg-white/20 transition-all duration-300">
                    <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                            <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z"/>
                        </svg>
                    </div>
                    <span class="text-white font-semibold">Pengiriman Cepat</span>
                </div>
                <div class="group flex items-center space-x-3 bg-white/10 backdrop-blur-sm px-6 py-3 rounded-full border-2 border-white/20 hover:bg-white/20 transition-all duration-300">
                    <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <span class="text-white font-semibold">Harga Terjangkau</span>
                </div>
            </div>
            
            <!-- Stats -->
            <div class="flex flex-wrap justify-center gap-8 text-white">
                <div class="text-center">
                    <div class="text-4xl font-black mb-1">{{ $products->total() }}+</div>
                    <div class="text-sm font-medium text-blue-100">Produk Tersedia</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-black mb-1">{{ $brands->count() }}</div>
                    <div class="text-sm font-medium text-blue-100">Brand Terpercaya</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-black mb-1">100%</div>
                    <div class="text-sm font-medium text-blue-100">Kualitas Original</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Section -->
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
        <div class="bg-gradient-to-r from-blue-50 to-cyan-50 px-6 py-4 border-b border-blue-100">
            <h2 class="text-lg font-bold text-gray-900 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 3a1 1 0 000 2h11a1 1 0 100-2H3zM3 7a1 1 0 000 2h5a1 1 0 000-2H3zM3 11a1 1 0 100 2h4a1 1 0 100-2H3zM13 16a1 1 0 102 0v-5.586l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 101.414 1.414L13 10.414V16z"/>
                </svg>
                Filter & Pencarian
            </h2>
        </div>
        <form method="GET" action="{{ route('products.index') }}" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <!-- Search -->
                <div class="group">
                    <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center">
                        <i class="fas fa-search mr-2 text-blue-500"></i>
                        Cari Produk
                    </label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Cari nama atau brand..." 
                               class="w-full pl-10 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300 group-hover:border-blue-300">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>
                
                <!-- Brand Filter -->
                <div class="group">
                    <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center">
                        <i class="fas fa-tag mr-2 text-purple-500"></i>
                        Brand
                    </label>
                    <div class="relative">
                        <select name="brand" class="w-full pl-10 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 appearance-none transition-all duration-300 group-hover:border-purple-300 bg-white">
                            <option value="">🏷️ Semua Brand</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand }}" {{ request('brand') === $brand ? 'selected' : '' }}>
                                    {{ $brand }}
                                </option>
                            @endforeach
                        </select>
                        <i class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                        <i class="fas fa-tag absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>

                <!-- Size Filter -->
                <div class="group">
                    <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center">
                        <i class="fas fa-ruler mr-2 text-emerald-500"></i>
                        Ukuran
                    </label>
                    <div class="relative">
                        <select name="size" class="w-full pl-10 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 appearance-none transition-all duration-300 group-hover:border-emerald-300 bg-white">
                            <option value="">📏 Semua Ukuran</option>
                            @foreach($sizes as $size)
                                <option value="{{ $size }}" {{ request('size') === $size ? 'selected' : '' }}>
                                    {{ $size }}
                                </option>
                            @endforeach
                        </select>
                        <i class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                        <i class="fas fa-ruler absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>

                <!-- Sort -->
                <div class="group">
                    <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center">
                        <i class="fas fa-sort-amount-down mr-2 text-orange-500"></i>
                        Urutkan
                    </label>
                    <div class="relative">
                        <select name="sort" class="w-full pl-10 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 appearance-none transition-all duration-300 group-hover:border-orange-300 bg-white">
                            <option value="">⚡ Default</option>
                            <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>💰 Harga Terendah</option>
                            <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>💎 Harga Tertinggi</option>
                            <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>🔤 Nama A-Z</option>
                        </select>
                        <i class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                        <i class="fas fa-sort-amount-down absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>
            </div>
            
            <div class="flex flex-wrap gap-3">
                <button type="submit" class="relative px-6 py-3 font-bold text-white rounded-xl overflow-hidden group shadow-lg hover:shadow-xl transition-all duration-300">
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-600 via-blue-500 to-cyan-500 group-hover:scale-105 transition-transform"></div>
                    <span class="relative flex items-center">
                        <i class="fas fa-search mr-2"></i>
                        Terapkan Filter
                    </span>
                </button>
                <a href="{{ route('products.index') }}" class="px-6 py-3 font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-all duration-300 flex items-center shadow-md hover:shadow-lg">
                    <i class="fas fa-redo mr-2"></i>
                    Reset
                </a>
                @if(request('search') || request('brand') || request('size') || request('sort'))
                    <div class="flex items-center px-4 py-2 bg-blue-50 text-blue-700 rounded-xl text-sm font-medium">
                        <i class="fas fa-filter mr-2"></i>
                        Filter Aktif
                    </div>
                @endif
            </div>
        </form>
    </div>

    <!-- Products Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($products as $product)
            <div class="group bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 scale-in">
                <!-- Product Image -->
                <div class="relative h-56 bg-gradient-to-br from-blue-50 via-cyan-50 to-blue-100 overflow-hidden">
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="text-center transform group-hover:scale-110 transition-transform duration-300">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-blue-400 to-cyan-400 rounded-full filter blur-2xl opacity-30 animate-pulse"></div>
                                <div class="relative text-6xl mb-3">💧</div>
                            </div>
                            <div class="inline-block px-4 py-1.5 bg-white/80 backdrop-blur-sm rounded-full">
                                <span class="text-sm font-bold bg-gradient-to-r from-blue-600 to-cyan-600 bg-clip-text text-transparent">{{ $product->brand }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Badge -->
                    @if($product->stock > 0)
                        <div class="absolute top-3 left-3">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-emerald-500 to-teal-500 text-white shadow-lg">
                                <i class="fas fa-check-circle mr-1"></i>
                                Tersedia
                            </span>
                        </div>
                    @else
                        <div class="absolute top-3 left-3">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-red-500 to-pink-500 text-white shadow-lg">
                                <i class="fas fa-times-circle mr-1"></i>
                                Habis
                            </span>
                        </div>
                    @endif
                    
                    <!-- Size Badge -->
                    <div class="absolute top-3 right-3">
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-white text-gray-900 shadow-lg border border-gray-200">
                            <i class="fas fa-flask mr-1.5 text-blue-500"></i>
                            {{ $product->size }}
                        </span>
                    </div>
                </div>
                
                <!-- Product Info -->
                <div class="p-5">
                    <h3 class="font-bold text-gray-900 text-base mb-2 line-clamp-2 leading-tight group-hover:text-blue-600 transition-colors">
                        {{ $product->name }}
                    </h3>
                    
                    <p class="text-gray-600 text-xs mb-4 line-clamp-2 leading-relaxed">{{ $product->description }}</p>
                    
                    <!-- Price & Stock -->
                    <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-100">
                        <div>
                            <div class="text-2xl font-black bg-gradient-to-r from-blue-600 to-cyan-600 bg-clip-text text-transparent">{{ $product->formatted_price }}</div>
                            <div class="flex items-center text-xs text-gray-500 mt-1">
                                <i class="fas fa-box mr-1.5 text-gray-400"></i>
                                Stok: <span class="font-semibold ml-1">{{ $product->stock }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex gap-2">
                        <a href="{{ route('products.show', $product) }}" 
                           class="flex-1 relative px-4 py-2.5 font-bold text-white rounded-xl overflow-hidden group/btn transition-all duration-300 shadow-lg hover:shadow-xl">
                            <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-cyan-600 group-hover/btn:scale-105 transition-transform"></div>
                            <span class="relative text-sm flex items-center justify-center">
                                <i class="fas fa-eye mr-2"></i>
                                Lihat
                            </span>
                        </a>
                        @auth
                            @if(auth()->user()->role === 'Admin')
                                <a href="{{ route('products.edit', $product) }}" 
                                   class="px-4 py-2.5 font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-all duration-300 shadow-md hover:shadow-lg flex items-center justify-center">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="text-center py-20">
                    <div class="inline-block p-6 bg-gradient-to-br from-blue-50 to-cyan-50 rounded-full mb-6">
                        <svg class="w-24 h-24 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Tidak ada produk ditemukan</h3>
                    <p class="text-gray-500 mb-6">Coba ubah filter pencarian Anda atau hapus semua filter</p>
                    <a href="{{ route('products.index') }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-cyan-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
                        <i class="fas fa-redo mr-2"></i>
                        Reset Filter
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($products->hasPages())
        <div class="flex justify-center">
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection
