<?php

namespace App\Services;

use App\Enums\HalamanQr;
use App\Enums\PosisiQr;
use App\Enums\StatusSurat;
use App\Models\AuditLog;
use App\Models\Surat;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class SuratPublisher
{
    /** Tanpa 0/O/1/I agar mudah diketik ulang dari kertas. */
    private const ALFABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public function __construct(private readonly PdfStamperService $stamper) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function terbitkan(User $admin, array $data, UploadedFile $berkas): Surat
    {
        $disk = Storage::disk('surat');
        $uuid = (string) Str::uuid(); // UUID v4 (acak), bukan v7 yang berurutan waktu
        $kode = $this->buatKode();

        $disk->makeDirectory('asli');
        $disk->makeDirectory('final');

        $pathAsli = 'asli/'.Str::random(40).'.pdf';
        $pathFinal = 'final/'.Str::random(40).'.pdf';

        try {
            $disk->putFileAs('asli', $berkas, basename($pathAsli));

            $this->stamper->stempel(
                sumber: $disk->path($pathAsli),
                tujuan: $disk->path($pathFinal),
                url: Surat::bangunUrlVerifikasiNomor($data['nomor_surat']),
                kode: $kode,
                posisi: PosisiQr::from($data['qr_posisi']),
                halaman: HalamanQr::from($data['qr_halaman']),
                meta: [
                    'judul' => $data['perihal'],
                    // PERBAIKAN: Mengambil metadata pembuat PDF dari input nama instansi
                    'pembuat' => $data['nama_instansi'], 
                ],
            );

            return DB::transaction(function () use ($admin, $data, $uuid, $kode, $pathAsli, $pathFinal, $disk) {
                $surat = Surat::create([
                    'uuid' => $uuid,
                    'kode_verifikasi' => $kode,
                    // PERBAIKAN: Menyisipkan input nama instansi ke database
                    'nama_instansi' => $data['nama_instansi'], 
                    'jenis_surat_id' => $data['jenis_surat_id'],
                    'nomor_surat' => $data['nomor_surat'],
                    'perihal' => $data['perihal'],
                    'ditujukan_kepada' => $data['ditujukan_kepada'],
                    'penandatangan_id' => $data['penandatangan_id'] ?? null,
                    'tanggal_surat' => $data['tanggal_surat'],
                    'penandatangan_nama' => $data['penandatangan_nama'],
                    'penandatangan_jabatan' => $data['penandatangan_jabatan'],
                    'qr_posisi' => $data['qr_posisi'],
                    'qr_halaman' => $data['qr_halaman'],
                    'file_asli_path' => $pathAsli,
                    'file_final_path' => $pathFinal,
                    'file_hash_sha256' => hash_file('sha256', $disk->path($pathFinal)),
                    'file_size' => filesize($disk->path($pathFinal)),
                    'status' => StatusSurat::from($data['status'] ?? StatusSurat::Draf->value),
                    'published_at' => ($data['status'] ?? StatusSurat::Draf->value) === StatusSurat::Publish->value ? now() : null,
                    'created_by' => $admin->getKey(),
                ]);

                AuditLog::catat($surat->status === StatusSurat::Publish ? 'terbit' : 'buat_draf', $admin, $surat, ['nomor_surat' => $surat->nomor_surat]);

                return $surat;
            });
        } catch (Throwable $e) {
            $disk->delete([$pathAsli, $pathFinal]);

            throw $e;
        }
    }

    public function publish(Surat $surat, User $admin): bool
    {
        return DB::transaction(function () use ($surat, $admin) {
            $terkini = Surat::whereKey($surat->getKey())->lockForUpdate()->firstOrFail();

            if ($terkini->status !== StatusSurat::Draf) {
                return false;
            }

            $terkini->update([
                'status' => StatusSurat::Publish,
                'published_at' => now(),
            ]);

            AuditLog::catat('publish', $admin, $terkini, ['nomor_surat' => $terkini->nomor_surat]);
            $surat->refresh();

            return true;
        });
    }

    /** @return bool false bila surat sudah berstatus ditarik */
    public function cabut(Surat $surat, User $admin, string $alasan): bool
    {
        return DB::transaction(function () use ($surat, $admin, $alasan) {
            $terkini = Surat::whereKey($surat->getKey())->lockForUpdate()->firstOrFail();

            if ($terkini->status === StatusSurat::Ditarik) {
                return false;
            }

            $terkini->update([
                'status' => StatusSurat::Ditarik,
                'alasan_pencabutan' => $alasan,
                'revoked_at' => now(),
                'revoked_by' => $admin->getKey(),
            ]);

            AuditLog::catat('cabut', $admin, $terkini, ['alasan' => $alasan]);

            $surat->refresh();

            return true;
        });
    }

    private function buatKode(): string
    {
        do {
            $kode = '';
            for ($i = 0; $i < 10; $i++) {
                $kode .= self::ALFABET[random_int(0, strlen(self::ALFABET) - 1)];
            }
        } while (Surat::where('kode_verifikasi', $kode)->exists());

        return $kode;
    }
}