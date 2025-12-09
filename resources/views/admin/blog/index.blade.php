@extends('admin.layouts.app')

@section('title', 'Kelola Blog')
@section('page-title', 'Kelola Blog')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <a href="{{ route('admin.blog.create') }}"
           class="inline-flex items-center px-4 py-2 bg-blue-600 rounded-lg font-semibold text-sm text-white shadow hover:bg-blue-700 focus:outline-none">
            <i class="fas fa-plus mr-2"></i> Buat Blog Baru
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white shadow rounded-lg p-5 flex items-center">
            <i class="fas fa-newspaper text-2xl text-blue-600"></i>
            <div class="ml-4">
                <p class="text-sm text-gray-500">Total Blog</p>
                <p class="text-lg font-semibold">{{ $posts->total() }}</p>
            </div>
        </div>
        <div class="bg-white shadow rounded-lg p-5 flex items-center">
            <i class="fas fa-eye text-2xl text-green-600"></i>
            <div class="ml-4">
                <p class="text-sm text-gray-500">Views Hari Ini</p>
                <p class="text-lg font-semibold">{{ $todayViews ?? 0 }}</p>
            </div>
        </div>
        <div class="bg-white shadow rounded-lg p-5 flex items-center">
            <i class="fas fa-calendar text-2xl text-yellow-600"></i>
            <div class="ml-4">
                <p class="text-sm text-gray-500">Views Bulan Ini</p>
                <p class="text-lg font-semibold">{{ $monthViews ?? 0 }}</p>
            </div>
        </div>
        <div class="bg-white shadow rounded-lg p-5 flex items-center">
            <i class="fas fa-clock text-2xl text-gray-600"></i>
            <div class="ml-4">
                <p class="text-sm text-gray-500">Views Tahun Ini</p>
                <p class="text-lg font-semibold">{{ $yearViews ?? 0 }}</p>
            </div>
        </div>
    </div>
    

    <!-- Articles List -->
    @if($posts->count() > 0)
        <ul class="space-y-3">
            @foreach($posts as $post)
                <li class="bg-white border rounded-lg shadow-sm hover:shadow-md transition p-4">
                    <div class="flex items-start">
                        <!-- Thumbnail -->
                        <div class="flex-shrink-0 h-16 w-16">
                            @if($post->thumbnail)
                                <img class="h-16 w-16 rounded-lg object-cover"
                                     src="{{ asset('storage/'.$post->thumbnail) }}"
                                     alt="{{ $post->judul }}">
                            @else
                                <div class="h-16 w-16 rounded-lg bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center">
                                    <i class="fas fa-image text-gray-500 text-xl"></i>
                                </div>
                            @endif
                        </div>

                        <!-- Content -->
                        <div class="ml-4 flex-1 min-w-0">
                            <a href="{{ route('admin.blog.show',$post) }}"
                               class="text-blue-600 font-medium hover:underline">
                               {{ $post->judul }}
                            </a>

                            <div class="mt-2 flex flex-wrap gap-4 text-sm text-gray-500">
                                <span class="inline-flex items-center">
                                    <i class="fas fa-user mr-1"></i>{{ $post->author->name ?? 'Admin' }}
                                </span>
                                <span class="inline-flex items-center">
                                    <i class="fas fa-eye mr-1"></i>{{ $post->views_count ?? 0 }} views
                                </span>
                                <span class="inline-flex items-center">
                                    <i class="fas fa-calendar mr-1"></i>{{ $post->created_at->format('d M Y') }}
                                </span>
                            </div>

                            @if($post->excerpt)
                                <p class="mt-2 text-sm text-gray-600 line-clamp-2">
                                    {{ \Illuminate\Support\Str::limit($post->excerpt, 120) }}
                                </p>
                            @endif

                            <!-- Actions: MOBILE -->
                            <div class="sm:hidden w-full mt-3">
                                <div class="grid grid-cols-3 gap-2">
                                    <a href="{{ route('admin.blog.show',$post) }}"
                                       class="inline-flex items-center justify-center gap-2 px-3 py-3 min-h-[44px]
                                              rounded-lg border bg-white text-gray-700 text-sm font-medium
                                              hover:bg-gray-50 active:bg-gray-100"
                                       aria-label="Lihat Blog">
                                        <i class="fas fa-eye"></i><span>Lihat</span>
                                    </a>
                                    <a href="{{ route('admin.blog.edit',$post) }}"
                                       class="inline-flex items-center justify-center gap-2 px-3 py-3 min-h-[44px]
                                              rounded-lg border bg-white text-gray-700 text-sm font-medium
                                              hover:bg-gray-50 active:bg-gray-100"
                                       aria-label="Edit Blog">
                                        <i class="fas fa-edit"></i><span>Edit</span>
                                    </a>
                                    <form method="POST" action="{{ route('admin.blog.destroy',$post) }}"
                                          onsubmit="return confirm('Yakin ingin menghapus Blog ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="w-full inline-flex items-center justify-center gap-2 px-3 py-3 min-h-[44px]
                                                       rounded-lg border bg-red-50 text-red-700 text-sm font-semibold
                                                       hover:bg-red-100 active:bg-red-200"
                                                aria-label="Hapus Blog">
                                            <i class="fas fa-trash"></i><span>Hapus</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Actions: DESKTOP -->
                        <div class="ml-4 flex-shrink-0 items-center space-x-3 hidden sm:flex">
                            <a href="{{ route('admin.blog.show',$post) }}"
                               class="text-indigo-600 hover:text-indigo-900" title="Lihat" aria-label="Lihat Blog">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.blog.edit',$post) }}"
                               class="text-green-600 hover:text-green-900" title="Edit" aria-label="Edit Blog">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.blog.destroy',$post) }}"
                                  onsubmit="return confirm('Yakin ingin menghapus Blog ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900" title="Hapus" aria-label="Hapus Blog">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $posts->withQueryString()->links() }}
        </div>
    @else
        <div class="text-center py-12 bg-white rounded-lg border">
            <i class="fas fa-newspaper text-gray-400 text-6xl mb-4"></i>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada Blog</h3>
            <p class="mt-1 text-sm text-gray-500">Mulai buat Blog pertama Anda sekarang.</p>
            <div class="mt-6">
                <a href="{{ route('admin.blog.create') }}"
                   class="inline-flex items-center px-6 py-3 bg-blue-600 text-white text-sm font-semibold rounded-lg shadow hover:bg-blue-700">
                    <i class="fas fa-plus mr-2"></i> Buat Blog Baru
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
