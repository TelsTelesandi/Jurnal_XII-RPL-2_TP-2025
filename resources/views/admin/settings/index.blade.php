@extends('admin.layouts.app')

@section('title', 'Admin Settings')
@section('page-title', 'Admin Settings')

@section('content')
<div class="space-y-6">
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-lg font-semibold mb-4">Pengaturan Umum</h2>

        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        {{-- pakai resource: settings.update BUTUH param, kita kasih dummy id=1 --}}
      <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')


            <div>
                <label class="block text-sm font-medium text-gray-700">Nama Website</label>
                <input type="text" name="site_name" value="{{ $settings['site_name'] ?? '' }}"
                    class="mt-1 block w-full border rounded px-3 py-2">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Logo Website</label>
                <input type="file" name="site_logo" class="mt-1 block w-full border rounded px-3 py-2">
                @if(!empty($settings['site_logo']))
                    <img src="{{ asset('storage/'.$settings['site_logo']) }}" alt="Logo" class="h-12 mt-2">
                @endif
            </div>

          <div class="flex items-center">
    <input type="hidden" name="maintenance_mode" value="0"> {{-- default kalau unchecked --}}
    <input type="checkbox" name="maintenance_mode" value="1"
        @if(!empty($settings['maintenance_mode']) && $settings['maintenance_mode'] == 1) checked @endif
        class="mr-2">
    <label class="text-sm text-gray-700">Aktifkan Maintenance Mode</label>
</div>

            <div>
                <button type="submit"
                    class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
