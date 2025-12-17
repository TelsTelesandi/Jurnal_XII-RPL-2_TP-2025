@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h1 class="text-2xl font-bold text-gray-900">📦 Konfirmasi Pengiriman</h1>
            <p class="text-sm text-gray-600">Order {{ $order->code }}</p>
        </div>

        <div class="px-6 py-6">
            <form method="post" action="{{ route('deliveries.store', $order) }}" enctype="multipart/form-data" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Penerima</label>
                    <input type="text" name="recipient" required value="{{ old('recipient') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                    @error('recipient')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pesan untuk Customer (opsional)</label>
                    <textarea name="driver_message" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Contoh: Paket diletakkan di depan pintu.">{{ old('driver_message') }}</textarea>
                    @error('driver_message')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanda Tangan (gambar, opsional)</label>
                        <input type="file" name="signature" accept="image/*" class="w-full text-sm" />
                        @error('signature')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Foto Bukti (opsional)</label>
                        <input type="file" name="photo" accept="image/*" class="w-full text-sm" />
                        @error('photo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (opsional)</label>
                    <textarea name="notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('notes') }}</textarea>
                </div>

                <div class="flex gap-3">
                    <button class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors" type="submit">Konfirmasi</button>
                    <a class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md font-medium transition-colors text-center" href="{{ route('orders.index') }}">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
