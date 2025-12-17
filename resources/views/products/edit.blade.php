@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200">
            <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                ✏️ Edit Produk: {{ $product->name }}
            </h1>
            <p class="mt-1 text-sm text-gray-600">Perbarui informasi produk air mineral.</p>
        </div>

        <!-- Form -->
        <div class="px-6 py-6">
            <form method="POST" action="{{ route('products.update', $product) }}" class="space-y-6">
                @csrf
                @method('PUT')
                
                <!-- Product Basic Info -->
                <div class="bg-blue-50 rounded-lg p-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                        📝 Informasi Dasar
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Produk *</label>
                            <input type="text" name="name" value="{{ old('name', $product->name) }}" required
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
                                <option value="AQUA" {{ old('brand', $product->brand) === 'AQUA' ? 'selected' : '' }}>AQUA</option>
                                <option value="VIT" {{ old('brand', $product->brand) === 'VIT' ? 'selected' : '' }}>VIT</option>
                                <option value="Le Minerale" {{ old('brand', $product->brand) === 'Le Minerale' ? 'selected' : '' }}>Le Minerale</option>
                                <option value="Cleo" {{ old('brand', $product->brand) === 'Cleo' ? 'selected' : '' }}>Cleo</option>
                                <option value="Prima" {{ old('brand', $product->brand) === 'Prima' ? 'selected' : '' }}>Prima</option>
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
                                <option value="220ml" {{ old('size', $product->size) === '220ml' ? 'selected' : '' }}>220ml</option>
                                <option value="330ml" {{ old('size', $product->size) === '330ml' ? 'selected' : '' }}>330ml</option>
                                <option value="600ml" {{ old('size', $product->size) === '600ml' ? 'selected' : '' }}>600ml</option>
                                <option value="1.5L" {{ old('size', $product->size) === '1.5L' ? 'selected' : '' }}>1.5L</option>
                                <option value="15L" {{ old('size', $product->size) === '15L' ? 'selected' : '' }}>15L</option>
                                <option value="19L" {{ old('size', $product->size) === '19L' ? 'selected' : '' }}>19L</option>
                            </select>
                            @error('size')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                            <textarea name="description" rows="3" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                      placeholder="Deskripsi produk...">{{ old('description', $product->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Pricing & Stock -->
                <div class="bg-green-50 rounded-lg p-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                        💰 Harga & Stok
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Harga (Rp) *</label>
                            <input type="number" name="price" value="{{ old('price', $product->price) }}" required min="0" step="0.01"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   placeholder="0">
                            @error('price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Stok *</label>
                            <input type="number" name="stock" value="{{ old('stock', $product->stock) }}" required min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   placeholder="0">
                            @error('stock')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Additional Info -->
                <div class="bg-purple-50 rounded-lg p-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                        🖼️ Media & Status
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">URL Gambar</label>
                            <input type="url" name="image_url" value="{{ old('image_url', $product->image_url) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   placeholder="https://example.com/image.jpg">
                            @error('image_url')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">Opsional: Link ke gambar produk</p>
                        </div>
                        
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" 
                                       {{ old('is_active', $product->is_active) ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <span class="ml-2 text-sm text-gray-700">Produk Aktif</span>
                            </label>
                            <p class="mt-1 text-xs text-gray-500">Centang untuk menampilkan produk di katalog</p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 pt-6 border-t border-gray-200">
                    <button type="submit" class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Simpan Perubahan
                    </button>
                    <a href="{{ route('products.show', $product) }}" class="inline-flex items-center justify-center px-6 py-3 bg-gray-300 hover:bg-gray-400 text-gray-700 text-sm font-medium rounded-md transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
