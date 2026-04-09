<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // semua boleh login
    }

    public function rules(): array
    {
        $rules = [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];

        // Hanya wajib reCAPTCHA jika ada token aktif di .env
        if (config('services.recaptcha.secret_key')) {
            $rules['g-recaptcha-response'] = ['required'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'g-recaptcha-response.required' => 'Silakan verifikasi captcha terlebih dahulu.',
        ];
    }

    /**
     * Jalankan validasi tambahan setelah rules() lolos
     */
    protected function passedValidation()
    {
        if (!config('services.recaptcha.secret_key')) {
            return;
        }

        try {
            // Kirim verifikasi ke Google reCAPTCHA
            $response = Http::asForm()
                ->withoutVerifying() // Laragon kadang bermasalah dengan cert SSL
                ->timeout(5)
                ->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => config('services.recaptcha.secret_key'),
                'response' => $this->input('g-recaptcha-response'),
                'remoteip' => $this->ip(),
            ]);

            $result = $response->json();

            if (!($result['success'] ?? false)) {
                throw ValidationException::withMessages([
                    'g-recaptcha-response' => 'Verifikasi captcha gagal, coba lagi.',
                ]);
            }
        } catch (\Exception $e) {
            // Bypass reCAPTCHA jika koneksi error di local/development
            if (app()->environment('local')) {
                return;
            }
            throw ValidationException::withMessages([
                'g-recaptcha-response' => 'Sistem tidak dapat terhubung ke verifikasi reCAPTCHA. Silakan cek koneksi.',
            ]);
        }
    }

    /**
     * Proses autentikasi user
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (!Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Cegah brute force login (Rate Limiting)
     */
    public function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Key unik untuk rate limiting login
     */
    public function throttleKey(): string
    {
        return Str::lower($this->input('email')) . '|' . $this->ip();
    }
}
