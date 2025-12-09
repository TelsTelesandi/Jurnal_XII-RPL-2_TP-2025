@extends('admin.layouts.app')

@section('title', 'Edit Artikel')
@section('page-title', 'Edit Artikel')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white shadow-xl rounded-2xl p-6 sm:p-8">
        <form action="{{ route('admin.blog.update', $post) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Judul -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Judul</label>
                <input type="text" name="judul" value="{{ old('judul', $post->judul) }}" 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm sm:text-base transition" 
                       placeholder="Masukkan judul artikel" required>
                @error('judul') 
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p> 
                @enderror
            </div>

            <!-- Excerpt -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Excerpt (Ringkasan)</label>
                <textarea name="excerpt" rows="3"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm sm:text-base transition resize-y" 
                          placeholder="Tulis ringkasan artikel...">{{ old('excerpt', $post->excerpt) }}</textarea>
                @error('excerpt') 
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p> 
                @enderror
            </div>

            <!-- Isi -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Isi Artikel</label>
                <textarea name="isi" rows="8"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm sm:text-base transition resize-y" 
                          placeholder="Tulis isi artikel di sini..." required>{{ old('isi', $post->isi) }}</textarea>
                @error('isi') 
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p> 
                @enderror
            </div>

            <!-- Thumbnail -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Thumbnail</label>
                @if($post->thumbnail)
                    <div class="mb-3">
                        <img src="{{ asset('storage/' . $post->thumbnail) }}" 
                             class="h-32 rounded-lg object-contain bg-gray-100" 
                             alt="{{ $post->judul }}">
                    </div>
                @endif
                <input type="file" name="thumbnail" accept="image/*" 
                       class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 
                              file:rounded-lg file:border-0 
                              file:text-sm file:font-semibold 
                              file:bg-blue-50 file:text-blue-600 
                              hover:file:bg-blue-100 cursor-pointer">
                @error('thumbnail') 
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p> 
                @enderror
            </div>

         

            <!-- Actions -->
            <div class="pt-4 flex items-center gap-3">
                <button type="submit" 
                        class="px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-semibold rounded-lg shadow-lg hover:from-green-700 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200">
                    Edit Artikel
                </button>

                <a href="{{ route('admin.blog.index') }}" 
                   class="px-6 py-3 bg-gray-500 text-white font-semibold rounded-lg shadow hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 transition-all duration-200">
                    Kembali
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
