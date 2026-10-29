<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PengaturanController;
use App\Http\Controllers\Admin\PenggunaController;
use App\Http\Controllers\Admin\JenisSuratController;
use App\Http\Controllers\Admin\SuratController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Publik\VerifikasiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halaman publik (verifikasi)
|--------------------------------------------------------------------------
*/
Route::get('/', [VerifikasiController::class, 'beranda'])->name('beranda');
Route::post('/cek', [VerifikasiController::class, 'cari'])->middleware('throttle:cek-kode')->name('verifikasi.cari');
Route::get('/verify/{nomor_surat}', [VerifikasiController::class, 'verifikasiNomor'])
    ->where('nomor_surat', '.*')->middleware('throttle:verifikasi')->name('verifikasi.nomor');
Route::get('/surat/{uuid}', [VerifikasiController::class, 'show'])
    ->whereUuid('uuid')->middleware('throttle:verifikasi')->name('verifikasi.show');
Route::get('/surat/{uuid}/unduh', [VerifikasiController::class, 'unduh'])
    ->whereUuid('uuid')->middleware('throttle:unduh')->name('verifikasi.unduh');

/*
|--------------------------------------------------------------------------
| Autentikasi admin
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get(config('penyuratan.login_path'), [LoginController::class, 'create'])->name('login');
    Route::post(config('penyuratan.login_path'), [LoginController::class, 'store'])->middleware('throttle:login-ip');

    Route::get('lupa-sandi', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('lupa-sandi', [ForgotPasswordController::class, 'store'])->middleware('throttle:lupa-sandi')->name('password.email');
    Route::get('reset-sandi/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-sandi', [ResetPasswordController::class, 'store'])->middleware('throttle:lupa-sandi')->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Dasbor admin
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin.aktif', 'no-store'])->prefix('admin')->name('admin.')->group(function () {
    Route::post('keluar', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/', DashboardController::class)->name('dashboard');

    Route::get('surat', [SuratController::class, 'index'])->name('surat.index');
    Route::get('surat/buat', [SuratController::class, 'create'])->name('surat.create');
    Route::post('qr-preview', [SuratController::class, 'previewQr'])->middleware('throttle:60,1')->name('qr-preview');
    Route::post('surat', [SuratController::class, 'store'])->middleware('throttle:30,1')->name('surat.store');
    Route::get('surat/{surat}', [SuratController::class, 'show'])->name('surat.show');
    Route::get('surat/{surat}/unduh', [SuratController::class, 'download'])->name('surat.unduh');
    Route::post('surat/{surat}/publish', [SuratController::class, 'publish'])->name('surat.publish');
    Route::post('surat/{surat}/cabut', [SuratController::class, 'revoke'])->name('surat.cabut');

    Route::middleware('can:manage-users')->group(function () {
        Route::get('pengguna', [PenggunaController::class, 'index'])->name('pengguna.index');
        Route::get('pengguna/buat', [PenggunaController::class, 'create'])->name('pengguna.create');
        Route::post('pengguna', [PenggunaController::class, 'store'])->name('pengguna.store');
        Route::get('pengguna/{pengguna}/ubah', [PenggunaController::class, 'edit'])->name('pengguna.edit');
        Route::put('pengguna/{pengguna}', [PenggunaController::class, 'update'])->name('pengguna.update');
        Route::post('pengguna/{pengguna}/reset-password', [PenggunaController::class, 'resetPassword'])->name('pengguna.reset-password');
        Route::delete('pengguna/{pengguna}', [PenggunaController::class, 'destroy'])->name('pengguna.destroy');
    });

    Route::middleware('can:manage-jenis-surat')->group(function () {
        Route::get('jenis-surat', [JenisSuratController::class, 'index'])->name('jenis-surat.index');
        Route::post('jenis-surat', [JenisSuratController::class, 'store'])->name('jenis-surat.store');
        Route::put('jenis-surat/{jenisSurat}', [JenisSuratController::class, 'update'])->name('jenis-surat.update');
        Route::delete('jenis-surat/{jenisSurat}', [JenisSuratController::class, 'destroy'])->name('jenis-surat.destroy');
    });

    Route::get('pengaturan', [PengaturanController::class, 'edit'])->name('pengaturan');
    Route::put('pengaturan/profil', [PengaturanController::class, 'updateProfil'])->name('pengaturan.profil');
    Route::put('pengaturan/sandi', [PengaturanController::class, 'updatePassword'])->name('pengaturan.sandi');
});