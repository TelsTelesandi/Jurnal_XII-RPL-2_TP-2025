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
                  text-white font-semibold px-4 py-2 rounded-lg shadow-sm text-sm transition">
           <i class="fa fa-plus mr-2"></i> Tambah User
        </a>
    </div>

    <!-- Statistik -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 transition hover:shadow-md">
            <span class="text-xs text-gray-500 font-medium uppercase tracking-wider block mb-1">Total Users</span>
            <span class="text-2xl font-bold text-blue-600">{{ number_format($totalUsers ?? 0) }}</span>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 transition hover:shadow-md">
            <span class="text-xs text-gray-500 font-medium uppercase tracking-wider block mb-1">Terverifikasi</span>
            <span class="text-2xl font-bold text-green-600">{{ number_format($activeUsers ?? 0) }}</span>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 transition hover:shadow-md">
            <span class="text-xs text-gray-500 font-medium uppercase tracking-wider block mb-1">Belum Verifikasi</span>
            <span class="text-2xl font-bold text-yellow-500">{{ number_format($pendingUsers ?? 0) }}</span>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 transition hover:shadow-md">
            <span class="text-xs text-gray-500 font-medium uppercase tracking-wider block mb-1">Akun Diblokir</span>
            <span class="text-2xl font-bold text-red-600">{{ number_format($bannedUsers ?? 0) }}</span>
        </div>
    </div>

    <!-- DataTable -->
    <div class="bg-white shadow-sm border border-gray-100 rounded-xl overflow-hidden p-4 sm:p-6">
        <div class="overflow-x-auto">
            <table id="users-table" class="min-w-full text-sm" style="width:100%">
                <thead>
                    <tr>
                        {{-- col 0: dtr-control (expand) --}}
                        <th></th>
                        {{-- col 1 --}}<th class="px-4 py-3 text-left font-semibold text-gray-600">Nama</th>
                        {{-- col 2 --}}<th class="px-4 py-3 text-left font-semibold text-gray-600">Email</th>
                        {{-- col 3 --}}<th class="px-4 py-3 text-left font-semibold text-gray-600">Role</th>
                        {{-- col 4 --}}<th class="px-4 py-3 text-left font-semibold text-gray-600">Status</th>
                        {{-- col 5 --}}<th class="px-4 py-3 text-center font-semibold text-gray-600">Laporan</th>
                        {{-- col 6 --}}<th class="px-4 py-3 text-right font-semibold text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($users as $user)
                    <tr class="hover:bg-gray-50 transition">
                        {{-- col 0: expand control (empty cell, DataTables puts ▶ here) --}}
                        <td></td>
                        {{-- col 1: Nama --}}
                        <td class="px-4 py-3 align-middle font-medium text-gray-800">
                            <div class="flex items-center gap-3">
                                <img src="{{ $user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name) }}" 
                                     alt="{{ $user->name }}" 
                                     class="h-10 w-10 rounded-full border shadow-sm flex-shrink-0">
                                <span class="truncate">{{ $user->name }}</span>
                            </div>
                        </td>
                        {{-- col 2: Email --}}
                        <td class="px-4 py-3 align-middle text-gray-600">{{ $user->email }}</td>
                        {{-- col 3: Role --}}
                        <td class="px-4 py-3 align-middle">
                            <span class="px-2 py-1 rounded-md text-xs font-medium 
                                {{ $user->role_id == 1 ? 'bg-purple-100 text-purple-700' : ($user->role_id == 3 ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-700') }}">
                                @if($user->role_id == 1) Admin @elseif($user->role_id == 3) Moderator @else User @endif
                            </span>
                        </td>
                        {{-- col 4: Status --}}
                        <td class="px-4 py-3 align-middle">
                            <div class="flex flex-wrap items-center gap-1.5">
                                @if($user->email_verified_at)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-700 uppercase">Aktif</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-yellow-100 text-yellow-700 uppercase">Pending</span>
                                @endif
                                
                                @if($user->activeBan)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-700 border border-red-200 uppercase" title="User ini sedang diblokir">
                                        ⚠️ Banned
                                    </span>
                                @endif
                            </div>
                        </td>
                        {{-- col 5: Laporan --}}
                        <td class="px-4 py-3 align-middle text-center">
                            @php $repCount = $user->getTotalReportsCount(); @endphp
                            @if($repCount > 0)
                                <button type="button" onclick="showReports({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                        class="px-2.5 py-1 bg-red-50 text-red-600 font-bold text-xs rounded-lg border border-red-100 hover:bg-red-600 hover:text-white transition">
                                    {{ $repCount }} Laporan
                                </button>
                            @else
                                <span class="text-xs text-gray-400 font-medium">Clear</span>
                            @endif
                        </td>
                        {{-- col 6: Aksi --}}
                        <td class="px-4 py-3 align-middle text-right space-x-1 whitespace-nowrap">
                            @if($user->activeBan)
                                <form action="{{ route('admin.users.unban', $user->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" 
                                            onclick="return confirm('Yakin ingin membuka blokir (unban) user ini?')" 
                                            class="inline-flex items-center px-3 py-1 bg-green-500 text-white text-xs font-semibold rounded-lg hover:bg-green-600 shadow-sm transition">
                                        <i class="fa-solid fa-unlock mr-1"></i> Buka Ban
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('admin.users.edit', $user->id) }}" 
                                   class="inline-flex items-center px-3 py-1 bg-blue-500 text-white text-xs font-semibold rounded-lg hover:bg-blue-600 shadow-sm transition">
                                   <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                </a>
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            onclick="return confirm('Yakin ingin menghapus user ini?')" 
                                            class="inline-flex items-center px-3 py-1 bg-red-500 text-white text-xs font-semibold rounded-lg hover:bg-red-600 shadow-sm transition">
                                        <i class="fa-solid fa-trash-can mr-1"></i> Hapus
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Laporan -->
<div id="reports-modal" class="fixed inset-0 bg-black/60 hidden z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[85vh] flex flex-col overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-lg text-gray-900">Rincian Laporan</h3>
                <p class="text-xs text-gray-500" id="reports-user-name"></p>
            </div>
            <button onclick="document.getElementById('reports-modal').classList.add('hidden')" 
                    class="h-8 w-8 rounded-full bg-white border flex items-center justify-center text-gray-400 hover:text-gray-600 shadow-sm transition">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-6 overflow-y-auto flex-1 bg-white">
            <div id="reports-loading" class="flex flex-col items-center justify-center py-10 gap-3">
                <div class="h-8 w-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                <p class="text-sm text-gray-500">Memuat rincian laporan...</p>
            </div>
            <div id="reports-empty" class="hidden py-10 text-center">
                <div class="h-16 w-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="far fa-folder-open text-gray-300 text-2xl"></i>
                </div>
                <p class="text-sm text-gray-500">Tidak ada rincian laporan ditemukan.</p>
            </div>
            <div id="reports-list" class="space-y-4 hidden"></div>
        </div>
        <div class="px-6 py-4 bg-gray-50 border-t flex justify-end">
             <button onclick="document.getElementById('reports-modal').classList.add('hidden')" 
                    class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                Tutup
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    async function showReports(userId, userName) {
        document.getElementById('reports-modal').classList.remove('hidden');
        document.getElementById('reports-user-name').textContent = 'User: ' + userName;
        
        const loadingE = document.getElementById('reports-loading');
        const listE    = document.getElementById('reports-list');
        const emptyE   = document.getElementById('reports-empty');

        loadingE.classList.remove('hidden');
        listE.classList.add('hidden');
        listE.innerHTML = '';
        emptyE.classList.add('hidden');

        try {
            const res  = await fetch(`/admin/users/${userId}/reports`);
            const data = await res.json();
            loadingE.classList.add('hidden');
            
            if (!data.data || data.data.length === 0) {
                emptyE.classList.remove('hidden');
                return;
            }
            
            listE.classList.remove('hidden');
            listE.innerHTML = data.data.map(r => `
                <div class="p-4 border border-red-100 rounded-xl bg-red-50/30">
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider bg-red-100 text-red-700 px-2 py-0.5 rounded-full border border-red-200">${r.reason}</span>
                        <span class="text-[10px] text-gray-400 font-medium">${new Date(r.created_at).toLocaleString('id-ID')}</span>
                    </div>
                    <div class="text-sm">
                        <span class="text-gray-500 italic">" ${r.message || '(tanpa pesan)'} "</span>
                    </div>
                    <div class="mt-2 pt-2 border-t border-red-100/50 flex justify-between items-center text-[10px]">
                        <span class="text-gray-600 font-semibold">Pelapor: ${r.reporter}</span>
                        ${r.notes ? `<span class="text-gray-500 italic">Catatan: ${r.notes}</span>` : ''}
                    </div>
                </div>
            `).join('');

        } catch (e) {
            loadingE.innerHTML = '<p class="text-red-500">Gagal memuat laporan. Silakan coba lagi.</p>';
        }
    }

    $(document).ready(function () {
        const table = $('#users-table').DataTable({
            responsive: {
                details: {
                    type   : 'column',
                    target : 0
                }
            },
            language : { url: 'https://cdn.datatables.net/plug-ins/2.0.8/i18n/id.json' },
            pageLength: 10,
            order    : [[1, 'asc']],
            columnDefs: [
                { className: 'dtr-control', orderable: false, targets: 0, width: '30px' },
                { orderable: false, targets: 5 },
                { orderable: false, targets: 6 }
            ],
            initComplete: function () {
                const api = this.api();
                const $filterRow = $('<tr class="dt-filter-row">').appendTo($('#users-table thead'));

                api.columns().every(function (idx) {
                    const $th = $('<th>').appendTo($filterRow);
                    if (idx === 0 || idx === 6) return;

                    if (idx === 3) {
                        $('<select><option value="">Semua Role</option><option>Admin</option><option>Moderator</option><option>User</option></select>')
                            .appendTo($th)
                            .on('change', function () { api.column(idx).search(this.value).draw(); });
                    } else if (idx === 4) {
                        $('<select><option value="">Semua Status</option><option>Aktif</option><option>Pending</option><option>Banned</option></select>')
                            .appendTo($th)
                            .on('change', function () { api.column(idx).search(this.value).draw(); });
                    } else {
                        const title = api.column(idx).header().textContent.trim();
                        $('<input type="text" class="w-full border rounded px-2 py-1 text-xs">')
                            .attr('placeholder', 'Cari ' + title + '…')
                            .appendTo($th)
                            .on('keyup change clear', function () {
                                if (api.column(idx).search() !== this.value) {
                                    api.column(idx).search(this.value).draw();
                                }
                            });
                    }
                });
            }
        });
    });
</script>
@endpush
