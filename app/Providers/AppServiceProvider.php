<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Deteksi N+1 saat pengembangan; di produksi dibiarkan agar tidak pernah memutus halaman.
        Model::preventLazyLoading(! $this->app->isProduction());

        Gate::define('manage-users', fn ($user) => $user->dapatKelolaPengguna());
        Gate::define('manage-jenis-surat', fn ($user) => $user->dapatKelolaPengguna());

        Password::defaults(fn () => Password::min(10)->mixedCase()->numbers());

        // Tautan reset kata sandi memakai APP_URL, bukan header Host dari request.
        ResetPassword::createUrlUsing(fn ($user, string $token) => rtrim((string) config('app.url'), '/')
            .route('password.reset', ['token' => $token, 'email' => $user->getEmailForPasswordReset()], false));

        RateLimiter::for('login-ip', fn (Request $r) => Limit::perMinute(20)->by($r->ip()));
        RateLimiter::for('lupa-sandi', fn (Request $r) => Limit::perMinute(5)->by($r->ip()));
        RateLimiter::for('verifikasi', fn (Request $r) => Limit::perMinute(60)->by($r->ip()));
        RateLimiter::for('unduh', fn (Request $r) => Limit::perMinute(20)->by($r->ip()));
        // Pencarian kode manual dibatasi ketat agar kode tidak bisa ditebak lewat brute force.
        RateLimiter::for('cek-kode', fn (Request $r) => [
            Limit::perMinute(8)->by($r->ip()),
            Limit::perHour(60)->by($r->ip()),
        ]);
    }
}
