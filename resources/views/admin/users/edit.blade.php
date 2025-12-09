@extends('admin.layouts.app')

@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('content')
<div class="max-w-2xl mx-auto bg-white border border-gray-200 shadow-lg rounded-xl overflow-hidden">

    <!-- Header Form -->
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-5 flex items-center gap-4">
        <div class="h-12 w-12 rounded-full bg-white/20 flex items-center justify-center text-white text-xl">
            <i class="fa-solid fa-user-pen"></i>
        </div>
        <div>
            <h2 class="text-lg font-semibold text-white">Edit User</h2>
            <p class="text-sm text-indigo-100">Perbarui data user di bawah sesuai kebutuhan.</p>
        </div>
    </div>

    <!-- Body Form -->
    <div class="p-6 md:p-8 space-y-6">
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Name -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <i class="fa-solid fa-user"></i>
                    </span>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                           class="pl-10 pr-3 py-2 block w-full rounded-md border-gray-300 shadow-sm 
                                  focus:border-indigo-500 focus:ring focus:ring-indigo-200 sm:text-sm" required>
                </div>
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <i class="fa-solid fa-envelope"></i>
                    </span>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                           class="pl-10 pr-3 py-2 block w-full rounded-md border-gray-300 shadow-sm 
                                  focus:border-indigo-500 focus:ring focus:ring-indigo-200 sm:text-sm" required>
                </div>
                @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password (kosongkan jika tidak diubah)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input type="password" id="password" name="password"
                               class="pl-10 pr-10 py-2 block w-full rounded-md border-gray-300 shadow-sm 
                                      focus:border-indigo-500 focus:ring focus:ring-indigo-200 sm:text-sm">
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 cursor-pointer text-gray-400"
                              onclick="togglePassword('password', this)">
                            <i class="fa-solid fa-eye"></i>
                        </span>
                    </div>
                </div>

                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="pl-10 pr-10 py-2 block w-full rounded-md border-gray-300 shadow-sm 
                                      focus:border-indigo-500 focus:ring focus:ring-indigo-200 sm:text-sm">
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
                       focus:border-blue-500 focus:ring focus:ring-blue-200 sm:text-sm" required>
            <option value="1">Admin</option>
            <option value="2" selected>User</option>
            <option value="3">Moderator</option>
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
                        class="px-4 py-2 rounded-md bg-indigo-600 text-white font-medium shadow-md hover:bg-indigo-700 
                               transition w-full sm:w-auto">
                    <i class="fa-solid fa-save mr-1"></i> Perbarui
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Toggle password -->
<script>
    function togglePassword(fieldId, el) {
        const input = document.getElementById(fieldId);
        const icon = el.querySelector("i");
        if (input.type === "password") {
            input.type = "text";
            icon.classList.replace("fa-eye", "fa-eye-slash");
        } else {
            input.type = "password";
            icon.classList.replace("fa-eye-slash", "fa-eye");
        }
    }
</script>
@endsection
