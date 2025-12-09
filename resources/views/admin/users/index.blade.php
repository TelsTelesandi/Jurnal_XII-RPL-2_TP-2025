@extends('admin.layouts.app')

@section('title', 'Kelola Users')
@section('page-title', 'Kelola Users')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-3">
        <h2 class="text-xl font-bold text-gray-800">👥 Kelola Users</h2>
        <a href="{{ route('admin.users.create') }}" 
           class="inline-flex items-center bg-blue-600 hover:bg-blue-700 
                  text-white font-semibold px-3 py-2 rounded-md shadow text-sm">
           <i class="fa fa-plus mr-2"></i> Tambah User
        </a>
    </div>

    <!-- Statistik -->
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
        <div class="bg-white p-3 rounded-lg shadow text-center">
            <span class="text-xs text-gray-500 block">Total</span>
            <span class="text-lg font-bold text-blue-600">{{ $totalUsers ?? 0 }}</span>
        </div>
        <div class="bg-white p-3 rounded-lg shadow text-center">
            <span class="text-xs text-gray-500 block">Terverifikasi</span>
            <span class="text-lg font-bold text-green-600">{{ $activeUsers ?? 0 }}</span>
        </div>
        <div class="bg-white p-3 rounded-lg shadow text-center col-span-2 sm:col-span-1">
            <span class="text-xs text-gray-500 block">Belum Verifikasi</span>
            <span class="text-lg font-bold text-yellow-500">{{ $pendingUsers ?? 0 }}</span>
        </div>
    </div>

    <!-- Pencarian -->
    <div class="bg-white p-3 rounded-lg shadow">
        <form method="GET" action="{{ route('admin.users.index') }}" 
              class="flex gap-2">
            <input type="text" name="q" value="{{ request('q') }}"
                   placeholder="Cari nama atau email..."
                   class="flex-1 px-3 py-2 border border-gray-300 rounded-md text-sm 
                          focus:ring focus:ring-blue-200 focus:outline-none">
            <button type="submit" 
                    class="px-3 py-2 bg-blue-600 text-white rounded-md shadow 
                           hover:bg-blue-700 text-sm">
                Cari
            </button>
        </form>
    </div>

    <!-- List Users (mobile-friendly) -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <!-- Mode Desktop: Tabel -->
        <div class="hidden sm:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left font-semibold text-gray-600">Nama</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-600">Email</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-600">Role</th>
                        <th class="px-4 py-2 text-left font-semibold text-gray-600">Status</th>
                        <th class="px-4 py-2 text-right font-semibold text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $user)
                    <tr>
                        <td class="px-4 py-2 font-medium text-gray-800 flex items-center gap-2">
                            <img src="{{ $user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name) }}" 
                                 alt="{{ $user->name }}" 
                                 class="h-8 w-8 rounded-full border">
                            {{ $user->name }}
                        </td>
                        <td class="px-4 py-2 text-gray-600">{{ $user->email }}</td>
                      <td class="px-4 py-2 text-gray-600">
    @if($user->role_id == 1)
        Admin
    @elseif($user->role_id == 3)
        Moderator
    @else
        User
    @endif
</td>

                        <td class="px-4 py-2">
                            @if($user->email_verified_at)
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                    Aktif
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                                    Pending
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-right space-x-1 whitespace-nowrap">
                            <a href="{{ route('admin.users.edit', $user->id) }}" 
                               class="px-2 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600">Edit</a>
                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        onclick="return confirm('Yakin ingin menghapus user ini?')" 
                                        class="px-2 py-1 bg-red-500 text-white text-xs rounded hover:bg-red-600">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-3 text-center text-gray-500">
                            Tidak ada user ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mode Mobile: Card list -->
        <div class="sm:hidden divide-y divide-gray-100">
            @forelse($users as $user)
            <div class="p-3 flex flex-col gap-2">
                <div class="flex items-center gap-3">
                    <img src="{{ $user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name) }}" 
                         alt="{{ $user->name }}" 
                         class="h-9 w-9 rounded-full border">
                    <div>
                        <p class="font-semibold text-gray-800">{{ $user->name }}</p>
                        <p class="text-xs text-gray-500">{{ $user->email }}</p>
                    </div>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-600">{{ $user->role->nama ?? 'User' }}</span>
                    @if($user->email_verified_at)
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">Aktif</span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">Pending</span>
                    @endif
                </div>
                <div class="flex gap-2 mt-2">
                    <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
    <a href="{{ route('admin.users.edit', $user->id) }}" 
       class="inline-flex items-center px-3 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600 transition">
       <i class="fa-solid fa-pen mr-1"></i> Edit
    </a>
    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline">
        @csrf
        @method('DELETE')
        <button type="submit" 
                onclick="return confirm('Yakin ingin menghapus user ini?')"
                class="inline-flex items-center px-3 py-1 bg-red-500 text-white text-xs rounded hover:bg-red-600 transition">
            <i class="fa-solid fa-trash mr-1"></i> Hapus
        </button>
    </form>
</td>

                </div>
            </div>
            @empty
            <p class="p-3 text-center text-gray-500">Tidak ada user ditemukan.</p>
            @endforelse
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $users->links() }}
    </div>
</div>
@endsection
