<?php

namespace App\Services;

use App\Enums\HalamanQr;
use App\Enums\PosisiQr;
use App\Exceptions\PdfTidakDapatDiprosesException;
use setasign\Fpdi\FpdiException;
use Illuminate\Http\UploadedFile;

class PdfStamperService
{
    public function __construct(
        private readonly QrCodeService $qr,
        private readonly PdfNormalizer $normalizer,
    ) {}

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
                $this->gambarStempel($pdf, $ukuran['width'], $ukuran['height'], $matrix, $kode, $posisi, $meta);
            }
        }

        $pdf->Output('F', $tujuan);
    }

    private function gambarStempel(StampPdf $pdf, float $w, float $h, array $matrix, string $kode, PosisiQr $posisi, array $meta): void
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

        // Cetak background putih & garis tepi
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetDrawColor(203, 213, 225);
        $pdf->SetLineWidth(0.15);
        $pdf->Rect($x, $y, $boxW, $boxH, 'DF');

        // PERBAIKAN: Ambil warna HEX dan atur warna pengisi (FillColor)
        $hex = ltrim($meta['qr_color'] ?? '#000000', '#');
        if (strlen($hex) == 6) {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
            $pdf->SetFillColor($r, $g, $b);
        } else {
            $pdf->SetFillColor(0, 0, 0);
        }

        $n = count($matrix);
        $modul = $qrSize / $n;
        $overdraw = 0.02; 

        // PERBAIKAN UTAMA: Gambar matriks QR dengan "Rect" secara langsung
        // untuk mengabaikan fungsi isiPersegi() yang mengunci warna ke hitam
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
                
                $pdf->Rect(
                    $x + $quiet + $awal * $modul,
                    $y + $quiet + $baris * $modul,
                    ($c - $awal) * $modul + $overdraw,
                    $modul + $overdraw,
                    'F' // Parameter 'F' mewajibkan PDF menggunakan warna dari SetFillColor
                );
            }
        }

        // Eksekusi logo tengah (jika ada)
        $logoPath = null;
        $logoExt = null;

        if (!empty($meta['logo_path']) && file_exists($meta['logo_path'])) {
            $logoPath = $meta['logo_path'];
            $logoExt = strtoupper(pathinfo($logoPath, PATHINFO_EXTENSION));
        } elseif (isset($meta['logo_qr']) && $meta['logo_qr'] instanceof UploadedFile) {
            $logoPath = $meta['logo_qr']->getRealPath();
            $logoExt = strtoupper($meta['logo_qr']->extension());
        }

        if ($logoPath) {
            if ($logoExt === 'JPG') {
                $logoExt = 'JPEG';
            }
            
            $logoSize = $qrSize * 0.15; 
            $logoX = $x + $quiet + ($qrSize - $logoSize) / 2;
            $logoY = $y + $quiet + ($qrSize - $logoSize) / 2;

            $pdf->SetFillColor(255, 255, 255);
            $pdf->Rect($logoX, $logoY, $logoSize, $logoSize, 'F');
            $pdf->Image($logoPath, $logoX, $logoY, $logoSize, $logoSize, $logoExt);
        }

        // Teks footer
        $pdf->SetTextColor(51, 65, 85);
        $pdf->SetFont('Helvetica', '', 5.6);
        $pdf->SetXY($x, $y + $quiet + $qrSize + 0.6);
        $pdf->Cell($boxW, 2.6, 'Pindai untuk verifikasi keaslian', 0, 2, 'C');
        
        $pdf->SetFont('Helvetica', 'B', 6.4);
        $pdf->SetX($x);
        $pdf->Cell($boxW, 3.0, 'Sistem Verifikasi Resmi', 0, 0, 'C');
    }

    private function latin1(string $teks): string
    {
        return mb_convert_encoding($teks, 'ISO-8859-1', 'UTF-8');
    }
}