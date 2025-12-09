@extends('admin.layouts.app')

@section('title', 'Tambah User')
@section('page-title', 'Tambah User Baru')

@section('content')
<div class="max-w-2xl mx-auto bg-white border border-gray-200 shadow-lg rounded-xl overflow-hidden">

    <!-- Header Form -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-5 flex items-center gap-4">
        <div class="h-12 w-12 rounded-full bg-white/20 flex items-center justify-center text-white text-xl">
            <i class="fa-solid fa-user-plus"></i>
        </div>
        <div>
            <h2 class="text-lg font-semibold text-white">Tambah User Baru</h2>
            <p class="text-sm text-blue-100">Isi data lengkap untuk membuat akun baru.</p>
        </div>
    </div>

    <!-- Body Form -->
    <div class="p-6 md:p-8 space-y-6">
        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Name -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <i class="fa-solid fa-user"></i>
                    </span>
                    <input type="text" name="name" value="{{ old('name') }}"
                           class="pl-10 pr-3 py-2 block w-full rounded-md border-gray-300 shadow-sm 
                                  focus:border-blue-500 focus:ring focus:ring-blue-200 sm:text-sm" required>
                </div>
            </div>

            <!-- Email -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <i class="fa-solid fa-envelope"></i>
                    </span>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="pl-10 pr-3 py-2 block w-full rounded-md border-gray-300 shadow-sm 
                                  focus:border-blue-500 focus:ring focus:ring-blue-200 sm:text-sm" required>
                </div>
            </div>

            <!-- Password & Konfirmasi -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input type="password" id="password" name="password"
                               class="pl-10 pr-10 py-2 block w-full rounded-md border-gray-300 shadow-sm 
                                      focus:border-blue-500 focus:ring focus:ring-blue-200 sm:text-sm" required>
                        <!-- Toggle eye -->
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 cursor-pointer text-gray-400"
                              onclick="togglePassword('password', this)">
                            <i class="fa-solid fa-eye"></i>
                        </span>
                    </div>
                </div>

                <!-- Konfirmasi Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="pl-10 pr-10 py-2 block w-full rounded-md border-gray-300 shadow-sm 
                                      focus:border-blue-500 focus:ring focus:ring-blue-200 sm:text-sm" required>
                        <!-- Toggle eye -->
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 cursor-pointer text-gray-400"
                              onclick="togglePassword('password_confirmation', this)">
                            <i class="fa-solid fa-eye"></i>
                        </span>
                    </div>
                </div>
            </div>

           <!-- Role -->
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
    <div class="relative">
        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
            <i class="fa-solid fa-user-shield"></i>
        </span>
        <select name="role_id" 
                class="pl-10 pr-3 py-2 block w-full rounded-md border-gray-300 shadow-sm 
                       focus:border-indigo-500 focus:ring focus:ring-indigo-200 sm:text-sm" required>
            <option value="1" {{ $user->role_id == 1 ? 'selected' : '' }}>Admin</option>
            <option value="2" {{ $user->role_id == 2 ? 'selected' : '' }}>User</option>
            <option value="3" {{ $user->role_id == 3 ? 'selected' : '' }}>Moderator</option>
        </select>
    </div>
</div>


            <!-- Buttons -->
            <div class="flex flex-col sm:flex-row justify-end gap-3 pt-4">
                <a href="{{ route('admin.users.index') }}"
                   class="px-4 py-2 rounded-md bg-gray-200 text-gray-700 hover:bg-gray-300 text-center w-full sm:w-auto shadow">
                   Batal
                </a>
                <button type="submit"
                        class="px-4 py-2 rounded-md bg-blue-600 text-white font-medium shadow-md hover:bg-blue-700 
                               transition w-full sm:w-auto">
                    <i class="fa-solid fa-save mr-1"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Script toggle password -->
<script>
    function togglePassword(fieldId, el) {
        const input = document.getElementById(fieldId);
        const icon = el.querySelector("i");
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash");
        } else {
            input.type = "password";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye");
        }
    }
</script>
@endsection
