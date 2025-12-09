@extends('layout.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
    
    <!-- Article Header -->
    <header class="mb-6 sm:mb-8">
        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900 mb-3 sm:mb-4 leading-tight">
            {{ $post->judul }}
        </h1>
        
        <div class="flex flex-wrap items-center text-xs sm:text-sm text-gray-600 mb-4 sm:mb-6 gap-2 sm:gap-4">
            <span>{{ $post->created_at->translatedFormat('d F Y') }}</span>
            <span class="hidden sm:inline">•</span>
            <span>{{ $post->author->nama_lengkap ?? 'Admin' }}</span>
        </div>

        @if($post->thumbnail)
            <div class="mb-6 sm:mb-8">
                <img src="{{ asset('storage/'.$post->thumbnail) }}" 
                     alt="{{ $post->judul }}" 
                     class="w-full rounded-lg">
            </div>
        @endif
    </header>

    <!-- Article Content -->
    <article class="prose prose-sm sm:prose-lg max-w-none mb-8 sm:mb-12">
        <div class="text-gray-800 leading-relaxed text-base sm:text-lg">
            {!! nl2br(e($post->isi)) !!}
        </div>
    </article>

    <!-- Share Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-center py-4 sm:py-6 border-t border-gray-200 mb-6 sm:mb-8">
        <div class="flex flex-wrap items-center justify-center gap-2 sm:gap-3">
            <span class="text-xs sm:text-sm text-gray-600 w-full sm:w-auto text-center sm:text-left mb-2 sm:mb-0">Bagikan:</span>
            <div class="flex items-center gap-2">
                <button onclick="share('whatsapp')" class="p-2 sm:p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors" title="WhatsApp">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.525 3.687"/>
                    </svg>
                </button>
                <button onclick="share('telegram')" class="p-2 sm:p-2 text-blue-500 hover:bg-blue-50 rounded-lg transition-colors" title="Telegram">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                    </svg>
                </button>
                <button onclick="share('facebook')" class="p-2 sm:p-2 text-blue-700 hover:bg-blue-50 rounded-lg transition-colors" title="Facebook">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                </button>
                <button onclick="copyLink()" class="p-2 sm:p-2 text-gray-500 hover:bg-gray-50 rounded-lg transition-colors" title="Copy">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Comments Section -->
    <section class="border-t border-gray-200 pt-6 sm:pt-8">
        <h2 class="text-lg sm:text-xl font-bold mb-4 sm:mb-6">Komentar ({{ $post->comments->count() }})</h2>

        @auth
            <form method="POST" action="{{ route('blog.comment.store', $post) }}" class="mb-6 sm:mb-8">
                @csrf
                <textarea name="comment" rows="4" 
                          class="w-full border border-gray-300 rounded-lg p-3 sm:p-4 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none text-sm sm:text-base"
                          placeholder="Tulis komentar Anda..."></textarea>
                <button type="submit" 
                        class="mt-3 sm:mt-4 px-4 sm:px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm sm:text-base">
                    Kirim Komentar
                </button>
            </form>
        @else
            <div class="mb-6 sm:mb-8 p-4 sm:p-6 bg-gradient-to-br from-blue-50 to-indigo-100 rounded-xl border-2 border-dashed border-blue-200">
                <div class="text-center">
                    <div class="w-12 h-12 sm:w-16 sm:h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3 sm:mb-4">
                        <svg class="w-6 h-6 sm:w-8 sm:h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                        </svg>
                    </div>
                    <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-2">Bergabunglah dalam Diskusi!</h3>
                    <p class="text-gray-600 mb-4 text-sm">Login untuk berbagi pendapat tentang artikel ini</p>
                    <div class="flex flex-col gap-3 justify-center">
                        <a href="{{ route('login') }}" 
                           class="inline-flex items-center justify-center px-5 sm:px-6 py-2.5 sm:py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-all duration-200 shadow-sm hover:shadow-md text-sm sm:text-base">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                            </svg>
                            Masuk Sekarang
                        </a>
                        <a href="{{ route('register') }}" 
                           class="inline-flex items-center justify-center px-5 sm:px-6 py-2.5 sm:py-3 bg-white text-blue-600 border border-blue-300 rounded-lg font-medium hover:bg-blue-50 transition-all duration-200 text-sm sm:text-base">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                            Daftar Gratis
                        </a>
                    </div>
                   
                </div>
            </div>
        @endauth

        <!-- Comments List -->
        <div class="space-y-4 sm:space-y-6">
            @forelse($post->comments as $comment)
                <div class="flex space-x-3 sm:space-x-4">
                    <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gray-200 rounded-full flex items-center justify-center flex-shrink-0">
                        <span class="text-xs sm:text-sm font-medium text-gray-600">
                            {{ substr($comment->user->nama_lengkap ?? 'A', 0, 1) }}
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-2 mb-1">
                           <h4 class="font-medium text-gray-900 text-sm sm:text-base">
    {{ \App\Helpers\StringHelper::maskName($comment->user->name ?? '') }}
</h4>


                        </div>
                        <p class="text-gray-700 text-sm sm:text-base break-words">{{ $comment->isi }}</p>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-center py-6 sm:py-8 text-sm sm:text-base">Belum ada komentar. Jadilah yang pertama!</p>
            @endforelse
        </div>
    </section>

    <!-- Related Articles -->
    @if($relatedPosts->count() > 0)
        <section class="mt-12 sm:mt-16 pt-6 sm:pt-8 border-t border-gray-200">
            <h2 class="text-lg sm:text-xl font-bold mb-4 sm:mb-6">Blog Terkait</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                @foreach($relatedPosts->take(4) as $related)
                    <article class="group bg-white rounded-lg border border-gray-100 hover:border-gray-200 overflow-hidden transition-all duration-200 hover:shadow-md">
                        <a href="{{ route('blog.show', $related->slug) }}" class="block">
                            @if($related->thumbnail)
                                <div class="aspect-video overflow-hidden">
                                    <img src="{{ asset('storage/'.$related->thumbnail) }}" 
                                         alt="{{ $related->judul }}" 
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                </div>
                            @else
                                <div class="aspect-video bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                                    <svg class="w-8 h-8 sm:w-12 sm:h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            @endif
                            <div class="p-3 sm:p-4">
                                <h3 class="font-semibold text-gray-900 group-hover:text-blue-600 line-clamp-2 mb-2 leading-snug text-sm sm:text-base">
                                    {{ $related->judul }}
                                </h3>
                                <div class="flex items-center justify-between text-xs sm:text-sm text-gray-500">
                                    <span>{{ $related->created_at->format('d M Y') }}</span>
                                    <span>{{ ceil(str_word_count(strip_tags($related->isi)) / 200) }} min</span>
                                </div>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
</div>

<script>
function share(platform) {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent(document.title);
    
    const urls = {
        whatsapp: `https://wa.me/?text=${title} ${url}`,
        telegram: `https://t.me/share/url?url=${url}&text=${title}`,
        facebook: `https://www.facebook.com/sharer/sharer.php?u=${url}`
    };
    
    if (urls[platform]) {
        window.open(urls[platform], '_blank', 'width=600,height=400');
    }
}

function copyLink() {
    navigator.clipboard.writeText(window.location.href).then(() => {
        const btn = event.target.closest('button');
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
        btn.classList.add('text-green-600', 'bg-green-50');
        
        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.classList.remove('text-green-600', 'bg-green-50');
            btn.classList.add('text-gray-500');
        }, 2000);
    }).catch(() => {
        const textArea = document.createElement('textarea');
        textArea.value = window.location.href;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        alert('Link berhasil disalin!');
    });
}
</script>
@endsection