@extends('layout.app')

@section('content')
<div class="max-w-4xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
    <h2 class="text-3xl font-bold text-gray-800 mb-8">Pengaturan Profil</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        
        <!-- Kolom Kiri: Informasi Dasar -->
        <div class="md:col-span-2 space-y-8">
            
            <!-- Update Info Diri -->
            <div class="bg-white shadow rounded-2xl p-6 sm:p-8">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Informasi Profil</h3>
                <p class="text-sm text-gray-600 mb-6">Perbarui nama tampilan dan foto profil akun Anda.</p>

                @if (session('status') === 'profile-updated' || session('success'))
                <div class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg">
                    <i class="fas fa-check-circle mr-2"></i> {{ session('success') ?? 'Profil telah diperbarui.' }}
                </div>
                @endif

                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <!-- Foto Profil -->
                    <div class="flex items-center space-x-6">
                        <div class="shrink-0 relative group">
                            <img id="photo-preview" class="h-20 w-20 object-cover rounded-full shadow-md border-2 border-white" 
                                 src="{{ auth()->user()->profile_photo_path ? asset('storage/' . auth()->user()->profile_photo_path) : 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name).'&color=7F9CF5&background=EBF4FF' }}" alt="{{ auth()->user()->name }}" />
                            <div class="absolute inset-0 bg-black bg-opacity-40 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer" onclick="document.getElementById('photo-input').click()">
                                <i class="fas fa-camera text-white"></i>
                            </div>
                        </div>
                        <label class="block">
                            <span class="sr-only">Pilih foto profil</span>
                            <input type="file" name="photo" id="photo-input" accept="image/*" class="block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-full file:border-0
                                file:text-sm file:font-semibold
                                file:bg-blue-50 file:text-blue-700
                                hover:file:bg-blue-100 cursor-pointer
                            " onchange="previewImage(event)"/>
                        </label>
                    </div>
                    @error('photo')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror

                    <!-- Nama -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nama Tampilan</label>
                        <input type="text" name="name" id="name" value="{{ old('name', auth()->user()->name) }}" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-4 py-2 border">
                        @error('name')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end">
                        <button type="submit" class="px-6 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>

            <!-- Ganti Password -->
            <div class="bg-white shadow rounded-2xl p-6 sm:p-8">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Ganti Password</h3>
                <p class="text-sm text-gray-600 mb-6">Pastikan akun Anda menggunakan kata sandi yang panjang dan acak untuk tetap aman.</p>

                @if (session('status') === 'password-updated')
                <div class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg">
                    <i class="fas fa-check-circle mr-2"></i> {{ session('success') ?? 'Password berhasil diperbarui!' }}
                </div>
                @endif

                <form method="POST" action="{{ route('profile.password') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700">Password Saat Ini</label>
                        <input type="password" name="current_password" id="current_password" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm px-4 py-2 border">
                        @error('current_password')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password Baru</label>
                        <input type="password" name="password" id="password" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-4 py-2 border">
                        @error('password')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-4 py-2 border">
                    </div>

                    <div class="flex items-center justify-end">
                        <button type="submit" class="px-6 py-2 bg-gray-800 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Perbarui Password
                        </button>
                    </div>
                </form>
            </div>
            
        </div>

        <!-- Kolom Kanan: Panduan -->
        <div class="md:col-span-1 space-y-6">
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-6 border border-blue-100">
                <h4 class="font-semibold text-blue-900 mb-3"><i class="fas fa-info-circle mr-2"></i> Tips Keamanan</h4>
                <ul class="text-sm text-blue-800 space-y-3">
                    <li class="flex items-start">
                        <i class="fas fa-check text-blue-500 mt-1 mr-2"></i>
                        <span>Gunakan setidaknya 8 karakter untuk kata sandi baru Anda.</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-blue-500 mt-1 mr-2"></i>
                        <span>Jangan membagikan kata sandi dengan rekan kerja siapapun.</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-blue-500 mt-1 mr-2"></i>
                        <span>Foto profil memudahkan orang lain mengenali Anda di forum/artikel.</span>
                    </li>
                </ul>
            </div>
        </div>
        
    </div>
</div>

<script>
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function() {
            const output = document.getElementById('photo-preview');
            output.src = reader.result;
        }
        reader.readAsDataURL(event.target.files[0]);
    }
</script>
@endsection
