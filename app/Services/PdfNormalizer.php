<?php

namespace App\Services;

use App\Exceptions\PdfTidakDapatDiprosesException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Cadangan untuk PDF yang tidak bisa dibaca FPDI (umumnya PDF 1.5+ dengan
 * kompresi xref/object stream). Ghostscript menulis ulang ke PDF 1.4.
 */
class PdfNormalizer
{
    public function biner(): ?string
    {
        $dikonfigurasi = config('penyuratan.ghostscript');
        if ($dikonfigurasi) {
            return $dikonfigurasi;
        }

        return (new ExecutableFinder)->find('gs')
            ?? (new ExecutableFinder)->find('gswin64c')
            ?? (new ExecutableFinder)->find('gswin32c');
    }

    public function tersedia(): bool
    {
        return $this->biner() !== null;
    }

    public function turunkanVersi(string $sumber, string $tujuan): void
    {
        $gs = $this->biner();

        if ($gs === null) {
            throw new PdfTidakDapatDiprosesException(
                'Format PDF ini tidak didukung. Simpan ulang PDF dengan kompatibilitas PDF 1.4 '
                .'(Acrobat: Save As Other > Optimized PDF; Word: Simpan sebagai PDF, opsi "Standar"), lalu unggah kembali.'
            );
        }

        $proses = new Process([
            $gs, '-q', '-dNOPAUSE', '-dBATCH', '-dSAFER',
            '-sDEVICE=pdfwrite', '-dCompatibilityLevel=1.4', '-dPDFSETTINGS=/prepress',
            '-dAutoRotatePages=/None',
            '-sOutputFile='.$tujuan, $sumber,
        ]);
        $proses->setTimeout(90);
        $proses->run();

        // Ghostscript keluar dengan kode 0 bahkan untuk PDF berpassword (menghasilkan dokumen kosong),
        // jadi pesan galatnya diperiksa eksplisit supaya tidak pernah menerbitkan surat kosong.
        $galat = $proses->getErrorOutput().$proses->getOutput();
        $gagal = ! $proses->isSuccessful()
            || ! is_file($tujuan)
            || filesize($tujuan) === 0
            || preg_match('/requires a password|Couldn\'t initialise|No pages will be processed|Unrecoverable error/i', $galat) === 1;

        if ($gagal) {
            @unlink($tujuan);

            throw new PdfTidakDapatDiprosesException(
                'PDF tidak dapat diproses. Pastikan file tidak terproteksi kata sandi dan tidak rusak.'
            );
        }

        $halamanSumber = $this->hitungHalaman($gs, $sumber);
        $halamanHasil = $this->hitungHalaman($gs, $tujuan);

        if ($halamanSumber !== null && $halamanHasil !== null && $halamanSumber !== $halamanHasil) {
            @unlink($tujuan);

            throw new PdfTidakDapatDiprosesException(
                'PDF tidak dapat diproses dengan utuh (jumlah halaman berubah saat konversi). Simpan ulang PDF lalu coba lagi.'
            );
        }
    }

    private function hitungHalaman(string $gs, string $berkas): ?int
    {
        $ps = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $berkas);

        $proses = new Process([
            $gs, '-q', '-dNODISPLAY', '-dNOSAFER', '-dNOPAUSE', '-dBATCH',
            '-c', "({$ps}) (r) file runpdfbegin pdfpagecount = quit",
        ]);
        $proses->setTimeout(30);
        $proses->run();

        $keluaran = trim($proses->getOutput());

        return ctype_digit($keluaran) ? (int) $keluaran : null;
    }
}
