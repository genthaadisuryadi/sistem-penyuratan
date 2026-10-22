<?php

namespace App\Models;

use App\Enums\RoleAdmin;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

#[Fillable(['name', 'jabatan', 'email', 'username', 'password', 'role', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'role' => RoleAdmin::class,
            'is_active' => 'boolean',
        ];
    }

    public function surat(): HasMany
    {
        return $this->hasMany(Surat::class, 'created_by');
    }

    public function isSuperadmin(): bool
    {
        return $this->role === RoleAdmin::Superadmin;
    }

    public function isSekretariat(): bool
    {
        return $this->role === RoleAdmin::Sekretariat;
    }

    public function isUnit(): bool
    {
        return $this->role === RoleAdmin::Unit;
    }

    public function dapatKelolaPengguna(): bool
    {
        return $this->isSuperadmin() || $this->isSekretariat();
    }

    public function dapatKelolaSemuaSurat(): bool
    {
        return $this->isSuperadmin() || $this->isSekretariat();
    }

    public function inisial(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $kata) => Str::upper(Str::substr($kata, 0, 1)))
            ->implode('');
    }
}
