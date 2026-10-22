<?php

namespace App\Services;

use App\Enums\HalamanQr;
use App\Enums\PosisiQr;
use App\Exceptions\PdfTidakDapatDiprosesException;
use setasign\Fpdi\FpdiException;

class PdfStamperService
{
    public function __construct(
        private readonly QrCodeService $qr,
        private readonly PdfNormalizer $normalizer,
    ) {}

    /**
     * Menyalin seluruh halaman PDF sumber ke PDF baru dan menempelkan QR Code
     * (vektor) + kode verifikasi pada satu halaman.
     *
     * @param  array{judul?: string, pembuat?: string}  $meta
     */
    public function stempel(
        string $sumber,
        string $tujuan,
        string $url,
        string $kode,
        PosisiQr $posisi,
        HalamanQr $halaman,
        array $meta = [],
    ): void {
        $matrix = $this->qr->matrix($url);

        try {
            $this->tulis($sumber, $tujuan, $matrix, $kode, $posisi, $halaman, $meta);

            return;
        } catch (FpdiException $e) {
            // Lanjut ke jalur cadangan (Ghostscript) di bawah.
            $penyebab = $e;
        }

        $dinormalisasi = tempnam(sys_get_temp_dir(), 'pdfn_').'.pdf';

        try {
            $this->normalizer->turunkanVersi($sumber, $dinormalisasi);
            $this->tulis($dinormalisasi, $tujuan, $matrix, $kode, $posisi, $halaman, $meta);
        } catch (FpdiException) {
            throw new PdfTidakDapatDiprosesException(
                'PDF tidak dapat dibaca. Pastikan file tidak terenkripsi atau terproteksi kata sandi.',
                previous: $penyebab,
            );
        } finally {
            @unlink($dinormalisasi);
        }
    }

    /**
     * @param  array<int, array<int, bool>>  $matrix
     * @param  array{judul?: string, pembuat?: string}  $meta
     */
    private function tulis(
        string $sumber,
        string $tujuan,
        array $matrix,
        string $kode,
        PosisiQr $posisi,
        HalamanQr $halaman,
        array $meta,
    ): void {
        $pdf = new StampPdf('P', 'mm');
        $pdf->SetAutoPageBreak(false);
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetCreator($meta['pembuat'] ?? 'Sistem Penyuratan');

        if (! empty($meta['judul'])) {
            $pdf->SetTitle($this->latin1($meta['judul']));
        }

        $jumlah = $pdf->setSourceFile($sumber);
        $target = $halaman === HalamanQr::Pertama ? 1 : $jumlah;

        for ($i = 1; $i <= $jumlah; $i++) {
            $template = $pdf->importPage($i);
            $ukuran = $pdf->getTemplateSize($template);

            $pdf->AddPage($ukuran['orientation'], [$ukuran['width'], $ukuran['height']]);
            $pdf->useTemplate($template);

            if ($i === $target) {
                $this->gambarStempel($pdf, $ukuran['width'], $ukuran['height'], $matrix, $kode, $posisi);
            }
        }

        $pdf->Output('F', $tujuan);
    }

    /**
     * @param  array<int, array<int, bool>>  $matrix
     */
    private function gambarStempel(StampPdf $pdf, float $w, float $h, array $matrix, string $kode, PosisiQr $posisi): void
    {
        $qrSize = (float) config('penyuratan.qr.size_mm');
        $margin = (float) config('penyuratan.qr.margin_mm');
        $quiet = (float) config('penyuratan.qr.quiet_mm');
        $labelH = 7.0;

        $boxW = $qrSize + 2 * $quiet;
        $boxH = $quiet + $qrSize + $labelH + 0.8;

        $x = match ($posisi->kolom()) {
            0 => $margin,
            1 => ($w - $boxW) / 2,
            default => $w - $margin - $boxW,
        };
        $y = $posisi->atas() ? $margin : $h - $margin - $boxH;

        // Latar putih + bingkai tipis, agar QR tetap terbaca di atas konten apa pun.
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetDrawColor(203, 213, 225);
        $pdf->SetLineWidth(0.15);
        $pdf->Rect($x, $y, $boxW, $boxH, 'DF');

        // Modul QR, digabung per-baris agar file kecil.
        $n = count($matrix);
        $modul = $qrSize / $n;
        $overdraw = 0.02; // tutup celah anti-aliasing antar-modul di sebagian viewer
        $rects = [];

        foreach ($matrix as $baris => $kolom) {
            $c = 0;
            while ($c < $n) {
                if (! $kolom[$c]) {
                    $c++;

                    continue;
                }
                $awal = $c;
                while ($c < $n && $kolom[$c]) {
                    $c++;
                }
                $rects[] = [
                    $x + $quiet + $awal * $modul,
                    $y + $quiet + $baris * $modul,
                    ($c - $awal) * $modul + $overdraw,
                    $modul + $overdraw,
                ];
            }
        }
        $pdf->isiPersegi($rects);

        // Label di bawah QR.
        $pdf->SetTextColor(51, 65, 85);
        $pdf->SetFont('Helvetica', '', 5.6);
        $pdf->SetXY($x, $y + $quiet + $qrSize + 0.6);
        $pdf->Cell($boxW, 2.6, 'Pindai untuk verifikasi keaslian', 0, 2, 'C');
        $pdf->SetFont('Helvetica', 'B', 6.4);
        $pdf->SetX($x);
        $pdf->Cell($boxW, 3.0, 'Kode: '.\App\Models\Surat::formatKode($kode), 0, 0, 'C');
    }

    private function latin1(string $teks): string
    {
        return mb_convert_encoding($teks, 'ISO-8859-1', 'UTF-8');
    }
}
