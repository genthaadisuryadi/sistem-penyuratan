<?php

namespace Database\Seeders;

use App\Enums\RoleAdmin;
use App\Models\User;
use App\Models\JenisSurat;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Membuat akun superadmin pertama dari variabel ADMIN_* di .env.
     * Jika ADMIN_PASSWORD kosong, kata sandi acak dibuat dan ditampilkan SEKALI.
     */
    public function run(): void
    {
        foreach ([
            ['nama' => 'Surat Undangan', 'kode' => 'UND'],
            ['nama' => 'Surat Pemberitahuan', 'kode' => 'PEM'],
            ['nama' => 'Surat Keputusan', 'kode' => 'KEP'],
            ['nama' => 'Surat Tugas', 'kode' => 'TGS'],
            ['nama' => 'Surat Permohonan', 'kode' => 'PMH'],
        ] as $jenis) {
            JenisSurat::firstOrCreate(['kode' => $jenis['kode']], $jenis + ['is_active' => true]);
        }

        $email = env('ADMIN_EMAIL', 'admin@instansi.test');

        $akunAwal = User::where('email', $email)->first();
        if ($akunAwal) {
            // Email bootstrap di .env adalah akun Super Admin; pertahankan akunnya
            // dan pastikan migrasi dari role lama tidak menghilangkan akses utama.
            if ($akunAwal->role !== RoleAdmin::Superadmin) {
                $akunAwal->update(['role' => RoleAdmin::Superadmin]);
            }
            $this->command?->warn("Akun Super Admin {$email} sudah ada, kata sandi tidak diubah.");

            return;
        }

        $password = env('ADMIN_PASSWORD') ?: Str::password(16, symbols: false);

        User::create([
            'name' => env('ADMIN_NAME', 'Administrator'),
            'email' => $email,
            'username' => env('ADMIN_USERNAME', 'admin'),
            'password' => $password,
            'role' => RoleAdmin::Superadmin,
            'is_active' => true,
        ]);

        $this->command?->info("Superadmin dibuat: {$email}");

        if (! env('ADMIN_PASSWORD')) {
            $this->command?->warn("Kata sandi (catat sekarang, tidak akan ditampilkan lagi): {$password}");
        }
    }
}
