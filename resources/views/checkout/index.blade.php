@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4">
    <div class="bg-white shadow-2xl rounded-3xl border border-gray-100 overflow-hidden">
        <!-- Header with Gradient -->
        <div class="relative bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 px-8 py-12">
            <div class="absolute inset-0 bg-black opacity-5"></div>
            <div class="relative z-10 text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-white/20 backdrop-blur rounded-2xl mb-4 shadow-xl">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h1 class="text-4xl font-extrabold text-white mb-2">
                    Checkout Pesanan
                </h1>
                <p class="text-blue-100 text-lg">Lengkapi informasi pengiriman untuk menyelesaikan pesanan Anda</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 p-8 bg-gray-50">
            <!-- Checkout Form -->
            <div class="lg:col-span-2 space-y-6">
                <form method="POST" action="{{ route('checkout.store') }}" class="space-y-6">
                    @csrf
                    
                    <!-- Customer Information -->
                    <div class="bg-white rounded-2xl p-6 shadow-lg border border-blue-100">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-3 rounded-xl shadow-md">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">Informasi Pemesan</h3>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap *</label>
                                <input type="text" name="customer_name" value="{{ old('customer_name', auth()->user()->name ?? '') }}" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                       placeholder="Nama lengkap pemesan">
                                @error('customer_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon *</label>
                                <input type="tel" name="customer_phone" value="{{ old('customer_phone') }}" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                       placeholder="08xxxxxxxxxx">
                                @error('customer_phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" name="customer_email" value="{{ old('customer_email', auth()->user()->email ?? '') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                       placeholder="email@example.com">
                                @error('customer_email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Shipping Information -->
                    <div class="bg-white rounded-2xl p-6 shadow-lg border border-green-100">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="bg-gradient-to-r from-green-500 to-emerald-600 p-3 rounded-xl shadow-md">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">Alamat Pengiriman</h3>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap *</label>
                                <textarea name="shipping_address" id="shipping_address" rows="4" required
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                          placeholder="Tulis alamat lengkap tujuan pengiriman (contoh: Jl. Sudirman No. 1, Jakarta)">{{ old('shipping_address') }}</textarea>
                                @error('shipping_address')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">Tuliskan alamat sedetail mungkin (jalan, nomor, RT/RW, kelurahan, kecamatan, kota/kabupaten, provinsi, kode pos).</p>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="bg-white rounded-2xl p-6 shadow-lg border border-yellow-100">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="bg-gradient-to-r from-yellow-500 to-orange-500 p-3 rounded-xl shadow-md">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">Metode Pembayaran *</h3>
                        </div>
                        @error('payment_method')
                            <div class="mb-4 p-3 bg-red-50 border-l-4 border-red-500 text-red-700">
                                <p>{{ $message }}</p>
                            </div>
                        @enderror
                        <div class="space-y-3">
                            <div class="flex items-center p-3 border border-gray-300 rounded-md bg-gray-50">
                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">💵 Cash on Delivery (COD)</div>
                                    <div class="text-sm text-gray-500">Bayar saat barang sampai</div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="payment_method" value="cod">
                    </div>

                    <!-- Notes -->
                    <div class="bg-white rounded-2xl p-6 shadow-lg border border-purple-100">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="bg-gradient-to-r from-purple-500 to-pink-500 p-3 rounded-xl shadow-md">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">Catatan Tambahan</h3>
                        </div>
                        <div>
                            <textarea name="notes" rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                      placeholder="Catatan khusus untuk pesanan (opsional)">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex flex-col sm:flex-row gap-4 pt-8">
                        <button type="submit" class="flex-1 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-8 py-4 rounded-xl font-bold text-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center gap-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Buat Pesanan
                        </button>
                        <a href="{{ route('cart.index') }}" class="flex-1 bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 hover:text-gray-900 px-8 py-4 rounded-xl font-bold text-lg shadow hover:shadow-lg transition-all duration-200 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Kembali ke Keranjang
                        </a>
                    </div>
                </form>
            </div>

            <!-- Order Summary -->
            <div class="space-y-6">
                <!-- Cart Items Summary -->
                <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100 sticky top-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="bg-gradient-to-r from-indigo-500 to-purple-500 p-3 rounded-xl shadow-md">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">Ringkasan Pesanan</h3>
                    </div>
                    <div class="space-y-3">
                        @foreach($cartItems as $item)
                            <div class="flex items-center justify-between py-2 border-b border-gray-200 last:border-b-0">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-gradient-to-br from-blue-100 to-blue-200 rounded flex items-center justify-center">
                                        <span class="text-xs font-medium text-blue-800">{{ $item->product->brand }}</span>
                                    </div>
                                    <div>
                                        <div class="font-medium text-sm text-gray-900">{{ $item->product->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $item->product->size }}</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-medium text-gray-900">{{ $item->formatted_subtotal }}</div>
                                    <div class="text-xs text-gray-500">{{ $item->quantity }}x {{ $item->formatted_price }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Total -->
                    <div class="border-t-2 border-gray-200 pt-4 mt-4">
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-4 border border-blue-100">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-lg font-bold text-gray-900">Total Pembayaran:</span>
                                <span class="text-2xl font-extrabold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">{{ $cart->formatted_total_price }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/>
                                </svg>
                                <span class="font-medium">{{ $cart->total_items }} produk</span>
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Info -->
                    <div class="mt-6 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-5 border border-blue-100">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <h4 class="font-bold text-gray-900">Informasi Pengiriman</h4>
                        </div>
                    <div class="space-y-2 text-sm text-gray-600">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Pesanan akan divalidasi admin terlebih dahulu
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Setelah divalidasi akan dikirim oleh driver
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Pemesanan hanya berlaku di Area Bekasi
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Map scripts removed intentionally -->
@endsection
