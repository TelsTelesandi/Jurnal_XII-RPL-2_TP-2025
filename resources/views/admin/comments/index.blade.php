@extends('admin.layouts.app')

@section('title', 'Kelola Komentar')
@section('page-title', 'Kelola Komentar')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between md:items-center gap-4">
        <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
            <i class="fas fa-comments text-blue-600"></i> Kelola Komentar
        </h2>
        @if($comments->total() > 0)
        <form action="{{ route('admin.comments.clear') }}" method="POST" onsubmit="return confirm('APAKAH ANDA YAKIN INGIN MENGHAPUS SEMUA KOMENTAR? Tindakan ini tidak dapat dibatalkan!');  ">
            @csrf
            <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-red-600 text-white font-semibold rounded-lg shadow-sm hover:bg-red-700 transition flex items-center justify-center gap-2">
                <i class="fas fa-trash-alt"></i> Hapus Semua Komentar
            </button>
        </form>
        @endif
    </div>

    <!-- Statistik -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4 transition hover:shadow-md">
            <div class="h-12 w-12 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xl shadow-inner">
                <i class="fas fa-comment-dots"></i>
            </div>
            <div>
                <span class="text-xs text-gray-500 font-medium uppercase tracking-wider block">Total Komentar</span>
                <span class="text-2xl font-bold text-gray-900">{{ number_format($comments->total()) }}</span>
            </div>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4 transition hover:shadow-md">
            <div class="h-12 w-12 rounded-lg bg-green-50 text-green-600 flex items-center justify-center text-xl shadow-inner">
                <i class="fas fa-clock"></i>
            </div>
            <div>
                <span class="text-xs text-gray-500 font-medium uppercase tracking-wider block">Komentar Hari Ini</span>
                <span class="text-2xl font-bold text-gray-900">{{ number_format(\App\Models\BlogComment::whereDate('created_at', today())->count()) }}</span>
            </div>
        </div>
    </div>

    <!-- DataTable -->
    <div class="bg-white shadow-sm border border-gray-100 rounded-xl overflow-hidden p-4 sm:p-6">
        <div class="overflow-x-auto">
            <table id="comments-table" class="min-w-full text-sm" style="width:100%">
                <thead>
                    <tr>
                        {{-- col 0: dtr-control expand --}}
                        <th></th>
                        {{-- col 1 --}}<th class="px-4 py-3 text-left font-semibold text-gray-600">Pengirim</th>
                        {{-- col 2 --}}<th class="px-4 py-3 text-left font-semibold text-gray-600">Isi Komentar</th>
                        {{-- col 3 --}}<th class="px-4 py-3 text-left font-semibold text-gray-600">Artikel</th>
                        {{-- col 4 --}}<th class="px-4 py-3 text-left font-semibold text-gray-600">Waktu</th>
                        {{-- col 5 --}}<th class="px-4 py-3 text-right font-semibold text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($comments as $comment)
                    <tr class="hover:bg-gray-50 transition">
                        {{-- col 0: expand control --}}
                        <td></td>

                        {{-- col 1: Pengirim --}}
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-full bg-indigo-50 border border-indigo-100 flex items-center justify-center text-sm font-bold text-indigo-600 shadow-sm flex-shrink-0">
                                    {{ strtoupper(substr($comment->user->name ?? 'A', 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-gray-900 truncate">{{ $comment->user->name ?? 'Anonim' }}</div>
                                    <div class="text-[10px] text-gray-400 font-medium truncate">{{ $comment->user->email ?? '-' }}</div>
                                </div>
                            </div>
                        </td>

                        {{-- col 2: Isi --}}
                        <td class="px-4 py-3">
                            <div class="text-gray-700 max-w-sm whitespace-normal leading-relaxed">
                                {{ $comment->isi }}
                            </div>
                            @if($comment->reports_count > 0)
                                <div class="mt-2 text-[10px] font-bold uppercase tracking-tighter text-red-600 bg-red-50 inline-flex items-center px-2 py-0.5 rounded-full border border-red-100"
                                     title="Komentar ini telah dilaporkan {{ $comment->reports_count }} kali">
                                    <i class="fas fa-flag-checkered mr-1"></i> {{ $comment->reports_count }} Laporan
                                </div>
                            @endif
                        </td>

                        {{-- col 3: Artikel --}}
                        <td class="px-4 py-3">
                            <a href="{{ route('blog.show', $comment->post->slug) }}" target="_blank"
                               class="text-blue-600 hover:text-blue-800 font-medium hover:underline line-clamp-2 transition text-xs">
                                {{ $comment->post->judul }}
                                <i class="fas fa-external-link-alt text-[10px] ml-1 opacity-50"></i>
                            </a>
                        </td>

                        {{-- col 4: Waktu --}}
                        <td class="px-4 py-3">
                            <div class="text-[11px] text-gray-500 font-medium whitespace-nowrap">
                                <span class="block text-gray-900">{{ $comment->created_at->format('d M Y') }}</span>
                                <span class="text-gray-400">{{ $comment->created_at->format('H:i') }}</span>
                            </div>
                        </td>

                        {{-- col 5: Aksi --}}
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <form action="{{ route('admin.comments.destroy', $comment->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Hapus komentar ini?')"
                                        class="inline-flex items-center px-3 py-1.5 bg-red-50 text-red-600 text-[11px] font-bold rounded-lg border border-red-100 hover:bg-red-600 hover:text-white shadow-sm transition uppercase">
                                    <i class="fas fa-trash-alt mr-1.5"></i> Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    $('#comments-table').DataTable({
        responsive: {
            details: {
                type  : 'column',
                target: 0
            }
        },
        language : { url: 'https://cdn.datatables.net/plug-ins/2.0.8/i18n/id.json' },
        pageLength: 10,
        order    : [[4, 'desc']],          // order by Waktu col (idx 4)
        columnDefs: [
            // col 0 = expand toggle
            { className: 'dtr-control', orderable: false, targets: 0, width: '30px' },
            // col 5 = Aksi — not sortable
            { orderable: false, targets: 5 }
        ],

        initComplete: function () {
            const api = this.api();
            const $filterRow = $('<tr class="dt-filter-row">').appendTo($('#comments-table thead'));

            api.columns().every(function (idx) {
                const $th = $('<th>').appendTo($filterRow);

                // no filter for expand col or Aksi col
                if (idx === 0 || idx === 5) return;

                const title = api.column(idx).header().textContent.trim();
                $('<input type="text">')
                    .attr('placeholder', 'Cari ' + title + '…')
                    .appendTo($th)
                    .on('keyup change clear', function () {
                        if (api.column(idx).search() !== this.value) {
                            api.column(idx).search(this.value).draw();
                        }
                    });
            });
        }
    });
});
</script>
@endpush
