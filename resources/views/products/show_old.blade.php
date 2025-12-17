@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 p-8">
            <!-- Product Image -->
            <div class="space-y-4">
                <div class="h-96 bg-gradient-to-br from-blue-100 to-blue-200 rounded-lg flex items-center justify-center">
                    <div class="text-center">
                        <div class="text-8xl mb-4">💧</div>
                        <div class="text-2xl font-bold text-blue-800">{{ $product->brand }}</div>
                        <div class="text-lg text-blue-600">{{ $product->size }}</div>
                    </div>
                </div>

                <!-- Purchase Stats -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistik Pembelian</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="text-xs text-gray-500">Total Unit Terjual</div>
                            <div class="text-xl font-bold text-gray-900">{{ $purchasesCount ?? 0 }}</div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="text-xs text-gray-500">Jumlah Pembeli</div>
                            <div class="text-xl font-bold text-gray-900">{{ $buyersCount ?? 0 }}</div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="text-xs text-gray-500">Jumlah Ulasan</div>
                            <div class="text-xl font-bold text-gray-900">{{ $reviewsCount ?? 0 }}</div>
                        </div>
                    </div>
                </div>
                
                <!-- Additional Product Images Placeholder -->
                <div class="grid grid-cols-4 gap-2">
                    @for($i = 1; $i <= 4; $i++)
                        <div class="h-20 bg-gray-100 rounded border-2 border-transparent hover:border-blue-500 cursor-pointer flex items-center justify-center">
                            <span class="text-2xl">💧</span>
                        </div>
                    @endfor
                </div>
            </div>

            <!-- Product Details -->
            <div class="space-y-6">
                <!-- Product Title & Brand -->
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full">
                            {{ $product->brand }}
                        </span>
                        <span class="bg-gray-100 text-gray-800 text-sm font-medium px-3 py-1 rounded-full">
                            {{ $product->size }}
                        </span>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $product->name }}</h1>
                    <div class="flex items-center gap-3 mb-2">
                        @php($avg = $avgRating ?? 0)
                        <div class="text-yellow-500 text-xl">
                            @for($i=1;$i<=5;$i++)
                                <span>{{ $i <= round($avg) ? '★' : '☆' }}</span>
                            @endfor
                        </div>
                        <div class="text-sm text-gray-600">{{ $avgRating ? $avgRating.' / 5' : 'Belum ada rating' }}</div>
                        <div class="text-xs text-gray-500">({{ $reviewsCount ?? 0 }} ulasan)</div>
                    </div>
                    <p class="text-gray-600 leading-relaxed">{{ $product->description }}</p>
                </div>

                <!-- Price & Stock -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <div class="text-3xl font-bold text-blue-600">{{ $product->formatted_price }}</div>
                            <div class="text-sm text-gray-500">per {{ $product->size }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-semibold text-gray-900">Stok: {{ $product->stock }}</div>
                            <div class="text-sm {{ $product->stock > 10 ? 'text-green-600' : ($product->stock > 0 ? 'text-yellow-600' : 'text-red-600') }}">
                                @if($product->stock > 10)
                                    ✅ Tersedia
                                @elseif($product->stock > 0)
                                    ⚠️ Stok Terbatas
                                @else
                                    ❌ Habis
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Quantity Selector & Add to Cart -->
                    @if(!auth()->check() || auth()->user()->role !== 'Admin')
                        <form method="POST" action="{{ route('cart.add', $product) }}" class="flex items-center gap-4">
                            @csrf
                            <div class="flex items-center border border-gray-300 rounded-md">
                                <button type="button" class="px-3 py-2 text-gray-600 hover:text-gray-800" onclick="decreaseQty()">-</button>
                                <input type="number" name="quantity" id="quantity" value="1" min="1" max="{{ $product->stock }}" 
                                       class="w-16 text-center border-0 focus:ring-0 focus:outline-none">
                                <button type="button" class="px-3 py-2 text-gray-600 hover:text-gray-800" onclick="increaseQty()">+</button>
                            </div>
                            
                            @if($product->stock > 0)
                                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-md font-medium transition-colors">
                                    🛒 Tambah ke Keranjang
                                </button>
                            @else
                                <button disabled class="flex-1 bg-gray-300 text-gray-500 px-6 py-3 rounded-md font-medium cursor-not-allowed">
                                    Stok Habis
                                </button>
                            @endif
                        </form>
                    @else
                        <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-md text-yellow-800 text-sm">
                            Admin tidak dapat membeli produk. Gunakan menu Admin Actions di bawah untuk mengelola stok/produk.
                        </div>
                    @endif
                </div>

                <!-- Product Specifications -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Spesifikasi Produk</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="font-medium text-gray-700">Brand:</span>
                            <span class="text-gray-600">{{ $product->brand }}</span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">Ukuran:</span>
                            <span class="text-gray-600">{{ $product->size }}</span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">Kategori:</span>
                            <span class="text-gray-600">{{ ucfirst(str_replace('_', ' ', $product->category)) }}</span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">Status:</span>
                            <span class="text-green-600">{{ $product->is_active ? 'Aktif' : 'Tidak Aktif' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Admin Actions -->
                @auth
                    @if(auth()->user()->role === 'Admin')
                        <div class="border-t pt-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Admin Actions</h3>
                            <div class="flex gap-2 flex-wrap items-center">
                                <a href="{{ route('products.edit', $product) }}" 
                                   class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition-colors">
                                    ✏️ Edit Produk
                                </a>
                                <form method="POST" action="{{ route('products.destroy', $product) }}" 
                                      onsubmit="return confirm('Yakin ingin menghapus produk ini?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md transition-colors">
                                        🗑️ Hapus
                                    </button>
                                </form>
                                <!-- Stock In Form -->
                                <form method="POST" action="{{ route('products.stockIn', $product) }}" class="inline-flex items-center gap-2">
                                    @csrf
                                    <input type="number" name="quantity" placeholder="Qty" min="1" class="w-24 px-2 py-1 border border-gray-300 rounded" required>
                                    <input type="text" name="notes" placeholder="Catatan (opsional)" class="px-2 py-1 border border-gray-300 rounded w-48">
                                    <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md transition-colors">
                                        ➕ Tambah Stok
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                @endauth
            </div>
        </div>
    </div>

    <!-- Back to Products -->
    <div class="mt-6 text-center">
        <a href="{{ route('products.index') }}" 
           class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded-md transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Produk
        </a>
    </div>
</div>

<!-- Reviews Section -->
<div class="max-w-4xl mx-auto mt-6">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">🗣️ Ulasan Pembeli</h3>
        </div>
        <div>
            @forelse(($reviews ?? []) as $rev)
                <div class="px-6 py-4 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <div class="font-medium text-gray-900">{{ $rev->user->name ?? 'Pengguna' }}</div>
                        <div class="text-yellow-500">
                            @for($i=1;$i<=5;$i++)
                                <span class="text-lg">{{ $i <= (int)$rev->rating ? '★' : '☆' }}</span>
                            @endfor
                        </div>
                    </div>
                    <div class="text-sm text-gray-700 mt-1">Kualitas: {{ $rev->quality ?: '-' }}</div>
                    @if($rev->notes)
                        <div class="text-sm text-gray-600 mt-1">"{{ $rev->notes }}"</div>
                    @endif
                    <div class="text-xs text-gray-400 mt-1">{{ $rev->created_at?->diffForHumans() }}</div>
                </div>
            @empty
                <div class="px-6 py-10 text-center text-gray-500">Belum ada ulasan untuk produk ini.</div>
            @endforelse
        </div>
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
