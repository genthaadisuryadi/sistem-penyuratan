<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Request;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function surat(): BelongsTo
    {
        return $this->belongsTo(Surat::class);
    }

    public static function catat(string $aksi, ?User $user = null, ?Surat $surat = null, array $metadata = []): self
    {
        return self::create([
            'user_id' => $user?->getKey(),
            'surat_id' => $surat?->getKey(),
            'action' => $aksi,
            'metadata' => $metadata ?: null,
            'ip' => Request::ip(),
            'created_at' => now(),
        ]);
    }

    public function label(): string
    {
        return match ($this->action) {
            'terbit' => 'Surat dipublikasikan',
            'buat_draf' => 'Draf dibuat',
            'publish' => 'Surat dipublikasikan',
            'cabut' => 'Status surat ditarik',
            'unduh' => 'Berkas diunduh',
            'login' => 'Masuk ke sistem',
            default => $this->action,
        };
    }
}
