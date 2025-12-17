@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 text-center p-8">
        <!-- Success Icon -->
        <div class="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-6">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>

        <!-- Success Message -->
        <h1 class="text-3xl font-bold text-gray-900 mb-4">🎉 Pesanan Berhasil Dibuat!</h1>
        <p class="text-lg text-gray-600 mb-6">
            Terima kasih atas pesanan Anda. Pesanan sedang menunggu validasi dari admin.
        </p>

        <!-- Process Steps -->
        <div class="bg-blue-50 rounded-lg p-6 mb-8">
            <h3 class="text-lg font-medium text-gray-900 mb-4">📋 Proses Selanjutnya:</h3>
            <div class="space-y-3 text-left">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-yellow-100 text-yellow-800 rounded-full flex items-center justify-center text-sm font-medium mr-3">1</div>
                    <div>
                        <div class="font-medium text-gray-900">Menunggu Validasi Admin</div>
                        <div class="text-sm text-gray-600">Admin akan memvalidasi pesanan dan ketersediaan stok</div>
                    </div>
                </div>
                
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-blue-100 text-blue-800 rounded-full flex items-center justify-center text-sm font-medium mr-3">2</div>
                    <div>
                        <div class="font-medium text-gray-900">Assignment ke Driver</div>
                        <div class="text-sm text-gray-600">Setelah divalidasi, pesanan akan diberikan ke driver untuk pengiriman</div>
                    </div>
                </div>
                
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-green-100 text-green-800 rounded-full flex items-center justify-center text-sm font-medium mr-3">3</div>
                    <div>
                        <div class="font-medium text-gray-900">Pengiriman</div>
                        <div class="text-sm text-gray-600">Driver akan mengirim pesanan ke alamat yang Anda berikan</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Info -->
        <div class="bg-gray-50 rounded-lg p-6 mb-8">
            <h3 class="text-lg font-medium text-gray-900 mb-2">📞 Butuh Bantuan?</h3>
            <p class="text-gray-600 mb-4">
                Jika ada pertanyaan tentang pesanan Anda, silakan hubungi customer service kami.
            </p>
            <div class="flex justify-center space-x-4 text-sm">
                <div class="flex items-center text-gray-600">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                    </svg>
                    WhatsApp: 0812-3456-7890
                </div>
                <div class="flex items-center text-gray-600">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                    </svg>
                    Email: cs@aquastore.com
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('products.index') }}" 
               class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-md font-medium transition-colors">
                🛒 Lanjut Belanja
            </a>
            @auth
                <a href="{{ route('orders.index') }}" 
                   class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-3 rounded-md font-medium transition-colors">
                    📋 Lihat Pesanan Saya
                </a>
            @endauth
        </div>
    </div>
</div>
@endsection
