<?php

namespace App\Jobs;

use App\Enums\StatusSurat;
use App\Models\ScanLog;
use App\Models\Surat;
use Carbon\CarbonImmutable;

/**
 * Dijalankan SETELAH respons terkirim (lewat defer() di controller), sehingga halaman
 * publik tetap cepat dan tidak butuh queue worker.
 */
class CatatPemindaian
{
    public function __construct(
        public readonly int $suratId,
        public readonly string $ipHash,
        public readonly ?string $userAgent,
        public readonly bool $bot,
        public readonly StatusSurat $status,
        public readonly CarbonImmutable $waktu,
    ) {}

    public function handle(): void
    {
        if (! $this->bot) {
            $jendela = (int) config('penyuratan.scan.dedupe_seconds');

            $duplikat = ScanLog::query()
                ->where('surat_id', $this->suratId)
                ->where('ip_hash', $this->ipHash)
                ->where('is_bot', false)
                ->where('scanned_at', '>=', $this->waktu->subSeconds($jendela))
                ->exists();

            if ($duplikat) {
                return;
            }
        }

        ScanLog::create([
            'surat_id' => $this->suratId,
            'scanned_at' => $this->waktu,
            'status_saat_scan' => $this->status->value,
            'ip_hash' => $this->ipHash,
            'user_agent' => $this->userAgent ? mb_substr($this->userAgent, 0, 255) : null,
            'is_bot' => $this->bot,
        ]);

        if (! $this->bot) {
            Surat::whereKey($this->suratId)->increment('scan_count', 1, ['last_scanned_at' => $this->waktu]);
        }
    }
}
