@extends('layouts.app')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-sky-600 via-cyan-600 to-teal-600 shadow-2xl">
        <div class="absolute inset-0 bg-black/5"></div>
        <div class="relative px-8 py-10">
            <div class="flex items-center gap-3 text-white">
                <div class="bg-white/20 backdrop-blur p-3 rounded-xl shadow">
                    <i class="fas fa-truck text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-extrabold">Vehicles</h1>
                    <p class="text-cyan-100">Kelola kendaraan dan pantau kendaraan yang dipakai driver</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions / Search -->
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6">
        <div class="flex flex-col md:flex-row md:items-center gap-4 justify-between">
            <form method="get" action="{{ route('vehicles.index') }}" class="flex-1">
                <div class="relative max-w-lg">
                    <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Cari plat atau nama kendaraan..." 
                           class="w-full pl-11 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500"/>
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                </div>
            </form>
            <div class="flex items-center gap-3">
                <a href="{{ route('vehicles.create') }}" 
                   class="inline-flex items-center gap-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white px-5 py-3 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all">
                    <i class="fas fa-plus"></i> Tambah Kendaraan
                </a>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
        <div class="bg-gradient-to-r from-sky-50 to-cyan-50 px-6 py-4 border-b border-sky-100">
            <h2 class="text-lg font-bold text-gray-900 flex items-center">
                <i class="fas fa-table mr-2 text-sky-600"></i> Daftar Kendaraan
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="text-left text-sm text-gray-600 border-b">
                        <th class="px-6 py-3 font-semibold">Plat</th>
                        <th class="px-6 py-3 font-semibold">Nama</th>
                        <th class="px-6 py-3 font-semibold">Kapasitas</th>
                        <th class="px-6 py-3 font-semibold">Status</th>
                        <th class="px-6 py-3 font-semibold">Dipakai Oleh</th>
                        <th class="px-6 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @forelse($vehicles as $v)
                    @php($current = $v->assignments->first())
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-6 py-4 font-semibold text-gray-900">{{ $v->plate }}</td>
                        <td class="px-6 py-4 text-gray-800">{{ $v->name }}</td>
                        <td class="px-6 py-4 text-gray-700">{{ $v->capacity_weight ?? 0 }} kg • {{ $v->capacity_volume ?? 0 }} m³</td>
                        <td class="px-6 py-4">
                            @if($v->active)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-green-100 text-green-800 text-xs font-bold">Aktif</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-gray-200 text-gray-700 text-xs font-bold">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @if($current)
                                <div class="text-gray-900 font-medium">{{ $current->driver?->name ?? '-' }}</div>
                                <div class="text-gray-500">Status: {{ ucfirst($current->status) }}</div>
                            @else
                                <span class="text-gray-500">Tidak sedang dipakai</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2 justify-end">
                                @if(auth()->check() && auth()->user()->role === 'Admin')
                                <details class="relative">
                                    <summary class="list-none inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-sky-200 text-sky-700 hover:bg-sky-50 font-semibold text-sm cursor-pointer select-none">
                                        <i class="fas fa-user-plus"></i> Assign
                                    </summary>
                                    <div class="absolute right-0 mt-2 w-[360px] bg-white rounded-xl shadow-xl border border-gray-100 p-4 z-10">
                                        <form method="post" action="{{ route('assignments.store') }}" class="space-y-3">
                                            @csrf
                                            <input type="hidden" name="vehicle_id" value="{{ $v->id }}" />
                                            <div>
                                                <label class="block text-xs font-semibold text-gray-600 mb-1">Driver</label>
                                                <select name="driver_id" class="w-full px-3 py-2 border rounded-lg" required>
                                                    <option value="">-- Pilih Driver --</option>
                                                    @foreach(($drivers ?? []) as $d)
                                                        <option value="{{ $d->id }}">{{ $d->name }} ({{ $d->email }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="flex gap-2">
                                                <button type="submit" class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg bg-sky-600 hover:bg-sky-700 text-white font-semibold text-sm">
                                                    <i class="fas fa-paper-plane"></i> Assign
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </details>
                                @endif
                                <a href="{{ route('vehicles.edit', $v) }}" 
                                   class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-50 font-semibold text-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form method="post" action="{{ route('vehicles.destroy', $v) }}" onsubmit="return confirm('Hapus kendaraan ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-red-200 text-red-700 hover:bg-red-50 font-semibold text-sm">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-500">Belum ada kendaraan.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t">
            {{ $vehicles->links() }}
        </div>
    </div>
</div>
@endsection
