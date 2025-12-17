@extends('layouts.app')

@section('content')
<!-- Page Header with Gradient -->
<div class="relative bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-3xl mb-8 overflow-hidden shadow-2xl">
    <div class="absolute inset-0 bg-black opacity-5"></div>
    <div class="relative z-10 px-8 py-12 text-center">
        <div class="inline-flex items-center justify-center w-24 h-24 bg-white/20 backdrop-blur rounded-full mb-4 shadow-xl">
            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
        </div>
        <h1 class="text-4xl font-extrabold text-white mb-2">Akun Saya</h1>
        <p class="text-purple-100 text-lg">Kelola profil dan informasi akun Anda</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Profile Card -->
    <div class="lg:col-span-1">
        <div class="bg-white shadow-xl rounded-2xl border border-gray-100 overflow-hidden">
            <div class="bg-gradient-to-r from-indigo-500 to-purple-500 px-6 py-5">
                <div class="flex items-center gap-3">
                    <div class="bg-white/20 backdrop-blur p-2 rounded-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-white">Profil Saya</h2>
                        <p class="text-sm text-purple-100">Edit informasi Anda</p>
                    </div>
                </div>
            </div>
            <div class="px-6 py-6">
                <form method="POST" action="{{ route('account.update') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" value="{{ $user->email }}" disabled class="w-full px-3 py-2 border border-gray-200 bg-gray-50 text-gray-500 rounded-md" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Telepon</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                        @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                        <textarea name="address" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('address', $user->address) }}</textarea>
                        @error('address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bio</label>
                        <textarea name="bio" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Ceritakan sedikit tentang Anda...">{{ old('bio', $user->bio) }}</textarea>
                        @error('bio')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Avatar URL (opsional)</label>
                        <input type="url" name="avatar_url" value="{{ old('avatar_url', $user->avatar_url) }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                        @error('avatar_url')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white px-6 py-3 rounded-xl font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Simpan Profil
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Activity & Notifications -->
    <div class="lg:col-span-2 space-y-6">
        @if($user->role === 'Driver' && $driverStatus)
            <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">🚚</span>
                    <div>
                        <div class="text-sm text-gray-600">Status Driver</div>
                        <div class="text-lg font-semibold text-gray-900">{{ $driverStatus }}</div>
                    </div>
                </div>
                @if($driverStatus === 'Dalam Perjalanan')
                    <span class="px-2 py-1 text-xs rounded bg-indigo-100 text-indigo-800">Busy</span>
                @elseif($driverStatus === 'Menunggu Konfirmasi / Mulai')
                    <span class="px-2 py-1 text-xs rounded bg-purple-100 text-purple-800">Assigned</span>
                @else
                    <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800">Idle</span>
                @endif
        @if($user->role === 'Driver')
            <div class="bg-white shadow-xl rounded-2xl border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-sky-500 to-cyan-500 px-6 py-5 flex items-center gap-3">
                    <div class="bg-white/20 backdrop-blur p-2 rounded-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 13h1l3 7h8l3-7h1M5 13l1.5-4.5A2 2 0 018.4 7h7.2a2 2 0 011.9 1.5L19 13"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-white">Kendaraan Saat Ini</h2>
                        <p class="text-sm text-sky-100">Informasi kendaraan yang ditugaskan</p>
                    </div>
                </div>
                <div class="px-6 py-4">
                    @if(isset($driverVehicle) && $driverVehicle)
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-lg font-semibold text-gray-900">{{ $driverVehicle->plate }}</div>
                                <div class="text-sm text-gray-600">{{ $driverVehicle->name ?: 'Tanpa nama' }}</div>
                            </div>
                            @if($driverVehicle->active)
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">Aktif</span>
                            @else
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-gray-200 text-gray-700">Nonaktif</span>
                            @endif
                        </div>
                    @else
                        <div class="text-gray-600">Tidak ada kendaraan yang sedang digunakan.</div>
                    @endif
                </div>
            </div>
        @endif
            </div>
        @endif
        <!-- Notifications -->
        <div class="bg-white shadow-xl rounded-2xl border border-gray-100 overflow-hidden">
            <div class="bg-gradient-to-r from-yellow-500 to-orange-500 px-6 py-5 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="bg-white/20 backdrop-blur p-2 rounded-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-white">Notifikasi Terbaru</h2>
                        <p class="text-sm text-yellow-100">Update aktivitas Anda</p>
                    </div>
                </div>
                <span class="bg-white/20 backdrop-blur text-white font-bold text-sm px-3 py-1.5 rounded-full shadow">{{ $notifications->count() }}</span>
            </div>
            <div class="divide-y">
                @forelse($notifications as $n)
                    <div class="px-6 py-4 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900">{{ $n->title }}</p>
                                @if($n->body)
                                    <p class="text-sm text-gray-600">{{ $n->body }}</p>
                                @endif
                            </div>
                            @if($n->link_url)
                                <a href="{{ $n->link_url }}" class="text-sm text-blue-600 hover:text-blue-700">Lihat</a>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 mt-1">{{ $n->created_at->diffForHumans() }}</p>
                    </div>
                @empty
                    <div class="px-6 py-10 text-center text-gray-500">Tidak ada notifikasi.</div>
                @endforelse
            </div>
        </div>

        @if($user->role === 'Customer')
            <!-- Customer Orders -->
            <div class="bg-white shadow-xl rounded-2xl border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-500 to-indigo-500 px-6 py-5">
                    <div class="flex items-center gap-3">
                        <div class="bg-white/20 backdrop-blur p-2 rounded-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-white">Pesanan Dalam Proses</h2>
                            <p class="text-sm text-blue-100">Pending, Validated, Assigned, In Transit</p>
                        </div>
                    </div>
                </div>
                <div class="divide-y">
                    @forelse($pendingOrders as $o)
                        <div class="px-6 py-4 flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900">{{ $o->code }}</p>
                                <p class="text-sm text-gray-600">{{ $o->items->count() }} item • {{ $o->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded">{{ ucfirst($o->status) }}</span>
                        </div>
                    @empty
                        <div class="px-6 py-10 text-center text-gray-500">Tidak ada pesanan dalam proses.</div>
                    @endforelse
                </div>
            </div>

            <div class="bg-white shadow-xl rounded-2xl border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-green-500 to-emerald-500 px-6 py-5">
                    <div class="flex items-center gap-3">
                        <div class="bg-white/20 backdrop-blur p-2 rounded-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-white">Pesanan Selesai</h2>
                            <p class="text-sm text-green-100">Riwayat pesanan delivered</p>
                        </div>
                    </div>
                </div>
                <div class="divide-y">
                    @forelse($completedOrders as $o)
                        <div class="px-6 py-4 flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900">{{ $o->code }}</p>
                                <p class="text-sm text-gray-600">{{ $o->items->count() }} item • {{ $o->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">Delivered</span>
                        </div>
                    @empty
                        <div class="px-6 py-10 text-center text-gray-500">Belum ada pesanan selesai.</div>
                    @endforelse
                </div>
            </div>
        @endif

        @if($user->role === 'Driver')
            <!-- Driver History -->
            <div class="bg-white shadow-xl rounded-2xl border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-orange-500 to-red-500 px-6 py-5">
                    <div class="flex items-center gap-3">
                        <div class="bg-white/20 backdrop-blur p-2 rounded-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-white">Riwayat Pengantaran</h2>
                            <p class="text-sm text-orange-100">Delivery yang telah selesai</p>
                        </div>
                    </div>
                </div>
                <div class="divide-y">
                    @forelse($deliveredByDriver as $o)
                        <div class="px-6 py-4 flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900">{{ $o->code }}</p>
                                <p class="text-sm text-gray-600">{{ $o->items->count() }} item • {{ $o->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">Delivered</span>
                        </div>
                    @empty
                        <div class="px-6 py-10 text-center text-gray-500">Belum ada pengantaran selesai.</div>
                    @endforelse
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
