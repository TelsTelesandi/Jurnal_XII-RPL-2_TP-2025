@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4">
    <div class="bg-white shadow-2xl rounded-3xl border border-gray-100 overflow-hidden">
        <!-- Header with Gradient -->
        <div class="relative bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 px-8 py-12">
            <div class="absolute inset-0 bg-black opacity-5"></div>
            <div class="relative z-10 text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-white/20 backdrop-blur rounded-2xl mb-4 shadow-xl">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                </div>
                <h1 class="text-4xl font-extrabold text-white mb-2">
                    Tambah Produk Baru
                </h1>
                <p class="text-emerald-100 text-lg">Tambahkan produk air mineral baru ke dalam katalog toko</p>
            </div>
        </div>

        <!-- Form -->
        <div class="px-8 py-8 bg-gray-50">
            <form method="POST" action="{{ route('products.store') }}" class="space-y-6">
                @csrf
                
                <!-- Product Basic Info -->
                <div class="bg-white rounded-2xl p-6 shadow-lg border border-blue-100">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 p-3 rounded-xl shadow-md">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">Informasi Dasar</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Produk *</label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   placeholder="Contoh: AQUA Air Mineral Botol 600ml">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Brand *</label>
                            <select name="brand" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Pilih Brand</option>
                                <option value="AQUA" {{ old('brand') === 'AQUA' ? 'selected' : '' }}>AQUA</option>
                                <option value="VIT" {{ old('brand') === 'VIT' ? 'selected' : '' }}>VIT</option>
                                <option value="Le Minerale" {{ old('brand') === 'Le Minerale' ? 'selected' : '' }}>Le Minerale</option>
                                <option value="Cleo" {{ old('brand') === 'Cleo' ? 'selected' : '' }}>Cleo</option>
                                <option value="Prima" {{ old('brand') === 'Prima' ? 'selected' : '' }}>Prima</option>
                            </select>
                            @error('brand')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ukuran *</label>
                            <select name="size" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Pilih Ukuran</option>
                                <option value="220ml" {{ old('size') === '220ml' ? 'selected' : '' }}>220ml</option>
                                <option value="330ml" {{ old('size') === '330ml' ? 'selected' : '' }}>330ml</option>
                                <option value="600ml" {{ old('size') === '600ml' ? 'selected' : '' }}>600ml</option>
                                <option value="1.5L" {{ old('size') === '1.5L' ? 'selected' : '' }}>1.5L</option>
                                <option value="15L" {{ old('size') === '15L' ? 'selected' : '' }}>15L</option>
                                <option value="19L" {{ old('size') === '19L' ? 'selected' : '' }}>19L</option>
                            </select>
                            @error('size')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                            <textarea name="description" rows="3" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                      placeholder="Deskripsi produk...">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Pricing & Stock -->
                <div class="bg-white rounded-2xl p-6 shadow-lg border border-green-100">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="bg-gradient-to-r from-green-500 to-emerald-600 p-3 rounded-xl shadow-md">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">Harga & Stok</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Harga (Rp) *</label>
                            <input type="number" name="price" value="{{ old('price') }}" required min="0" step="0.01"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   placeholder="0">
                            @error('price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Stok *</label>
                            <input type="number" name="stock" value="{{ old('stock') }}" required min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   placeholder="0">
                            @error('stock')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Additional Info -->
                <div class="bg-white rounded-2xl p-6 shadow-lg border border-purple-100">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="bg-gradient-to-r from-purple-500 to-pink-500 p-3 rounded-xl shadow-md">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">Media & Lainnya</h3>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">URL Gambar</label>
                        <input type="url" name="image_url" value="{{ old('image_url') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500" 
                               placeholder="https://example.com/image.jpg">
                        @error('image_url')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Opsional: Link ke gambar produk</p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 pt-8">
                    <button type="submit" class="flex-1 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white px-8 py-4 rounded-xl font-bold text-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Simpan Produk
                    </button>
                    <a href="{{ route('products.index') }}" class="flex-1 bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 hover:text-gray-900 px-8 py-4 rounded-xl font-bold text-lg shadow hover:shadow-lg transition-all duration-200 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
