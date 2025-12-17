@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4">
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-sky-600 via-cyan-600 to-teal-600 shadow-2xl mb-8">
        <div class="absolute inset-0 bg-black/5"></div>
        <div class="relative px-8 py-10 text-center">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-white/20 backdrop-blur rounded-2xl mb-4 shadow-xl">
                <i class="fas fa-truck text-white text-3xl"></i>
            </div>
            <h1 class="text-3xl font-extrabold text-white mb-1">Edit Kendaraan</h1>
            <p class="text-cyan-100">{{ $vehicle->plate }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
        <div class="px-8 py-8">
    <form method="post" action="{{ route('vehicles.update', $vehicle) }}" class="space-y-6">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Plat (unik)</label>
            <input type="text" name="plate" value="{{ old('plate', $vehicle->plate) }}" required
                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500" />
            @error('plate')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Nama</label>
            <input type="text" name="name" value="{{ old('name', $vehicle->name) }}"
                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500" />
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Aktif</label>
            <select name="active" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                <option value="1" @selected(old('active', (string)(int)$vehicle->active)=='1')>Ya</option>
                <option value="0" @selected(old('active', (string)(int)$vehicle->active)=='0')>Tidak</option>
            </select>
        </div>
        <div class="flex flex-col sm:flex-row gap-4 pt-4">
            <button type="submit" class="flex-1 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white px-8 py-4 rounded-xl font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center gap-2">
                <i class="fas fa-save"></i> Simpan Perubahan
            </button>
            <a href="{{ route('vehicles.index') }}" class="flex-1 bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 hover:text-gray-900 px-8 py-4 rounded-xl font-bold shadow hover:shadow-lg transition-all duration-200 flex items-center justify-center gap-2">
                <i class="fas fa-times"></i> Batal
            </a>
        </div>
    </form>
        </div>
    </div>
</div>
@endsection
