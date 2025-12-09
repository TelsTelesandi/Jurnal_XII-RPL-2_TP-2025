<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-white to-indigo-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Header Section -->
            <div class="text-center">
                <div class="mx-auto h-16 w-16 flex items-center justify-center rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 shadow-lg">
                    <x-authentication-card-logo class="w-8 h-8 text-white" />
                </div>
                <h2 class="mt-6 text-3xl font-bold text-gray-900">Atur Ulang Kata Sandi</h2>
                <p class="mt-2 text-sm text-gray-600">Kami akan mengirimkan tautan reset yang aman</p>
            </div>

            <!-- Main Form Card -->
            <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
                <!-- Instructions -->
                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-start">
                        <svg class="flex-shrink-0 w-5 h-5 text-blue-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700 leading-relaxed">
                                {{ __('Lupa kata sandi Anda? Tidak masalah. Cukup masukkan alamat email Anda dan kami akan mengirimkan tautan reset kata sandi untuk membuat yang baru.') }}
                            </p>
                        </div>
                    </div>
                </div>

                <x-validation-errors class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700" />

                @session('status')
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <div class="flex items-center">
                            <svg class="flex-shrink-0 w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="ml-3 text-sm font-medium text-green-700">{{ $value }}</p>
                        </div>
                    </div>
                @endsession

                <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                    @csrf

                    <!-- Email Field -->
                    <div class="space-y-2">
                        <x-label for="email" value="{{ __('Alamat Email') }}" class="text-sm font-semibold text-gray-700" />
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                </svg>
                            </div>
                            <x-input id="email" 
                                     class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-gray-50 focus:bg-white" 
                                     type="email" 
                                     name="email" 
                                     :value="old('email')" 
                                     required autofocus 
                                     autocomplete="username"
                                     placeholder="Masukkan alamat email Anda" />
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button type="submit"
                                class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-semibold rounded-lg text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:scale-[1.02] shadow-lg hover:shadow-xl">
                            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <svg class="h-5 w-5 text-blue-300 group-hover:text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </span>
                            {{ __('Kirim Tautan Reset') }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Footer Section -->
            <div class="text-center space-y-3">
                <p class="text-sm text-gray-600">
                    Ingat kata sandi Anda?
                    <a href="{{ route('login') }}"
                       class="font-semibold text-blue-600 hover:text-blue-500 focus:outline-none focus:underline transition-colors duration-200">
                        {{ __('Masuk di sini') }}
                    </a>
                </p>
                
                @if (Route::has('register'))
                    <p class="text-sm text-gray-600">
                        Belum punya akun?
                        <a href="{{ route('register') }}"
                           class="font-semibold text-blue-600 hover:text-blue-500 focus:outline-none focus:underline transition-colors duration-200">
                            {{ __('Buat akun di sini') }}
                        </a>
                    </p>
                @endif
            </div>

            <!-- Security Notice -->
            <div class="mt-8 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <div class="flex items-start">
                    <svg class="flex-shrink-0 w-5 h-5 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <div class="ml-3">
                        <p class="text-xs text-gray-600 leading-relaxed">
                            Demi alasan keamanan, tautan reset akan kedaluwarsa dalam 60 menit.  
                            Jika Anda tidak menerima email dalam beberapa menit, harap periksa folder spam Anda.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const emailInput = document.getElementById('email');
    
    // Fokus efek
    emailInput.addEventListener('focus', function() {
        this.parentElement.classList.add('ring-2', 'ring-blue-200');
    });
    emailInput.addEventListener('blur', function() {
        this.parentElement.classList.remove('ring-2', 'ring-blue-200');
    });
    
    // Validasi email
    emailInput.addEventListener('input', function() {
        const email = this.value;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const existingMsg = this.parentElement.parentElement.querySelector('.validation-message');
        if (existingMsg) existingMsg.remove();
        
        if (email.length > 0 && !emailRegex.test(email)) {
            const validationMsg = document.createElement('p');
            validationMsg.className = 'validation-message mt-1 text-xs text-red-500';
            validationMsg.textContent = 'Masukkan alamat email yang valid';
            this.parentElement.parentElement.appendChild(validationMsg);
        }
    });
    
    // Loading state
    const form = document.querySelector('form');
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    
    form.addEventListener('submit', function(e) {
        const email = emailInput.value;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (!emailRegex.test(email)) {
            e.preventDefault();
            return;
        }
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = `
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Mengirim...
        `;
        
        setTimeout(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }, 10000);
    });
    
    // Auto-hide status messages
    const statusMessages = document.querySelectorAll('[class*="bg-green-50"], [class*="bg-red-50"]');
    statusMessages.forEach(message => {
        setTimeout(() => {
            message.style.opacity = '0';
            message.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                if (message.parentNode) message.parentNode.removeChild(message);
            }, 300);
        }, 10000);
    });
});
</script>
