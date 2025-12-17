@extends('layouts.app')

@section('content')
<!-- Breadcrumb -->
<div class="mb-6">
    <nav class="flex items-center space-x-2 text-sm">
        <a href="{{ route('products.index') }}" class="text-gray-500 hover:text-blue-600 transition-colors">
            <i class="fas fa-home"></i> Beranda
        </a>
        <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
        <a href="{{ route('products.index') }}" class="text-gray-500 hover:text-blue-600 transition-colors">Produk</a>
        <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
        <span class="text-gray-900 font-medium">{{ $product->name }}</span>
    </nav>
</div>

<div class="max-w-7xl mx-auto space-y-8">
    <!-- Main Product Section -->
    <div class="bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden scale-in">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">
            <!-- Product Image Section -->
            <div class="relative bg-gradient-to-br from-blue-50 via-cyan-50 to-blue-100 p-8 lg:p-12">
                <!-- Main Image -->
                <div class="relative h-[500px] rounded-2xl bg-gradient-to-br from-white/50 to-white/30 backdrop-blur-sm border-2 border-white/50 overflow-hidden group">
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="text-center transform group-hover:scale-110 transition-all duration-500">
                            <div class="relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-blue-400 to-cyan-400 rounded-full filter blur-3xl opacity-40 animate-pulse"></div>
                                <div class="relative text-9xl mb-6 drop-shadow-2xl">💧</div>
                            </div>
                            <div class="space-y-3">
                                <div class="inline-block px-6 py-2 bg-white/90 backdrop-blur-sm rounded-full shadow-xl">
                                    <span class="text-2xl font-black bg-gradient-to-r from-blue-600 to-cyan-600 bg-clip-text text-transparent">{{ $product->brand }}</span>
                                </div>
                                <div class="inline-block px-5 py-1.5 bg-gradient-to-r from-blue-600 to-cyan-600 rounded-full shadow-lg">
                                    <span class="text-white font-bold text-lg">{{ $product->size }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Badges -->
                    @if($product->stock > 0)
                        <div class="absolute top-6 left-6">
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold bg-gradient-to-r from-emerald-500 to-teal-500 text-white shadow-xl">
                                <i class="fas fa-check-circle mr-2"></i>
                                Tersedia
                            </span>
                        </div>
                    @else
                        <div class="absolute top-6 left-6">
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold bg-gradient-to-r from-red-500 to-pink-500 text-white shadow-xl">
                                <i class="fas fa-times-circle mr-2"></i>
                                Stok Habis
                            </span>
                        </div>
                    @endif
                </div>

                <!-- Thumbnail Gallery -->
                <div class="grid grid-cols-4 gap-3 mt-6">
                    @for($i = 1; $i <= 4; $i++)
                        <div class="group relative h-24 bg-white/60 backdrop-blur-sm rounded-xl border-2 {{ $i === 1 ? 'border-blue-500' : 'border-white/50' }} hover:border-blue-500 cursor-pointer transition-all duration-300 overflow-hidden">
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-3xl group-hover:scale-110 transition-transform">💧</span>
                            </div>
                        </div>
                    @endfor
                </div>

                <!-- Purchase Stats -->
                <div class="mt-8 grid grid-cols-3 gap-4">
                    <div class="bg-white/60 backdrop-blur-sm rounded-xl p-4 text-center border border-white/50 hover:bg-white/80 transition-all">
                        <div class="text-3xl font-black bg-gradient-to-r from-blue-600 to-cyan-600 bg-clip-text text-transparent">{{ $purchasesCount ?? 0 }}</div>
                        <div class="text-xs font-medium text-gray-600 mt-1">Unit Terjual</div>
                    </div>
                    <div class="bg-white/60 backdrop-blur-sm rounded-xl p-4 text-center border border-white/50 hover:bg-white/80 transition-all">
                        <div class="text-3xl font-black bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">{{ $buyersCount ?? 0 }}</div>
                        <div class="text-xs font-medium text-gray-600 mt-1">Pembeli</div>
                    </div>
                    <div class="bg-white/60 backdrop-blur-sm rounded-xl p-4 text-center border border-white/50 hover:bg-white/80 transition-all">
                        <div class="text-3xl font-black bg-gradient-to-r from-orange-600 to-amber-600 bg-clip-text text-transparent">{{ $reviewsCount ?? 0 }}</div>
                        <div class="text-xs font-medium text-gray-600 mt-1">Ulasan</div>
                    </div>
                </div>
            </div>

            <!-- Product Details Section -->
            <div class="p-8 lg:p-12 space-y-8">
                <!-- Product Title & Rating -->
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-bold bg-gradient-to-r from-blue-600 to-cyan-600 text-white shadow-lg">
                            <i class="fas fa-tag mr-2"></i>
                            {{ $product->brand }}
                        </span>
                        <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-bold bg-gray-100 text-gray-800">
                            <i class="fas fa-flask mr-2"></i>
                            {{ $product->size }}
                        </span>
                    </div>
                    
                    <h1 class="text-4xl font-black text-gray-900 mb-4 leading-tight">{{ $product->name }}</h1>
                    
                    <!-- Rating -->
                    <div class="flex items-center gap-4 mb-4 pb-4 border-b border-gray-100">
                        @php($avg = $avgRating ?? 0)
                        <div class="flex items-center">
                            @for($i=1;$i<=5;$i++)
                                <svg class="w-6 h-6 {{ $i <= round($avg) ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xl font-bold text-gray-900">{{ $avgRating ? number_format($avgRating, 1) : '0.0' }}</span>
                            <span class="text-sm text-gray-500">({{ $reviewsCount ?? 0 }} ulasan)</span>
                        </div>
                    </div>
                    
                    <p class="text-gray-600 leading-relaxed text-base">{{ $product->description }}</p>
                </div>

                <!-- Price Section -->
                <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-2xl p-6 border-2 border-blue-100">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <div class="text-sm font-medium text-gray-600 mb-1">Harga</div>
                            <div class="text-4xl font-black bg-gradient-to-r from-blue-600 to-cyan-600 bg-clip-text text-transparent">
                                {{ $product->formatted_price }}
                            </div>
                            <div class="text-sm text-gray-500 mt-1">per {{ $product->size }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-medium text-gray-600 mb-1">Stok</div>
                            <div class="text-3xl font-black text-gray-900">{{ $product->stock }}</div>
                            <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold mt-2 {{ $product->stock > 10 ? 'bg-emerald-100 text-emerald-700' : ($product->stock > 0 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                @if($product->stock > 10)
                                    <i class="fas fa-check-circle mr-1"></i> Tersedia
                                @elseif($product->stock > 0)
                                    <i class="fas fa-exclamation-triangle mr-1"></i> Terbatas
                                @else
                                    <i class="fas fa-times-circle mr-1"></i> Habis
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Add to Cart Form -->
                    @auth
                        @if(auth()->user()->role === 'Customer')
                            <form method="POST" action="{{ route('cart.add', $product) }}" class="space-y-4">
                                @csrf
                                
                                <!-- Quantity Selector -->
                                <div class="flex items-center gap-4">
                                    <label class="text-sm font-semibold text-gray-700">Jumlah:</label>
                                    <div class="flex items-center bg-white rounded-xl border-2 border-blue-200 overflow-hidden">
                                        <button type="button" class="px-5 py-3 text-gray-600 hover:bg-blue-50 hover:text-blue-600 font-bold transition-colors" onclick="decreaseQty()">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <input type="number" name="quantity" id="quantity" value="1" min="1" max="{{ $product->stock }}" 
                                               class="w-20 text-center border-0 focus:ring-0 focus:outline-none font-bold text-lg">
                                        <button type="button" class="px-5 py-3 text-gray-600 hover:bg-blue-50 hover:text-blue-600 font-bold transition-colors" onclick="increaseQty()">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Add to Cart Button -->
                                @if($product->stock > 0)
                                    <button type="submit" class="w-full relative px-6 py-4 font-bold text-white rounded-xl overflow-hidden group shadow-xl hover:shadow-2xl transition-all duration-300">
                                        <div class="absolute inset-0 bg-gradient-to-r from-blue-600 via-blue-500 to-cyan-500 group-hover:scale-105 transition-transform"></div>
                                        <span class="relative text-lg flex items-center justify-center">
                                            <i class="fas fa-shopping-cart mr-3"></i>
                                            Tambah ke Keranjang
                                        </span>
                                    </button>
                                @else
                                    <button disabled class="w-full px-6 py-4 bg-gray-200 text-gray-500 rounded-xl font-bold text-lg cursor-not-allowed">
                                        <i class="fas fa-times-circle mr-2"></i>
                                        Stok Habis
                                    </button>
                                @endif
                            </form>
                        @else
                            <div class="p-5 bg-gradient-to-r from-yellow-50 to-amber-50 border-2 border-yellow-200 rounded-xl">
                                <div class="flex items-start space-x-3">
                                    <i class="fas fa-info-circle text-yellow-600 text-xl mt-0.5"></i>
                                    <div>
                                        <div class="text-sm font-bold text-yellow-900 mb-1">Informasi</div>
                                        <div class="text-sm text-yellow-800">Akun dengan role {{ auth()->user()->role }} tidak dapat melakukan pemesanan.</div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @else
                        <form method="POST" action="{{ route('cart.add', $product) }}" class="space-y-4">
                            @csrf
                            <!-- Quantity Selector -->
                            <div class="flex items-center gap-4">
                                <label class="text-sm font-semibold text-gray-700">Jumlah:</label>
                                <div class="flex items-center bg-white rounded-xl border-2 border-blue-200 overflow-hidden">
                                    <button type="button" class="px-5 py-3 text-gray-600 hover:bg-blue-50 hover:text-blue-600 font-bold transition-colors" onclick="decreaseQty()">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" name="quantity" id="quantity" value="1" min="1" max="{{ $product->stock }}" 
                                           class="w-20 text-center border-0 focus:ring-0 focus:outline-none font-bold text-lg">
                                    <button type="button" class="px-5 py-3 text-gray-600 hover:bg-blue-50 hover:text-blue-600 font-bold transition-colors" onclick="increaseQty()">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <!-- Add to Cart Button -->
                            @if($product->stock > 0)
                                <button type="submit" class="w-full relative px-6 py-4 font-bold text-white rounded-xl overflow-hidden group shadow-xl hover:shadow-2xl transition-all duration-300">
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-600 via-blue-500 to-cyan-500 group-hover:scale-105 transition-transform"></div>
                                    <span class="relative text-lg flex items-center justify-center">
                                        <i class="fas fa-shopping-cart mr-3"></i>
                                        Tambah ke Keranjang
                                    </span>
                                </button>
                            @else
                                <button disabled class="w-full px-6 py-4 bg-gray-200 text-gray-500 rounded-xl font-bold text-lg cursor-not-allowed">
                                    <i class="fas fa-times-circle mr-2"></i>
                                    Stok Habis
                                </button>
                            @endif
                            <p class="text-xs text-gray-500">Anda dapat checkout setelah login.</p>
                        </form>
                    @endauth
                </div>

                <!-- Product Specifications -->
                <div class="bg-gray-50 rounded-2xl p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                        Spesifikasi Produk
                    </h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex items-center space-x-3 p-3 bg-white rounded-xl">
                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-tag text-blue-600"></i>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 font-medium">Brand</div>
                                <div class="text-sm font-bold text-gray-900">{{ $product->brand }}</div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3 p-3 bg-white rounded-xl">
                            <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-flask text-purple-600"></i>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 font-medium">Ukuran</div>
                                <div class="text-sm font-bold text-gray-900">{{ $product->size }}</div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3 p-3 bg-white rounded-xl">
                            <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-layer-group text-emerald-600"></i>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 font-medium">Kategori</div>
                                <div class="text-sm font-bold text-gray-900">{{ ucfirst(str_replace('_', ' ', $product->category)) }}</div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3 p-3 bg-white rounded-xl">
                            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-check-circle text-green-600"></i>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 font-medium">Status</div>
                                <div class="text-sm font-bold {{ $product->is_active ? 'text-green-600' : 'text-red-600' }}">{{ $product->is_active ? 'Aktif' : 'Tidak Aktif' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Admin Actions -->
                @auth
                    @if(auth()->user()->role === 'Admin')
                        <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl p-6 border-2 border-gray-200">
                            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-cog mr-2 text-gray-700"></i>
                                Admin Actions
                            </h3>
                            <div class="space-y-3">
                                <!-- Edit & Delete -->
                                <div class="flex gap-3">
                                    <a href="{{ route('products.edit', $product) }}" 
                                       class="flex-1 px-4 py-3 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 text-white rounded-xl font-semibold transition-all shadow-lg hover:shadow-xl text-center">
                                        <i class="fas fa-edit mr-2"></i>
                                        Edit Produk
                                    </a>
                                    <form method="POST" action="{{ route('products.destroy', $product) }}" 
                                          onsubmit="return confirm('Yakin ingin menghapus produk ini?')" class="flex-1">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full px-4 py-3 bg-gradient-to-r from-red-600 to-pink-600 hover:from-red-700 hover:to-pink-700 text-white rounded-xl font-semibold transition-all shadow-lg hover:shadow-xl">
                                            <i class="fas fa-trash mr-2"></i>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                                
                                <!-- Stock In Form -->
                                <form method="POST" action="{{ route('products.stockIn', $product) }}" class="flex gap-3">
                                    @csrf
                                    <input type="number" name="quantity" placeholder="Jumlah" min="1" 
                                           class="flex-1 px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 font-semibold" required>
                                    <input type="text" name="notes" placeholder="Catatan (opsional)" 
                                           class="flex-1 px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white rounded-xl font-semibold transition-all shadow-lg hover:shadow-xl whitespace-nowrap">
                                        <i class="fas fa-plus-circle mr-2"></i>
                                        Tambah Stok
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                @endauth
            </div>
        </div>
    </div>

    <!-- Reviews Section -->
    <div class="bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden scale-in">
        <div class="bg-gradient-to-r from-blue-50 to-cyan-50 px-8 py-6 border-b border-blue-100">
            <div class="flex items-center justify-between">
                <h3 class="text-2xl font-black text-gray-900 flex items-center">
                    <i class="fas fa-comments mr-3 text-blue-600"></i>
                    Ulasan Pembeli
                </h3>
                <div class="flex items-center space-x-2">
                    <div class="text-3xl font-black bg-gradient-to-r from-blue-600 to-cyan-600 bg-clip-text text-transparent">{{ $avgRating ? number_format($avgRating, 1) : '0.0' }}</div>
                    <div class="flex flex-col">
                        <div class="flex">
                            @for($i=1;$i<=5;$i++)
                                <svg class="w-4 h-4 {{ $i <= round($avgRating ?? 0) ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                        </div>
                        <div class="text-xs text-gray-500 font-medium">{{ $reviewsCount ?? 0 }} ulasan</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="divide-y divide-gray-100">
            @forelse(($reviews ?? []) as $rev)
                <div class="px-8 py-6 hover:bg-gray-50 transition-colors">
                    <div class="flex items-start space-x-4">
                        <!-- User Avatar -->
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-600 to-cyan-600 flex items-center justify-center text-white font-bold text-lg shadow-lg">
                                {{ substr($rev->user->name ?? 'U', 0, 1) }}
                            </div>
                        </div>
                        
                        <!-- Review Content -->
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-2">
                                <div>
                                    <div class="font-bold text-gray-900">{{ $rev->user->name ?? 'Pengguna' }}</div>
                                    <div class="text-xs text-gray-500">{{ $rev->created_at?->diffForHumans() }}</div>
                                </div>
                                <div class="flex items-center space-x-1">
                                    @for($i=1;$i<=5;$i++)
                                        <svg class="w-5 h-5 {{ $i <= (int)$rev->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                </div>
                            </div>
                            
                            @if($rev->quality)
                                <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 mb-2">
                                    <i class="fas fa-award mr-1.5"></i>
                                    Kualitas: {{ $rev->quality }}
                                </div>
                            @endif
                            
                            @if($rev->notes)
                                <p class="text-gray-700 text-sm leading-relaxed bg-gray-50 rounded-xl p-4 border border-gray-100">
                                    "{{ $rev->notes }}"
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-8 py-16 text-center">
                    <div class="inline-block p-6 bg-gradient-to-br from-gray-50 to-gray-100 rounded-full mb-4">
                        <i class="fas fa-comments text-6xl text-gray-300"></i>
                    </div>
                    <h4 class="text-lg font-bold text-gray-900 mb-2">Belum Ada Ulasan</h4>
                    <p class="text-gray-500">Jadilah yang pertama memberikan ulasan untuk produk ini</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Back Button -->
    <div class="text-center">
        <a href="{{ route('products.index') }}" 
           class="inline-flex items-center px-8 py-4 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-2xl font-semibold transition-all shadow-lg hover:shadow-xl">
            <i class="fas fa-arrow-left mr-3"></i>
            Kembali ke Katalog Produk
        </a>
    </div>
</div>

<script>
function increaseQty() {
    const input = document.getElementById('quantity');
    const max = parseInt(input.getAttribute('max'));
    const current = parseInt(input.value);
    if (current < max) {
        input.value = current + 1;
    }
}

function decreaseQty() {
    const input = document.getElementById('quantity');
    const min = parseInt(input.getAttribute('min'));
    const current = parseInt(input.value);
    if (current > min) {
        input.value = current - 1;
    }
}
</script>
@endsection
