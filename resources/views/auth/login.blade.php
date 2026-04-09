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
                <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight animate-fade-in-up delay-100">Selamat Datang Kembali</h2>
                <p class="mt-2 text-sm text-gray-500 font-medium animate-fade-in-up delay-200">Silakan masuk ke akun Anda untuk melanjutkan percobaan</p>
            </div>

            <!-- Main Form Card -->
            <div class="bg-white/90 backdrop-blur-xl rounded-3xl shadow-2xl p-8 border border-white animate-fade-in-up delay-200">
                <x-validation-errors class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700" />

                @session('status')
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">
                        {{ $value }}
                    </div>
                @endsession

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
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
                                     placeholder="Masukkan email Anda" />
                        </div>
                    </div>

                    <!-- Password Field with Enhanced Toggle -->
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
                                     autocomplete="current-password"
                                     placeholder="Masukkan kata sandi" />

                            <!-- Enhanced Password Toggle -->
                            <button type="button" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none focus:text-blue-500 transition-colors duration-200"
                                    onclick="togglePassword()">
                                <svg id="eyeOpen" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg id="eyeClosed" class="h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.481-2.139m1.138-1.138A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.97 9.97 0 01-.826 1.874m-1.138 1.138L19.07 19.07M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between">
                        <label for="remember_me" class="flex items-center">
                            <x-checkbox id="remember_me" 
                                        name="remember" 
                                        class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 focus:ring-2" />
                            <span class="ml-2 text-sm text-gray-600">{{ __('Ingat saya') }}</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-sm font-medium text-blue-600 hover:text-blue-500 focus:outline-none focus:underline transition-colors duration-200" 
                               href="{{ route('password.request') }}">
                                {{ __('Lupa kata sandi?') }}
                            </a>
                        @endif
                    </div>
{{-- === reCAPTCHA v2 Checkbox (explicit render) === --}}
@php($siteKey = config('services.recaptcha.site_key'))

<div class="space-y-2">
  <label class="text-sm font-semibold text-gray-700">Verifikasi Keamanan</label>

  @if (empty($siteKey))
    <div class="p-3 rounded-lg border border-amber-300 bg-amber-50 text-amber-800 text-sm">
      RECAPTCHA_SITE_KEY belum di-set. Isi .env lalu jalankan <code>php artisan config:clear</code>.
    </div>
  @else
    <div id="recaptcha-container" class="rounded-xl border border-gray-200 bg-gray-50 p-3">
      <div id="rc-loading" class="flex items-center gap-2 text-gray-400 text-sm">
        <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
        </svg>
        Memuat reCAPTCHA…
      </div>
    </div>

    @error('g-recaptcha-response')
      <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
    @enderror

    <script>
      function onRecaptchaLoad() {
        const container = document.getElementById('recaptcha-container');
        if (!container || !window.grecaptcha?.render) return;

        document.getElementById('rc-loading')?.remove();

        grecaptcha.render(container, {
          sitekey: @json($siteKey),
          theme: 'light', // ubah ke 'dark' kalau perlu
          size: 'normal', // bisa 'compact'
          callback: () => {
            document.querySelector('#rc-error')?.classList.add('hidden');
          }
        });
      }
    </script>

    <script src="https://www.google.com/recaptcha/api.js?onload=onRecaptchaLoad&render=explicit" async defer></script>
  @endif
</div>

                    <!-- Login Button -->
                    <div>
                        <button type="submit"
                                class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-semibold rounded-lg text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:scale-[1.02] shadow-lg hover:shadow-xl">
                            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <svg class="h-5 w-5 text-blue-300 group-hover:text-blue-200" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            {{ __('Masuk') }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Back to Home Button -->
            <a href="{{ url('/') }}"
               class="group relative w-full flex justify-center py-3 px-4 border border-gray-300 text-sm font-semibold rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 transition-all duration-200 transform hover:scale-[1.02] shadow-md hover:shadow-lg">
                <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                    <svg class="h-5 w-5 text-gray-400 group-hover:text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 19l-7-7h4V4h6v8h4l-7 7z" clip-rule="evenodd" />
                    </svg>
                </span>
                {{ __('Kembali ke Beranda') }}
            </a>

            <!-- Footer Section -->
            @if (Route::has('register'))
                <div class="text-center">
                    <p class="text-sm text-gray-600">
                        Belum punya akun?
                        <a href="{{ route('register') }}"
                           class="font-semibold text-blue-600 hover:text-blue-500 focus:outline-none focus:underline transition-colors duration-200">
                            {{ __('Buat akun di sini') }}
                        </a>
                    </p>
                </div>
            @endif
        </div>
    </div>
</x-guest-layout>
