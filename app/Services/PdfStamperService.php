<?php

namespace App\Services;

use App\Enums\HalamanQr;
use App\Enums\PosisiQr;
use App\Exceptions\PdfTidakDapatDiprosesException;
use setasign\Fpdi\FpdiException;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PdfStamperService
{
    public function __construct(
        private readonly QrCodeService $qr,
        private readonly PdfNormalizer $normalizer,
    ) {}

    /**
     * @param  array{judul?: string, pembuat?: string, logo_qr?: \Illuminate\Http\UploadedFile}  $meta
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
        try {
            $this->tulis($sumber, $tujuan, $url, $kode, $posisi, $halaman, $meta);
            return;
        } catch (FpdiException $e) {
            $penyebab = $e;
        }

        $dinormalisasi = tempnam(sys_get_temp_dir(), 'pdfn_').'.pdf';

        try {
            $this->normalizer->turunkanVersi($sumber, $dinormalisasi);
            $this->tulis($dinormalisasi, $tujuan, $url, $kode, $posisi, $halaman, $meta);
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
     * @param  array{judul?: string, pembuat?: string, logo_qr?: \Illuminate\Http\UploadedFile}  $meta
     */
    private function tulis(
        string $sumber,
        string $tujuan,
        string $url,
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

        // PERBAIKAN: Persiapkan file PNG sementara untuk QR Code
        $tempQrPath = tempnam(sys_get_temp_dir(), 'qr_').'.png';
        
        $qrBuilder = QrCode::format('png')->size(300)->margin(1)->errorCorrection('H');
        
        // Sisipkan logo ke QR jika ada
        if (isset($meta['logo_qr'])) {
            $qrBuilder->merge($meta['logo_qr']->getRealPath(), 0.25, true);
        }
        
        // Simpan QR image ke file temporary
        file_put_contents($tempQrPath, $qrBuilder->generate($url));

        for ($i = 1; $i <= $jumlah; $i++) {
            $template = $pdf->importPage($i);
            $ukuran = $pdf->getTemplateSize($template);

            $pdf->AddPage($ukuran['orientation'], [$ukuran['width'], $ukuran['height']]);
            $pdf->useTemplate($template);

            if ($i === $target) {
                // Lempar path gambar PNG untuk ditempel ke dokumen
                $this->gambarStempelDenganLogo($pdf, $ukuran['width'], $ukuran['height'], $tempQrPath, $kode, $posisi);
            }
        }

        $pdf->Output('F', $tujuan);
        
        // Hapus file temporary QR
        @unlink($tempQrPath);
    }

    private function gambarStempelDenganLogo(StampPdf $pdf, float $w, float $h, string $qrImagePath, string $kode, PosisiQr $posisi): void
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

        // Latar putih + bingkai tipis
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetDrawColor(203, 213, 225);
        $pdf->SetLineWidth(0.15);
        $pdf->Rect($x, $y, $boxW, $boxH, 'DF');

        // PERBAIKAN: Cetak gambar PNG QR Code (dengan atau tanpa logo) ke dalam PDF
        // Parameter: file, X, Y, Width, Height, Type
        $pdf->Image($qrImagePath, $x + $quiet, $y + $quiet, $qrSize, $qrSize, 'PNG');

        // Label di bawah QR.
        $pdf->SetTextColor(51, 65, 85);
        $pdf->SetFont('Helvetica', '', 5.6);
        $pdf->SetXY($x, $y + $quiet + $qrSize + 0.6);
        $pdf->Cell($boxW, 2.6, 'Pindai untuk verifikasi keaslian', 0, 2, 'C');
        
        // Hapus kode unik, sekarang instruksikan pencarian pakai nomor surat
        $pdf->SetFont('Helvetica', 'B', 6.4);
        $pdf->SetX($x);
        $pdf->Cell($boxW, 3.0, 'Sistem Verifikasi Resmi', 0, 0, 'C');
    }

    private function latin1(string $teks): string
    {
        return mb_convert_encoding($teks, 'ISO-8859-1', 'UTF-8');
    }
}