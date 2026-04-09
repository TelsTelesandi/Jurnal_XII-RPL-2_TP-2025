<x-guest-layout>
    <style>
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-up {
            animation: fadeInUp 0.5s ease-out forwards;
        }
        .delay-100 { animation-delay: 100ms; }
        .delay-200 { animation-delay: 200ms; }
    </style>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-white to-indigo-100 py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
        <!-- Decoration background elements -->
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-64 h-64 rounded-full bg-blue-400 opacity-10 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-64 h-64 rounded-full bg-indigo-500 opacity-10 blur-3xl"></div>
        
        <div class="max-w-md w-full space-y-8 z-10">
            <!-- Header Section -->
            <div class="text-center">
                <div class="flex justify-center mb-6 animate-fade-in-up">
                    <img src="{{ setting('site_logo') ? asset('storage/'.setting('site_logo')) : asset('image/image.png') }}" 
                         alt="Logo PT RKA" 
                         class="h-24 w-auto drop-shadow-2xl hover:scale-105 transition-transform duration-300">
                </div>
                <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight animate-fade-in-up delay-100">Buat Akun Anda</h2>
                <p class="mt-2 text-sm text-gray-500 font-medium animate-fade-in-up delay-200">Bergabung dan nikmati semua layanannya</p>
            </div>

            <!-- Main Form Card -->
            <div class="bg-white/90 backdrop-blur-xl rounded-3xl shadow-2xl p-8 border border-white animate-fade-in-up delay-200">
                <x-validation-errors class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700" />

                <form method="POST" action="{{ route('register') }}" class="space-y-6">
                    @csrf

                    <!-- Name Field -->
                    <div class="space-y-2">
                        <x-label for="name" value="{{ __('Nama Lengkap') }}" class="text-sm font-semibold text-gray-700" />
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <x-input id="name" 
                                     class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-gray-50 focus:bg-white" 
                                     type="text" 
                                     name="name" 
                                     :value="old('name')" 
                                     required autofocus 
                                     autocomplete="name"
                                     placeholder="Masukkan nama lengkap" />
                        </div>
                    </div>

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
                                     required 
                                     autocomplete="username"
                                     placeholder="Masukkan alamat email" />
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div class="space-y-2">
                        <x-label for="password" value="{{ __('Kata Sandi') }}" class="text-sm font-semibold text-gray-700" />
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <x-input id="password" 
                                     class="block w-full pl-10 pr-12 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-gray-50 focus:bg-white" 
                                     type="password" 
                                     name="password" 
                                     required 
                                     autocomplete="new-password"
                                     placeholder="Buat kata sandi" />

                            <!-- Password Toggle -->
                            <button type="button" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none focus:text-blue-500 transition-colors duration-200"
                                    onclick="togglePassword('password','eyeOpenReg','eyeClosedReg')">
                                <svg id="eyeOpenReg" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg id="eyeClosedReg" class="h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.481-2.139m1.138-1.138A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.97 9.97 0 01-.826 1.874m-1.138 1.138L19.07 19.07M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                        <div id="password-strength" class="mt-1 text-xs text-gray-500 hidden"></div>
                    </div>

                    <!-- Confirm Password Field -->
                    <div class="space-y-2">
                        <x-label for="password_confirmation" value="{{ __('Konfirmasi Kata Sandi') }}" class="text-sm font-semibold text-gray-700" />
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <x-input id="password_confirmation" 
                                     class="block w-full pl-10 pr-12 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-gray-50 focus:bg-white" 
                                     type="password" 
                                     name="password_confirmation" 
                                     required 
                                     autocomplete="new-password"
                                     placeholder="Konfirmasi kata sandi Anda" />

                            <!-- Confirm Password Toggle -->
                            <button type="button" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none focus:text-blue-500 transition-colors duration-200"
                                    onclick="togglePassword('password_confirmation','eyeOpenConf','eyeClosedConf')">
                                <svg id="eyeOpenConf" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg id="eyeClosedConf" class="h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.481-2.139m1.138-1.138A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.97 9.97 0 01-.826 1.874m-1.138 1.138L19.07 19.07M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                        <div id="password-match" class="mt-1 text-xs hidden"></div>
                    </div>

                    <!-- Terms and Privacy Policy -->
                    @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                        <div class="space-y-2">
                            <div class="flex items-start space-x-3 p-4 bg-gray-50 rounded-lg">
                                <x-checkbox name="terms" 
                                           id="terms" 
                                           required 
                                           class="mt-1 h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 focus:ring-2" />
                                <div class="text-sm text-gray-600 leading-relaxed">
                                    {!! __('Saya menyetujui :terms_of_service dan :privacy_policy', [
                                            'terms_of_service' => '<a target="_blank" href="'.route('terms.show').'" class="font-medium text-blue-600 hover:text-blue-500 focus:outline-none focus:underline transition-colors duration-200">'.__('Ketentuan Layanan').'</a>',
                                            'privacy_policy' => '<a target="_blank" href="'.route('policy.show').'" class="font-medium text-blue-600 hover:text-blue-500 focus:outline-none focus:underline transition-colors duration-200">'.__('Kebijakan Privasi').'</a>',
                                    ]) !!}
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Register Button -->
                    <div>
                        <button type="submit"
                                class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-semibold rounded-lg text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:scale-[1.02] shadow-lg hover:shadow-xl">
                            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <svg class="h-5 w-5 text-blue-300 group-hover:text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                            </span>
                            {{ __('Buat Akun') }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Footer Section -->
            <div class="text-center">
                <p class="text-sm text-gray-600">
                    Sudah punya akun?
                    <a href="{{ route('login') }}"
                       class="font-semibold text-blue-600 hover:text-blue-500 focus:outline-none focus:underline transition-colors duration-200">
                        {{ __('Masuk di sini') }}
                    </a>
                </p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(fieldId, eyeOpenId, eyeClosedId) {
            const passwordField = document.getElementById(fieldId);
            const eyeOpen = document.getElementById(eyeOpenId);
            const eyeClosed = document.getElementById(eyeClosedId);
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                eyeOpen.classList.add('hidden');
                eyeClosed.classList.remove('hidden');
            } else {
                passwordField.type = 'password';
                eyeOpen.classList.remove('hidden');
                eyeClosed.classList.add('hidden');
            }
        }

        // Enhanced interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Enhanced focus effects for inputs
            const inputs = document.querySelectorAll('input[type="text"], input[type="email"], input[type="password"]');
            
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.closest('.space-y-2').classList.add('ring-2', 'ring-blue-200', 'rounded-lg', 'p-1');
                });
                
                input.addEventListener('blur', function() {
                    this.closest('.space-y-2').classList.remove('ring-2', 'ring-blue-200', 'rounded-lg', 'p-1');
                });
            });
            
            // Password strength indicator
            const passwordField = document.getElementById('password');
            const strengthIndicator = document.getElementById('password-strength');
            
            passwordField.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                let feedback = [];
                
                if (password.length >= 8) strength++;
                else feedback.push('minimal 8 karakter');
                
                if (/[A-Z]/.test(password)) strength++;
                else feedback.push('huruf kapital');
                
                if (/[a-z]/.test(password)) strength++;
                else feedback.push('huruf kecil');
                
                if (/[0-9]/.test(password)) strength++;
                else feedback.push('angka');
                
                if (/[^A-Za-z0-9]/.test(password)) strength++;
                else feedback.push('karakter khusus');
                
                const strengthText = ['Sangat Lemah', 'Lemah', 'Cukup', 'Baik', 'Kuat'][strength];
                const strengthColors = ['text-red-500', 'text-orange-500', 'text-yellow-500', 'text-blue-500', 'text-green-500'];
                
                if (password.length === 0) {
                    strengthIndicator.textContent = '';
                    strengthIndicator.classList.add('hidden');
                } else {
                    strengthIndicator.classList.remove('hidden');
                    strengthIndicator.className = `mt-1 text-xs ${strengthColors[strength] || 'text-gray-500'}`;
                    
                    if (strength < 3) {
                        strengthIndicator.textContent = `Kekuatan kata sandi: ${strengthText}. Tambahkan: ${feedback.slice(0, 2).join(', ')}`;
                    } else {
                        strengthIndicator.textContent = `Kekuatan kata sandi: ${strengthText}`;
                    }
                }
            });
            
            // Password match indicator
            const confirmPasswordField = document.getElementById('password_confirmation');
            const matchIndicator = document.getElementById('password-match');
            
            function checkPasswordMatch() {
                const password = passwordField.value;
                const confirmPassword = confirmPasswordField.value;
                
                if (confirmPassword.length === 0) {
                    matchIndicator.textContent = '';
                    matchIndicator.classList.add('hidden');
                    return;
                }
                
                matchIndicator.classList.remove('hidden');
                
                if (password === confirmPassword) {
                    matchIndicator.className = 'mt-1 text-xs text-green-500';
                    matchIndicator.textContent = '✓ Kata sandi cocok';
                } else {
                    matchIndicator.className = 'mt-1 text-xs text-red-500';
                    matchIndicator.textContent = '✗ Kata sandi tidak cocok';
                }
            }
            
            confirmPasswordField.addEventListener('input', checkPasswordMatch);
            passwordField.addEventListener('input', checkPasswordMatch);
            
            // Loading state for register button
            const form = document.querySelector('form');
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            
            form.addEventListener('submit', function() {
                submitBtn.disabled = true;
                submitBtn.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Membuat Akun...
                `;
                
                // Note: In real app, remove timeout and handle via backend response
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }, 5000);
            });
        });
    </script>
</x-guest-layout>