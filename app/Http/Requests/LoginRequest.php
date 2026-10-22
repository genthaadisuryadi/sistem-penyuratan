<?php

namespace App\Http\Requests;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
        ];
    }

    /**
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->pastikanTidakTerkunci();

        $email = (string) $this->input('email');

        // REVISI: Menggunakan $email sebagai pengganti password
        $berhasil = Auth::attempt(
            ['email' => $email, 'password' => $email, 'is_active' => true],
            false, // tanpa "ingat saya": sesi admin harus berakhir sendiri
        );

        if (! $berhasil) {
            RateLimiter::hit($this->kunciThrottle(), (int) config('penyuratan.login.decay_seconds'));

            throw ValidationException::withMessages(['email' => trans('auth.failed')]);
        }

        RateLimiter::clear($this->kunciThrottle());
    }

    private function pastikanTidakTerkunci(): void
    {
        if (! RateLimiter::tooManyAttempts($this->kunciThrottle(), (int) config('penyuratan.login.max_attempts'))) {
            return;
        }

        event(new Lockout($this));

        $detik = RateLimiter::availableIn($this->kunciThrottle());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', ['seconds' => $detik, 'minutes' => (int) ceil($detik / 60)]),
        ]);
    }

    private function kunciThrottle(): string
    {
        return Str::transliterate(Str::lower((string) $this->input('email')).'|'.$this->ip());
    }
}