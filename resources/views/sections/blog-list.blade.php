@extends('layout.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-end gap-4 mb-6 sm:mb-8">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Blog & Artikel</h1>
            <p class="text-gray-600 mt-1 text-sm sm:text-base">Temukan insight dan informasi terbaru</p>
        </div>

        <!-- Sort -->
        <form method="GET" action="{{ route('blog.list') }}" class="flex items-center space-x-2 sm:space-x-3">
            <label class="text-xs sm:text-sm text-gray-600 whitespace-nowrap">Urutkan:</label>
            <select name="sort" onchange="this.form.submit()"
                class="border border-gray-300 rounded-lg px-2 sm:px-3 py-1.5 sm:py-2 text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 min-w-0">
                <option value="terbaru" {{ request('sort') == 'terbaru' ? 'selected' : '' }}>Terbaru</option>
                <option value="terlama" {{ request('sort') == 'terlama' ? 'selected' : '' }}>Terlama</option>
                <option value="populer" {{ request('sort') == 'populer' ? 'selected' : '' }}>Terpopuler</option>
            </select>
        </form>
    </div>

    <!-- Articles Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
        @forelse($posts as $post)
            <article class="group bg-white rounded-lg border border-gray-200 hover:border-gray-300 overflow-hidden transition-all duration-200 hover:shadow-md">
                <a href="{{ route('blog.show', $post->slug) }}" class="block">
                    
                    <!-- Image -->
                    @if($post->thumbnail)
                        <div class="aspect-video overflow-hidden">
                            <img src="{{ asset('storage/'.$post->thumbnail) }}" 
                                 alt="{{ $post->judul }}" loading="lazy"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        </div>
                    @else
                        <div class="aspect-video bg-gray-100 flex items-center justify-center">
                            <svg class="w-8 h-8 sm:w-12 sm:h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    @endif

                    <!-- Content -->
                    <div class="p-3 sm:p-4">
                        <h2 class="font-semibold text-gray-900 group-hover:text-blue-600 line-clamp-2 mb-2 leading-tight text-sm sm:text-base">
                            {{ $post->judul }}
                        </h2>
                        
                        <p class="text-gray-600 text-xs sm:text-sm line-clamp-2 mb-3">
                            {{ \Illuminate\Support\Str::limit($post->excerpt ?? strip_tags($post->isi), 80) }}
                        </p>
                        
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 sm:gap-0 text-xs text-gray-500">
                            <span>{{ $post->created_at->format('d M Y') }}</span>
                            <div class="flex items-center space-x-2 sm:space-x-3">
                                @if($post->views_count)
                                    <span class="flex items-center gap-1" title="Dilihat"><i class="far fa-eye text-blue-400"></i> {{ number_format($post->views_count) }}</span>
                                @endif
                                <span class="flex items-center gap-1" title="Komentar"><i class="far fa-comment-dots text-green-400"></i> {{ number_format($post->comments_count ?? 0) }}</span>
                                <span class="flex items-center gap-1" title="Suka"><i class="far fa-heart text-red-400"></i> {{ number_format($post->likes_count ?? 0) }}</span>
                            </div>
                        </div>
                    </div>
                </a>
            </article>
        @empty
            <div class="col-span-full text-center py-8 sm:py-12">
                <svg class="w-12 h-12 sm:w-16 sm:h-16 text-gray-300 mx-auto mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2.5 2.5 0 00-2.5-2.5H15"></path>
                </svg>
                <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-2">Belum ada artikel</h3>
                <p class="text-gray-500 text-sm sm:text-base">Artikel akan muncul di sini setelah dipublikasikan</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($posts->hasPages())
        <div class="mt-8 sm:mt-12">
            {{ $posts->withQueryString()->links() }}
        </div>
    @endif
</div>

<style>
/* Line Clamp Utility */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Smooth transitions */
.group:hover img {
    transform: scale(1.05);
}

/* Focus states */
select:focus,
a:focus {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}

/* Responsive adjustments */
@media (max-width: 640px) {
    .aspect-video {
        aspect-ratio: 16 / 9;
    }
}
</style>
@endsection