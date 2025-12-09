@extends('admin.layouts.app')

@section('title', 'Detail Blog')
@section('page-title', 'Detail Blog')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">{{ $post->judul }}</h1>
        <div class="flex space-x-2">
            <a href="{{ route('admin.blog.edit', $post) }}" 
               class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                <i class="fas fa-edit mr-2"></i>Edit
            </a>
            <form method="POST" action="{{ route('admin.blog.destroy', $post) }}" onsubmit="return confirm('Yakin ingin menghapus Blog ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                    <i class="fas fa-trash mr-2"></i>Hapus
                </button>
            </form>
        </div>
    </div>

    <!-- Info -->
    <div class="bg-white shadow rounded-lg p-6 space-y-4">
        <!-- Thumbnail -->
        @if($post->thumbnail)
            <img src="{{ asset('storage/' . $post->thumbnail) }}" 
                 class="w-full max-h-96 object-cover rounded-lg" 
                 alt="{{ $post->judul }}">
        @endif

        <!-- Meta -->
        <div class="flex flex-wrap items-center text-sm text-gray-600 space-x-4">
            <div><i class="fas fa-user mr-1"></i>{{ $post->author->name ?? 'Unknown' }}</div>
            <div><i class="fas fa-calendar mr-1"></i>{{ $post->published_at?->format('d M Y') }}</div>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                @if($post->status === 'published') 
                @elseif($post->status === 'draft') 
                @else bg-gray-100 text-gray-800 @endif">
                {{ ucfirst($post->status) }}
            </span>
        </div>

        <!-- Excerpt -->
        @if($post->excerpt)
            <p class="text-gray-600 italic">{{ $post->excerpt }}</p>
        @endif

        <!-- Content -->
        <div class="prose max-w-none">
            {!! $post->isi !!}
        </div>
    </div>

    <!-- Back Button -->
    <div>
        <a href="{{ route('admin.blog.index') }}" 
           class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>
</div>
@endsection
