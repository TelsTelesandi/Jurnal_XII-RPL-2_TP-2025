@extends('admin.layouts.app')

@section('title', 'Buat Artikel Baru')
@section('page-title', 'Buat Artikel Baru')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white shadow-xl rounded-2xl p-6 sm:p-8">
        <form action="{{ route('admin.blog.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" onsubmit="return validateForm(event)">
            @csrf

            <!-- Judul -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Judul</label>
                <input type="text" name="judul" value="{{ old('judul') }}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm sm:text-base transition"
                       placeholder="Masukkan judul artikel" required>
                @error('judul')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Excerpt -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Excerpt (Ringkasan)</label>
                <textarea name="excerpt" id="excerpt" rows="3"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm sm:text-base transition resize-y"
                          placeholder="Tulis ringkasan artikel (tanpa spasi di awal/akhir)..."
                          oninput="checkExcerptRealtime(this)">{{ old('excerpt') }}</textarea>

                <!-- Kotak peringatan real-time -->
                <div id="ex-warn" style="display:none;margin-top:6px;padding:10px 14px;background:#fffbeb;border:1px solid #f59e0b;border-radius:6px;font-size:12px;color:#92400e;font-weight:600"></div>

                <p class="text-gray-400 text-xs mt-1">Maks. 160 karakter. Tidak boleh ada spasi di awal/akhir atau spasi ganda.</p>
                @error('excerpt')
                    <p class="text-red-500 text-sm mt-1 font-semibold">⚠ {{ $message }}</p>
                @enderror
            </div>

            <!-- Isi -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Isi Artikel</label>
                <div class="document-editor rounded-lg overflow-hidden border border-gray-300">
                    <div class="document-editor__toolbar bg-gray-50 border-b border-gray-200" id="toolbar-container"></div>
                    <div class="document-editor__editable-container bg-gray-100 p-4 flex justify-center overflow-y-auto max-h-[600px]">
                        <div class="document-editor__editable bg-white shadow-sm border border-gray-200 w-[21cm] min-h-[29.7cm] p-4 sm:p-8" id="editor">{!! old('isi') !!}</div>
                    </div>
                </div>
                <textarea name="isi" id="isi_hidden" class="hidden" required>{{ old('isi') }}</textarea>
                @error('isi')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Thumbnail -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Thumbnail</label>
                <input type="file" name="thumbnail" accept="image/*"
                       class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100 cursor-pointer">
                @error('thumbnail')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Actions -->
            <div class="pt-4 flex items-center gap-3">
                <button type="submit"
                        class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-lg shadow-lg hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                    Simpan Artikel
                </button>
                <a href="{{ route('admin.blog.index') }}"
                   class="px-6 py-3 bg-gray-500 text-white font-semibold rounded-lg shadow hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 transition-all duration-200">
                    Kembali
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Script validasi Pop-up --}}
<script>
function checkExcerptRealtime(el) {
    var s = el.value;
    var msg = '';
    if (s.length > 160) msg = '⚠ Terlalu panjang (' + s.length + '/160)';
    else if (/\s/.test(s)) msg = '⚠ Tidak boleh ada spasi sama sekali';
    
    var box = document.getElementById('ex-warn');
    if (box) { box.textContent = msg; box.style.display = msg ? 'block' : 'none'; }
    el.style.borderColor = msg ? '#f59e0b' : '';
}

function validateForm(event) {
    var el = document.getElementById('excerpt');
    if (!el || !el.value) return true; // kalau kosong biarkan karena opsional
    
    var s = el.value;
    var hasSpace = /\s/.test(s);
    var tooLong = s.length > 160;

    if (hasSpace || tooLong) {
        event.preventDefault(); // hentikan form terkirim
        
        var peringatan = "⚠️ GAGAL MENYIMPAN ARTIKEL ⚠️\n\n";
        peringatan += "Sistem mendeteksi kesalahan penulisan pada Excerpt (Ringkasan).\n\n";
        peringatan += "PANDUAN PENGGUNAAN EXCERPT YANG BENAR:\n";
        peringatan += "1. TIDAK BOLEH MENGANDUNG SPASI SAMA SEKALI di dalam kotak tulisan.\n";
        peringatan += "2. Tulisan harus bersambung sempurna (seperti satu kata / tidak boleh dienter).\n";
        peringatan += "3. Panjang tulisan maksimal 160 karakter.\n\n";
        peringatan += "Silakan hapus SEMUA spasi atau jarak di ringkasan artikel Anda!";
        
        alert(peringatan); // memunculkan pop-up browser
        
        el.focus();
        return false;
    }
    return true;
}

// Cek data awal
(function(){ var el = document.getElementById('excerpt'); if (el && el.value) checkExcerptRealtime(el); })();
</script>
@endsection

@push('styles')
<style>
    .document-editor__toolbar .ck-toolbar { border: 0 !important; border-radius: 0 !important; background: transparent !important; }
    .ck-editor__editable:focus { outline: none !important; border-color: #3b82f6 !important; box-shadow: 0 0 0 2px rgba(59,130,246,0.2) !important; }
    .ck-editor__editable ul { list-style-type: disc !important; padding-left: 2rem !important; margin-bottom: 1rem; }
    .ck-editor__editable ol { list-style-type: decimal !important; padding-left: 2rem !important; margin-bottom: 1rem; }
    .ck-editor__editable li { margin-bottom: 0.25rem; }
    .ck-editor__editable p  { margin-bottom: 1rem; }
    .ck-editor__editable h2 { font-size: 1.5rem; font-weight: bold; margin-top: 1.5rem; margin-bottom: 0.5rem; }
    .ck-editor__editable h3 { font-size: 1.25rem; font-weight: bold; margin-top: 1.25rem; margin-bottom: 0.5rem; }
    .ck-editor__editable blockquote { border-left: 4px solid #d1d5db; padding-left: 1rem; color: #6b7280; font-style: italic; margin-bottom: 1rem; }
    .ck-editor__editable a { color: #2563eb; text-decoration: underline; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/decoupled-document/ckeditor.js"></script>
<script>
    DecoupledEditor.create(document.querySelector('#editor'), {
        toolbar: ['heading','|','fontfamily','fontsize','fontColor','fontBackgroundColor','|','bold','italic','underline','strikethrough','|','alignment','|','numberedList','bulletedList','|','outdent','indent','|','link','blockQuote','insertTable','|','undo','redo']
    }).then(function(editor) {
        document.querySelector('#toolbar-container').appendChild(editor.ui.view.toolbar.element);
        var ta = document.querySelector('#isi_hidden');
        editor.model.document.on('change:data', function() { ta.value = editor.getData(); });
    }).catch(function(e) { console.error(e); });
</script>
@endpush
