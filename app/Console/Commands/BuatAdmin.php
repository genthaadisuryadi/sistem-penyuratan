<?php

namespace App\Console\Commands;

use App\Enums\RoleAdmin;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class BuatAdmin extends Command
{
    protected $signature = 'admin:buat {nama : Nama lengkap} {email : Alamat email} {--username= : Username (opsional)} {--role=unit : unit, sekretariat, atau superadmin}';

    protected $description = 'Membuat akun admin baru';

    public function handle(): int
    {
        $kata = $this->secret('Kata sandi (min. 10 karakter, huruf besar-kecil + angka)');
        $ulang = $this->secret('Ulangi kata sandi');

        $validator = Validator::make([
            'name' => $this->argument('nama'),
            'jabatan' => $this->ask('Jabatan / posisi'),
            'email' => $this->argument('email'),
            'username' => $this->option('username') ?: null,
            'role' => $this->option('role'),
            'password' => $kata,
            'password_confirmation' => $ulang,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'jabatan' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'username' => ['nullable', 'alpha_dash', 'max:50', 'unique:users,username'],
            'role' => ['required', Rule::enum(RoleAdmin::class)],
            'password' => ['required', 'confirmed', Password::min(10)->mixedCase()->numbers()],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $pesan) {
                $this->error($pesan);
            }

            return self::FAILURE;
        }

        $data = $validator->validated();
        unset($data['password_confirmation']);

        User::create($data + ['is_active' => true]);

        $this->info("Admin {$data['email']} berhasil dibuat.");

        return self::SUCCESS;
    }
}
