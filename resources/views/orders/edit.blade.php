@extends('layouts.app')

@section('content')
<div class="bg-white shadow-2xl rounded-2xl border border-gray-100 overflow-hidden">
    <!-- Header -->
    <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-cyan-50">
        <h1 class="text-2xl font-bold text-gray-900 flex items-center">
            ✏️ Edit Order: {{ $order->code }}
        </h1>
        <p class="mt-1 text-sm text-gray-600">Perbarui informasi order pengiriman barang.</p>
    </div>

    <!-- Form -->
    <div class="px-6 py-6">
        <form method="post" action="{{ route('orders.update', $order) }}" class="space-y-6">
            @csrf
            @method('PUT')
            
            <!-- Origin Section -->
            <div class="bg-blue-50 rounded-lg p-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    📍 Informasi Asal
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lokasi Asal</label>
                        <input type="text" name="origin_name" value="{{ old('origin_name', $order->origin_name) }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                               placeholder="Contoh: Gudang Pusat Jakarta">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap Asal</label>
                        <textarea name="origin_address" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                  placeholder="Alamat lengkap lokasi asal">{{ old('origin_address', $order->origin_address) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Latitude</label>
                        <input type="number" step="0.0000001" name="origin_lat" value="{{ old('origin_lat', $order->origin_lat) }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                               placeholder="-6.2088">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Longitude</label>
                        <input type="number" step="0.0000001" name="origin_lng" value="{{ old('origin_lng', $order->origin_lng) }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                               placeholder="106.8456">
                    </div>
                </div>
            </div>

            <!-- Destination Section -->
            <div class="bg-green-50 rounded-lg p-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    🎯 Informasi Tujuan
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lokasi Tujuan</label>
                        <input type="text" name="destination_name" value="{{ old('destination_name', $order->destination_name) }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                               placeholder="Contoh: Toko ABC Bandung">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap Tujuan</label>
                        <textarea name="destination_address" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                  placeholder="Alamat lengkap lokasi tujuan">{{ old('destination_address', $order->destination_address) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Latitude</label>
                        <input type="number" step="0.0000001" name="destination_lat" value="{{ old('destination_lat', $order->destination_lat) }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                               placeholder="-6.9175">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Longitude</label>
                        <input type="number" step="0.0000001" name="destination_lng" value="{{ old('destination_lng', $order->destination_lng) }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                               placeholder="107.6191">
                    </div>
                </div>
            </div>

            <!-- Time Window Section -->
            <div class="bg-yellow-50 rounded-lg p-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    ⏰ Waktu Pengiriman
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Waktu Mulai</label>
                        <input type="datetime-local" name="window_from" value="{{ old('window_from', optional($order->window_from)->format('Y-m-d\TH:i')) }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Waktu Selesai</label>
                        <input type="datetime-local" name="window_to" value="{{ old('window_to', optional($order->window_to)->format('Y-m-d\TH:i')) }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>

            <!-- Package Details Section -->
            <div class="bg-purple-50 rounded-lg p-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    📦 Detail Paket & Status
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Berat (kg)</label>
                        <input type="number" step="0.01" name="weight" value="{{ old('weight', $order->weight) }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                               placeholder="0.00">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Volume (m³)</label>
                        <input type="number" step="0.001" name="volume" value="{{ old('volume', $order->volume) }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                               placeholder="0.000">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @php($statuses = ['pending','planned','assigned','loading','in_transit','delivered','cancelled'])
                            @foreach($statuses as $s)
                                <option value="{{ $s }}" @selected(old('status', $order->status) === $s)>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                        <textarea name="notes" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                  placeholder="Catatan tambahan untuk pengiriman (opsional)">{{ old('notes', $order->notes) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-3 pt-6 border-t border-gray-200">
                <button type="submit" class="inline-flex items-center justify-center px-6 py-3 text-white text-sm font-semibold rounded-lg transition-all shadow-md bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan Perubahan
                </button>
                <a href="{{ route('orders.index') }}" class="inline-flex items-center justify-center px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-semibold rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
