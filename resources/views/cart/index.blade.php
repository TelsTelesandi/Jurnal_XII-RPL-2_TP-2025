@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-4xl font-black text-gray-900 mb-2 flex items-center">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-600 to-cyan-600 flex items-center justify-center mr-4 shadow-xl">
                    <i class="fas fa-shopping-cart text-white text-2xl"></i>
                </div>
                Keranjang Belanja
            </h1>
            <p class="text-gray-600">Review produk yang akan Anda beli sebelum checkout</p>
        </div>
        @if($cartItems->count() > 0)
            <div class="text-right">
                <div class="text-3xl font-black bg-gradient-to-r from-blue-600 to-cyan-600 bg-clip-text text-transparent">{{ $cart->total_items }}</div>
                <div class="text-sm text-gray-500 font-medium">Total Item</div>
            </div>
        @endif
    </div>
</div>

<div class="max-w-7xl mx-auto">
    @if($cartItems->count() > 0)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Cart Items -->
            <div class="lg:col-span-2 space-y-4">
                @foreach($cartItems as $item)
                    <div class="group bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-2xl transition-all duration-300 scale-in">
                        <div class="flex items-center gap-6 p-6">
                            <!-- Product Image -->
                            <div class="relative">
                                <div class="w-28 h-28 bg-gradient-to-br from-blue-50 via-cyan-50 to-blue-100 rounded-2xl flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                                    <div class="text-center">
                                        <div class="relative">
                                            <div class="absolute inset-0 bg-gradient-to-br from-blue-400 to-cyan-400 rounded-full filter blur-xl opacity-30 animate-pulse"></div>
                                            <div class="relative text-4xl mb-2">💧</div>
                                        </div>
                                        <div class="text-xs font-bold bg-gradient-to-r from-blue-600 to-cyan-600 bg-clip-text text-transparent">{{ $item->product->brand }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Product Info -->
                            <div class="flex-1">
                                <h3 class="font-bold text-gray-900 text-lg mb-2">{{ $item->product->name }}</h3>
                                <div class="flex items-center gap-2 mb-3">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-blue-600 to-cyan-600 text-white shadow-sm">
                                        <i class="fas fa-tag mr-1.5"></i>
                                        {{ $item->product->brand }}
                                    </span>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-800">
                                        <i class="fas fa-flask mr-1.5"></i>
                                        {{ $item->product->size }}
                                    </span>
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <span class="font-semibold">{{ $item->formatted_price }}</span>
                                    <span class="mx-2">×</span>
                                    <span class="font-semibold">{{ $item->quantity }}</span>
                                </div>
                            </div>

                            <!-- Quantity Controls -->
                            <div class="flex flex-col items-center gap-4">
                                <form method="POST" action="{{ route('cart.update', $item) }}" class="flex items-center bg-gray-50 rounded-xl border-2 border-gray-200 overflow-hidden">
                                    @csrf
                                    @method('PATCH')
                                    <button type="button" class="px-4 py-2 text-gray-600 hover:bg-blue-50 hover:text-blue-600 font-bold transition-colors" 
                                            onclick="updateQuantity({{ $item->id }}, {{ $item->quantity - 1 }}, {{ $item->product->stock }})">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" name="quantity" value="{{ $item->quantity }}" 
                                           min="1" max="{{ $item->product->stock }}"
                                           class="w-16 text-center border-0 focus:ring-0 focus:outline-none font-bold text-lg bg-transparent"
                                           onchange="this.form.submit()">
                                    <button type="button" class="px-4 py-2 text-gray-600 hover:bg-blue-50 hover:text-blue-600 font-bold transition-colors"
                                            onclick="updateQuantity({{ $item->id }}, {{ $item->quantity + 1 }}, {{ $item->product->stock }})">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </form>
                                <div class="text-xs text-gray-500">Stok: {{ $item->product->stock }}</div>
                            </div>

                            <!-- Price & Remove -->
                            <div class="text-right">
                                <div class="text-2xl font-black bg-gradient-to-r from-blue-600 to-cyan-600 bg-clip-text text-transparent mb-3">{{ $item->formatted_subtotal }}</div>
                                <form method="POST" action="{{ route('cart.remove', $item) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-4 py-2 bg-red-100 hover:bg-red-200 text-red-600 hover:text-red-700 rounded-xl font-semibold text-sm transition-all" 
                                            onclick="return confirm('Hapus produk dari keranjang?')">
                                        <i class="fas fa-trash mr-2"></i>Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Cart Summary -->
            <div class="lg:col-span-1">
                <div class="sticky top-24">
                    <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden">
                        <!-- Summary Header -->
                        <div class="bg-gradient-to-r from-blue-50 to-cyan-50 px-6 py-4 border-b border-blue-100">
                            <h3 class="text-xl font-black text-gray-900 flex items-center">
                                <i class="fas fa-receipt mr-2 text-blue-600"></i>
                                Ringkasan Belanja
                            </h3>
                        </div>
                        
                        <div class="p-6 space-y-6">
                            <!-- Items Count -->
                            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                                <div class="flex items-center space-x-2">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                        <i class="fas fa-box text-blue-600"></i>
                                    </div>
                                    <span class="font-semibold text-gray-700">Total Item</span>
                                </div>
                                <span class="text-xl font-black text-gray-900">{{ $cart->total_items }}</span>
                            </div>
                            
                            <!-- Total Price -->
                            <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-2xl p-6 border-2 border-blue-100">
                                <div class="text-sm font-medium text-gray-600 mb-2">Total Pembayaran</div>
                                <div class="text-4xl font-black bg-gradient-to-r from-blue-600 to-cyan-600 bg-clip-text text-transparent">{{ $cart->formatted_total_price }}</div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="space-y-3">
                                @auth
                                    @if(auth()->user()->role === 'Customer')
                                        <a href="{{ route('checkout.index') }}" class="block w-full relative px-6 py-4 font-bold text-white rounded-xl overflow-hidden group shadow-xl hover:shadow-2xl transition-all duration-300">
                                            <div class="absolute inset-0 bg-gradient-to-r from-blue-600 via-blue-500 to-cyan-500 group-hover:scale-105 transition-transform"></div>
                                            <span class="relative text-lg flex items-center justify-center">
                                                <i class="fas fa-shopping-bag mr-3"></i>
                                                Checkout Sekarang
                                            </span>
                                        </a>
                                    @else
                                        <div class="px-6 py-4 bg-yellow-50 border-2 border-yellow-200 text-yellow-800 rounded-xl font-semibold text-center">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            Akun dengan role {{ auth()->user()->role }} tidak dapat melakukan checkout.
                                        </div>
                                    @endif
                                @else
                                    <a href="{{ route('login') }}" class="block w-full relative px-6 py-4 font-bold text-white rounded-xl overflow-hidden group shadow-xl hover:shadow-2xl transition-all duration-300">
                                        <div class="absolute inset-0 bg-gradient-to-r from-blue-600 via-blue-500 to-cyan-500 group-hover:scale-105 transition-transform"></div>
                                        <span class="relative text-lg flex items-center justify-center">
                                            <i class="fas fa-sign-in-alt mr-3"></i>
                                            Login untuk Checkout
                                        </span>
                                    </a>
                                @endauth
                                
                                <a href="{{ route('products.index') }}" 
                                   class="block w-full px-6 py-4 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-semibold transition-all shadow-md hover:shadow-lg text-center">
                                    <i class="fas fa-store mr-2"></i>
                                    Lanjut Belanja
                                </a>
                                
                                <form method="POST" action="{{ route('cart.clear') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="w-full px-6 py-3 bg-red-50 hover:bg-red-100 text-red-600 rounded-xl font-semibold transition-all border-2 border-red-200 hover:border-red-300"
                                            onclick="return confirm('Kosongkan seluruh keranjang?')">
                                        <i class="fas fa-trash mr-2"></i>
                                        Kosongkan Keranjang
                                    </button>
                                </form>
                            </div>
                            
                            <!-- Trust Badges -->
                            <div class="pt-6 border-t border-gray-100 space-y-3">
                                <div class="flex items-center space-x-3 text-sm">
                                    <i class="fas fa-shield-alt text-green-500"></i>
                                    <span class="text-gray-600">Pembayaran Aman & Terpercaya</span>
                                </div>
                                <div class="flex items-center space-x-3 text-sm">
                                    <i class="fas fa-truck text-blue-500"></i>
                                    <span class="text-gray-600">Pengiriman Cepat & Akurat</span>
                                </div>
                                <div class="flex items-center space-x-3 text-sm">
                                    <i class="fas fa-undo text-purple-500"></i>
                                    <span class="text-gray-600">Garansi Uang Kembali</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @else
        <!-- Empty Cart -->
        <div class="bg-white rounded-3xl shadow-2xl border border-gray-100 overflow-hidden">
            <div class="text-center py-20 px-8">
                <div class="inline-block p-8 bg-gradient-to-br from-blue-50 to-cyan-50 rounded-full mb-8">
                    <svg class="w-32 h-32 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/>
                    </svg>
                </div>
                <h3 class="text-3xl font-black text-gray-900 mb-4">Keranjang Belanja Kosong</h3>
                <p class="text-gray-500 mb-8 text-lg max-w-md mx-auto">Belum ada produk yang ditambahkan ke keranjang. Yuk mulai belanja sekarang!</p>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('products.index') }}" 
                       class="relative px-8 py-4 font-bold text-white rounded-xl overflow-hidden group shadow-xl hover:shadow-2xl transition-all duration-300">
                        <div class="absolute inset-0 bg-gradient-to-r from-blue-600 via-blue-500 to-cyan-500 group-hover:scale-105 transition-transform"></div>
                        <span class="relative text-lg flex items-center justify-center">
                            <i class="fas fa-store mr-3"></i>
                            Mulai Belanja
                        </span>
                    </a>
                </div>
                
                <!-- Popular Categories -->
                <div class="mt-12 pt-12 border-t border-gray-100">
                    <h4 class="text-lg font-bold text-gray-900 mb-6">Kategori Populer</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <a href="{{ route('products.index', ['brand' => 'AQUA']) }}" class="group p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl hover:shadow-lg transition-all">
                            <div class="text-3xl mb-2">💧</div>
                            <div class="font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">AQUA</div>
                        </a>
                        <a href="{{ route('products.index', ['brand' => 'VIT']) }}" class="group p-4 bg-gradient-to-br from-cyan-50 to-cyan-100 rounded-2xl hover:shadow-lg transition-all">
                            <div class="text-3xl mb-2">💧</div>
                            <div class="font-semibold text-gray-900 group-hover:text-cyan-600 transition-colors">VIT</div>
                        </a>
                        <a href="{{ route('products.index', ['brand' => 'Le Minerale']) }}" class="group p-4 bg-gradient-to-br from-green-50 to-green-100 rounded-2xl hover:shadow-lg transition-all">
                            <div class="text-3xl mb-2">💧</div>
                            <div class="font-semibold text-gray-900 group-hover:text-green-600 transition-colors">Le Minerale</div>
                        </a>
                        <a href="{{ route('products.index', ['brand' => 'Cleo']) }}" class="group p-4 bg-gradient-to-br from-purple-50 to-purple-100 rounded-2xl hover:shadow-lg transition-all">
                            <div class="text-3xl mb-2">💧</div>
                            <div class="font-semibold text-gray-900 group-hover:text-purple-600 transition-colors">Cleo</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
function updateQuantity(itemId, newQuantity, maxStock) {
    if (newQuantity < 1 || newQuantity > maxStock) return;
    
    const form = document.querySelector(`form[action*="cart/update/${itemId}"]`);
    const input = form.querySelector('input[name="quantity"]');
    input.value = newQuantity;
    form.submit();
}

// Update cart count in navigation
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
</script>
@endsection
