<?php

namespace App\Models;

use App\Enums\HalamanQr;
use App\Enums\PosisiQr;
use App\Enums\StatusSurat;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'uuid', 'kode_verifikasi', 'jenis_surat_id', 'nomor_surat', 'perihal', 'ditujukan_kepada', 'tanggal_surat',
    'penandatangan_id', 'penandatangan_nama', 'penandatangan_jabatan', 'qr_posisi', 'qr_halaman',
    'file_asli_path', 'file_final_path', 'file_hash_sha256', 'file_size',
    'status', 'published_at', 'alasan_pencabutan', 'revoked_at', 'revoked_by', 'created_by',
])]
#[Hidden(['id', 'file_asli_path', 'file_final_path'])]
class Surat extends Model
{
    protected $table = 'surat';

    protected function casts(): array
    {
        return [
            'tanggal_surat' => 'date',
            'status' => StatusSurat::class,
            'qr_posisi' => PosisiQr::class,
            'qr_halaman' => HalamanQr::class,
            'published_at' => 'datetime',
            'revoked_at' => 'datetime',
            'last_scanned_at' => 'datetime',
            'file_size' => 'integer',
            'scan_count' => 'integer',
        ];
    }

    /** Semua route model binding memakai UUID — ID auto-increment tidak pernah diekspos. */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function jenisSurat(): BelongsTo
    {
        return $this->belongsTo(JenisSurat::class, 'jenis_surat_id');
    }

    public function penandatangan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penandatangan_id');
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pencabut(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    public function scanLogs(): HasMany
    {
        return $this->hasMany(ScanLog::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', StatusSurat::Publish);
    }

    public function scopeDraf(Builder $query): Builder
    {
        return $query->where('status', StatusSurat::Draf);
    }

    public function scopeDicabut(Builder $query): Builder
    {
        return $query->where('status', StatusSurat::Ditarik);
    }

    public function scopeCari(Builder $query, ?string $kata): Builder
    {
        $kata = trim((string) $kata);

        if ($kata === '') {
            return $query;
        }

        $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $kata).'%';

        return $query->where(function (Builder $q) use ($like, $kata) {
            $q->where('nomor_surat', 'like', $like)
                ->orWhere('perihal', 'like', $like)
                ->orWhere('penandatangan_nama', 'like', $like)
                ->orWhere('kode_verifikasi', self::normalisasiKode($kata));
        });
    }

    public function isAktif(): bool
    {
        return $this->status === StatusSurat::Publish;
    }

    public function isDicabut(): bool
    {
        return $this->status === StatusSurat::Ditarik;
    }

    /** Kode ditampilkan dengan tanda hubung agar mudah dibaca: ABCDE-FGHJK. */
    public function kodeTampil(): string
    {
        return self::formatKode($this->kode_verifikasi);
    }

    public function isDraf(): bool
    {
        return $this->status === StatusSurat::Draf;
    }

    public function urlVerifikasi(): string
    {
        return self::bangunUrlVerifikasiNomor($this->nomor_surat);
    }

    /** URL QR menggunakan nomor surat sesuai format yang diminta, bukan ID internal. */
    public static function bangunUrlVerifikasiNomor(string $nomorSurat): string
    {
        $bagian = array_map(static fn (string $segmen): string => rawurlencode($segmen), explode('/', ltrim($nomorSurat, '/')));

        return rtrim((string) config('penyuratan.verification_base_url'), '/').'/verify/'.implode('/', $bagian);
    }

    /** Dipertahankan untuk tautan lama yang menggunakan UUID. */
    public static function bangunUrlVerifikasi(string $uuid): string
    {
        return rtrim((string) config('penyuratan.verification_base_url'), '/').'/surat/'.$uuid;
    }

    public function namaBerkas(): string
    {
        return Str::slug(str_replace(['/', '\\'], '-', $this->nomor_surat)).'.pdf';
    }

    public static function normalisasiKode(string $input): string
    {
        return Str::upper((string) preg_replace('/[^A-Za-z0-9]/', '', $input));
    }

    public static function formatKode(string $kode): string
    {
        return trim(implode('-', str_split($kode, 5)), '-');
    }
}
