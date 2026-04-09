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

    <!-- DataTable -->
    <div class="bg-white shadow rounded-lg overflow-hidden p-4">
        <div class="overflow-x-auto">
            <table id="blog-table" class="min-w-full text-sm" style="width:100%">
                <thead>
                    <tr>
                        {{-- col 0: dtr-control expand --}}
                        <th></th>
                        {{-- col 1 --}}<th class="px-4 py-3 text-left font-semibold text-gray-600">Artikel</th>
                        {{-- col 2 --}}<th class="px-4 py-3 text-left font-semibold text-gray-600">Penulis</th>
                        {{-- col 3 --}}<th class="px-4 py-3 text-center font-semibold text-gray-600">Views</th>
                        {{-- col 4 --}}<th class="px-4 py-3 text-left font-semibold text-gray-600">Tanggal</th>
                        {{-- col 5 --}}<th class="px-4 py-3 text-center font-semibold text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($posts as $post)
                    <tr class="hover:bg-gray-50 transition">
                        {{-- col 0: expand control --}}
                        <td></td>

                        {{-- col 1: Artikel --}}
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if($post->thumbnail)
                                    <img class="h-12 w-16 rounded-lg object-cover flex-shrink-0"
                                         src="{{ asset('storage/'.$post->thumbnail) }}"
                                         alt="{{ $post->judul }}">
                                @else
                                    <div class="h-12 w-16 rounded-lg bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-image text-gray-500"></i>
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <a href="{{ route('admin.blog.show', $post) }}"
                                       class="text-blue-600 font-medium hover:underline line-clamp-2 block">
                                        {{ $post->judul }}
                                    </a>
                                    @if($post->excerpt)
                                        <p class="text-xs text-gray-400 line-clamp-1 mt-0.5">{{ \Illuminate\Support\Str::limit($post->excerpt, 70) }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- col 2: Penulis --}}
                        <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                            <i class="fas fa-user text-xs mr-1 text-gray-400"></i>{{ $post->author->name ?? 'Admin' }}
                        </td>

                        {{-- col 3: Views --}}
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center gap-1 text-gray-600">
                                <i class="fas fa-eye text-xs text-blue-400"></i> {{ number_format($post->views_count ?? 0) }}
                            </span>
                        </td>

                        {{-- col 4: Tanggal --}}
                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap text-xs">
                            {{ $post->created_at->format('d M Y') }}
                        </td>

                        {{-- col 5: Aksi --}}
                        <td class="px-4 py-3 text-center whitespace-nowrap space-x-1">
                            <a href="{{ route('admin.blog.show', $post) }}"
                               class="inline-flex items-center px-2 py-1 bg-indigo-50 text-indigo-600 rounded hover:bg-indigo-100 text-xs" title="Lihat">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.blog.edit', $post) }}"
                               class="inline-flex items-center px-2 py-1 bg-green-50 text-green-600 rounded hover:bg-green-100 text-xs" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.blog.destroy', $post) }}"
                                  class="inline" onsubmit="return confirm('Yakin ingin menghapus Blog ini?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center px-2 py-1 bg-red-50 text-red-600 rounded hover:bg-red-100 text-xs" title="Hapus">
                                    <i class="fas fa-trash"></i>
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
    $('#blog-table').DataTable({
        responsive: {
            details: {
                type  : 'column',
                target: 0
            }
        },
        language : { url: 'https://cdn.datatables.net/plug-ins/2.0.8/i18n/id.json' },
        pageLength: 10,
        order    : [[4, 'desc']],          // order by Tanggal col (idx 4)
        columnDefs: [
            // col 0 = expand toggle
            { className: 'dtr-control', orderable: false, targets: 0, width: '30px' },
            // col 1 = Artikel — disable sort (contains HTML/thumbnail)
            { orderable: false, targets: 1 },
            // col 5 = Aksi — not sortable
            { orderable: false, targets: 5 }
        ],

        initComplete: function () {
            const api = this.api();
            const $filterRow = $('<tr class="dt-filter-row">').appendTo($('#blog-table thead'));

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
