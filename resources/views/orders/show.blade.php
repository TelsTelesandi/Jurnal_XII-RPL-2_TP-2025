@extends('layouts.app')

@section('content')
<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <!-- Header -->
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                    👁️ Detail Order: {{ $order->code }}
                </h1>
                <p class="mt-1 text-sm text-gray-600">Informasi lengkap order pengiriman barang.</p>
            </div>
            <div class="flex gap-2">
                @php
                    $statusColors = [
                        'pending' => 'bg-yellow-100 text-yellow-800',
                        'planned' => 'bg-blue-100 text-blue-800',
                        'assigned' => 'bg-purple-100 text-purple-800',
                        'loading' => 'bg-orange-100 text-orange-800',
                        'in_transit' => 'bg-indigo-100 text-indigo-800',
                        'delivered' => 'bg-green-100 text-green-800',
                        'cancelled' => 'bg-red-100 text-red-800',
                    ];
                    $colorClass = $statusColors[$order->status] ?? 'bg-gray-100 text-gray-800';
                @endphp
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $colorClass }}">
                    {{ ucfirst($order->status) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="px-6 py-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Origin Section -->
            <div class="bg-blue-50 rounded-lg p-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    📍 Informasi Asal
                </h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Lokasi</label>
                        @php
                            $originName = $order->origin_name ?: config('googlemaps.company.name');
                        @endphp
                        <p class="mt-1 text-sm text-gray-900">{{ $originName ?: '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Alamat</label>
                        @php
                            $originAddr = $order->origin_address ?: config('googlemaps.company.address');
                        @endphp
                        <p class="mt-1 text-sm text-gray-900">{{ $originAddr ?: '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Koordinat</label>
                        @php
                            $origLat = $order->origin_lat ?: config('googlemaps.company_location.lat');
                            $origLng = $order->origin_lng ?: config('googlemaps.company_location.lng');
                        @endphp
                        <p class="mt-1 text-sm text-gray-900 font-mono">{{ $origLat }},  {{ $origLng }}</p>
                    </div>
                </div>
            </div>

            <!-- Destination Section -->
            <div class="bg-green-50 rounded-lg p-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    🎯 Informasi Tujuan
                </h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Lokasi</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $order->destination_name ?: '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Alamat</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $order->destination_address ?: '-' }}</p>
                    </div>
                    @if($order->destination_lat && $order->destination_lng)
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Koordinat</label>
                            <p class="mt-1 text-sm text-gray-900 font-mono">{{ $order->destination_lat }}, {{ $order->destination_lng }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Time Window Section -->
            @if($order->window_from || $order->window_to)
                <div class="bg-yellow-50 rounded-lg p-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                        ⏰ Waktu Pengiriman
                    </h3>
                    <div class="space-y-3">
                        @if($order->window_from)
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Waktu Mulai</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $order->window_from->format('d/m/Y H:i') }}</p>
                            </div>
                        @endif
                        @if($order->window_to)
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Waktu Selesai</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $order->window_to->format('d/m/Y H:i') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Package Details Section -->
            <div class="bg-purple-50 rounded-lg p-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    📦 Detail Paket
                </h3>
                <div class="space-y-3">
                    @if($order->weight)
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Berat</label>
                            <p class="mt-1 text-sm text-gray-900">{{ number_format($order->weight, 2) }} kg</p>
                        </div>
                    @endif
                    @if($order->volume)
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Volume</label>
                            <p class="mt-1 text-sm text-gray-900">{{ number_format($order->volume, 3) }} m³</p>
                        </div>
                    @endif
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Dibuat</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $order->created_at?->format('d/m/Y H:i') }}</p>
                    </div>
                    @if($order->notes)
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Catatan</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $order->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col gap-4 mt-6 pt-6 border-t border-gray-200">
            @if(auth()->check() && auth()->user()->role === 'Admin' && $order->driver_declined_at)
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="text-sm text-red-800 font-semibold">Alasan Penolakan Driver</div>
                            <div class="text-sm text-red-700 mt-1">{{ $order->driver_decline_reason ?: '-' }}</div>
                            <div class="text-xs text-gray-500 mt-2">Ditolak pada: {{ optional($order->driver_declined_at)->format('d/m/Y H:i') }}</div>
                            @if($order->driver_decline_admin_confirmed_at)
                                <div class="text-xs text-green-700 mt-1">Sudah dikonfirmasi Admin pada {{ $order->driver_decline_admin_confirmed_at->format('d/m/Y H:i') }}</div>
                            @endif
                        </div>
                        @if(!$order->driver_decline_admin_confirmed_at)
                            <form method="POST" action="{{ route('orders.driver.decline.confirm', $order) }}">
                                @csrf
                                @method('PATCH')
                                <button class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md text-sm" onclick="return confirm('Konfirmasi alasan penolakan driver?')">Konfirmasi Admin</button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif
            <div class="flex flex-wrap gap-3">
                @if(!(auth()->check() && auth()->user()->role === 'Driver'))
                <a href="{{ route('orders.edit', $order) }}" class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit Order
                </a>
                @endif
                <a href="{{ route('orders.index') }}" class="inline-flex items-center justify-center px-6 py-3 bg-gray-300 hover:bg-gray-400 text-gray-700 text-sm font-medium rounded-md transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali ke Daftar
                </a>

                @auth
                    @if(auth()->user()->role === 'Driver' && $order->assigned_driver_id === auth()->id())
                        @if($order->status === 'assigned')
                            <form method="POST" action="{{ route('orders.driver.accept', $order) }}" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md">
                                    ✅ Terima Penugasan
                                </button>
                            </form>
                            <form method="POST" action="{{ route('orders.driver.decline', $order) }}" class="inline flex items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <input type="text" name="reason" placeholder="Alasan penolakan (wajib)" required class="px-3 py-2 border border-gray-300 rounded-md text-sm">
                                @error('reason')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                                <button type="submit" class="inline-flex items-center px-6 py-3 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md"
                                        onclick="return confirm('Tolak penugasan ini?')">
                                    ❌ Tolak
                                </button>
                            </form>
                        @elseif($order->status === 'in_transit')
                            <a href="{{ route('deliveries.create', $order) }}" class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md">
                                📦 Konfirmasi Pengiriman
                            </a>
                        @endif
                    @endif
                @endauth

                @auth
                    @if(auth()->user()->role === 'Customer' && $order->status === 'delivered' && $order->delivery && !$order->delivery->customer_confirmed_at)
                        <a href="{{ route('deliveries.confirm.form', $order) }}" class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md">
                            ✅ Konfirmasi Penerimaan
                        </a>
                    @endif
                @endauth
            </div>

            {{-- Live map/tracking removed intentionally --}}
        </div>
    </div>
</div>
@endsection
