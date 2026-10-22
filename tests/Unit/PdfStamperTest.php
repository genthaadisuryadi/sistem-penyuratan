<?php

namespace Tests\Unit;

use App\Enums\HalamanQr;
use App\Enums\PosisiQr;
use App\Exceptions\PdfTidakDapatDiprosesException;
use App\Services\PdfNormalizer;
use App\Services\PdfStamperService;
use App\Services\QrCodeService;
use setasign\Fpdi\Fpdi;
use Tests\Support\BuatPdf;
use Tests\TestCase;

class PdfStamperTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dir = sys_get_temp_dir().'/stamper_'.uniqid();
        mkdir($this->dir);
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->dir.'/*'));
        rmdir($this->dir);
        parent::tearDown();
    }

    private function stempel(string $isiPdf, string $nama = 'in.pdf'): string
    {
        file_put_contents("{$this->dir}/$nama", $isiPdf);
        app(PdfStamperService::class)->stempel(
            "{$this->dir}/$nama", "{$this->dir}/out.pdf", 'https://surat.contoh.test/surat/8f7d9a2b-1c34-4d5e-9a1b-0c2d3e4f5a6b',
            'ABCDEFGHJK', PosisiQr::KananBawah, HalamanQr::Terakhir,
        );

        return "{$this->dir}/out.pdf";
    }

    public function test_jumlah_halaman_dan_ukuran_tetap(): void
    {
        $out = $this->stempel(BuatPdf::isi(3));

        $pdf = new Fpdi;
        $this->assertSame(3, $pdf->setSourceFile($out));
        $size = $pdf->getTemplateSize($pdf->importPage(1));
        $this->assertEqualsWithDelta(210, $size['width'], 0.5);
        $this->assertEqualsWithDelta(297, $size['height'], 0.5);
        $this->assertGreaterThan(filesize("{$this->dir}/in.pdf"), filesize($out));
    }

    public function test_pdf_15_terkompresi_diproses_lewat_ghostscript(): void
    {
        $normalizer = app(PdfNormalizer::class);
        if (! $normalizer->tersedia() || ! trim((string) shell_exec('command -v qpdf'))) {
            $this->markTestSkipped('Ghostscript/qpdf tidak tersedia.');
        }

        file_put_contents("{$this->dir}/lama.pdf", BuatPdf::isi(2));
        shell_exec("qpdf --object-streams=generate --force-version=1.5 {$this->dir}/lama.pdf {$this->dir}/baru.pdf");

        $out = $this->stempel(file_get_contents("{$this->dir}/baru.pdf"), 'baru2.pdf');

        $this->assertSame(2, (new Fpdi)->setSourceFile($out));
    }

    public function test_berkas_rusak_menghasilkan_pesan_ramah(): void
    {
        $this->expectException(PdfTidakDapatDiprosesException::class);

        $this->stempel('%PDF-1.4 rusak total');
    }

    public function test_matriks_qr_persegi_dan_svg_valid(): void
    {
        $qr = app(QrCodeService::class);
        $m = $qr->matrix('https://surat.contoh.test/surat/8f7d9a2b-1c34-4d5e-9a1b-0c2d3e4f5a6b');

        $this->assertGreaterThanOrEqual(25, count($m));
        $this->assertCount(count($m), $m[0]);
        $this->assertTrue($m[0][0] && $m[0][6], 'pola finder kiri-atas harus ada');

        $svg = $qr->svg('halo');
        $this->assertStringStartsWith('<svg', $svg);
        $this->assertNotFalse(simplexml_load_string($svg));
    }
}
