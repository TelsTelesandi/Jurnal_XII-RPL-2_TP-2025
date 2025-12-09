@extends('admin.layouts.app')

@section('title', 'Kelola Komentar')
@section('page-title', 'Kelola Komentar')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between md:items-center gap-4">
        <h2 class="text-2xl font-bold text-gray-800">💬 Kelola Komentar</h2>
    </div>

    <!-- Statistik -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="bg-white p-5 rounded-lg shadow flex flex-col">
        <span class="text-sm text-gray-500">Total Komentar</span>
        <span class="text-2xl font-bold text-blue-600">{{ $comments->total() }}</span>
    </div>
    <div class="bg-white p-5 rounded-lg shadow flex flex-col">
        <span class="text-sm text-gray-500">Komentar Hari Ini</span>
        <span class="text-2xl font-bold text-green-600">{{ \App\Models\BlogComment::whereDate('created_at', today())->count() }}</span>
    </div>
</div>


    <!-- Pencarian -->
    <div class="bg-white p-4 rounded-lg shadow">
        <form method="GET" action="{{ route('admin.comments.index') }}" 
              class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="🔍 Cari komentar, nama user, atau judul artikel..."
                   class="flex-1 px-4 py-2 border border-gray-300 rounded-md 
                          focus:ring focus:ring-blue-200 focus:outline-none">
            <button type="submit" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-md shadow 
                           hover:bg-blue-700 transition w-full sm:w-auto">
                Cari
            </button>
        </form>
    </div>

    <!-- Komentar List -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <!-- Desktop Table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left font-semibold text-gray-600">User</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-600">Isi</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-600">Artikel</th>
                        <th class="px-6 py-3 text-right font-semibold text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($comments as $comment)
                    <tr>
                        <!-- User -->
                        <td class="px-6 py-4 text-gray-800">
                            <div class="flex items-center gap-2">
                                <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center text-xs font-bold text-gray-600">
                                    {{ strtoupper(substr($comment->user->name ?? 'A',0,1)) }}
                                </div>
                                <div>
                                    <div class="font-medium">{{ $comment->user->name ?? 'Anonim' }}</div>
                                    <div class="text-xs text-gray-500">{{ $comment->user->email ?? '-' }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- Isi -->
                        <td class="px-6 py-4 text-gray-700 max-w-xs truncate">
                            {{ $comment->isi }}
                        </td>

                        <!-- Artikel -->
                        <td class="px-6 py-4 text-blue-600">
                            <a href="{{ route('blog.show', $comment->post->slug) }}" target="_blank" class="hover:underline">
                                {{ $comment->post->judul }}
                            </a>
                        </td>

                        <!-- Aksi -->
                        <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                            <form action="{{ route('admin.comments.destroy', $comment->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Hapus komentar ini?')" 
                                        class="px-3 py-1 bg-red-500 text-white text-xs rounded hover:bg-red-600 transition">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                            Belum ada komentar ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div class="block md:hidden divide-y divide-gray-200">
            @forelse($comments as $comment)
            <div class="p-4">
                <div class="flex items-center gap-3 mb-2">
                    <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center text-xs font-bold text-gray-600">
                        {{ strtoupper(substr($comment->user->name ?? 'A',0,1)) }}
                    </div>
                    <div>
                        <div class="font-medium text-gray-800">{{ $comment->user->name ?? 'Anonim' }}</div>
                        <div class="text-xs text-gray-500">{{ $comment->user->email ?? '-' }}</div>
                    </div>
                </div>
                <p class="text-gray-700 text-sm mb-2">{{ $comment->isi }}</p>
                <a href="{{ route('blog.show', $comment->post->slug) }}" target="_blank" class="text-sm text-blue-600 hover:underline">
                    {{ $comment->post->judul }}
                </a>
                <div class="mt-3 text-right">
                    <form action="{{ route('admin.comments.destroy', $comment->id) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" onclick="return confirm('Hapus komentar ini?')" 
                                class="px-3 py-1 bg-red-500 text-white text-xs rounded hover:bg-red-600 transition">
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <p class="text-gray-500 text-center py-4">Belum ada komentar ditemukan.</p>
            @endforelse
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $comments->links() }}
    </div>
</div>
@endsection
