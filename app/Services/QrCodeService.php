<?php

namespace App\Services;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;

/**
 * Membuat matriks QR Code murni (tanpa ekstensi GD/Imagick) sehingga bisa
 * digambar sebagai vektor di PDF maupun SVG di halaman web.
 */
class QrCodeService
{
    /**
     * @return array<int, array<int, bool>> baris => kolom => modul gelap?
     */
    public function matrix(string $isi): array
    {
        $qr = Encoder::encode($isi, ErrorCorrectionLevel::M());
        $byteMatrix = $qr->getMatrix();

        $rows = [];
        for ($y = 0; $y < $byteMatrix->getHeight(); $y++) {
            $row = [];
            for ($x = 0; $x < $byteMatrix->getWidth(); $x++) {
                $row[] = $byteMatrix->get($x, $y) === 1;
            }
            $rows[] = $row;
        }

        return $rows;
    }

    /** SVG hitam di atas putih lengkap dengan quiet zone (untuk pratinjau di dasbor). */
    public function svg(string $isi, int $quiet = 2): string
    {
        $rows = $this->matrix($isi);
        $n = count($rows);
        $total = $n + 2 * $quiet;

        $path = '';
        foreach ($rows as $y => $row) {
            $x = 0;
            while ($x < $n) {
                if (! $row[$x]) {
                    $x++;

                    continue;
                }
                $start = $x;
                while ($x < $n && $row[$x]) {
                    $x++;
                }
                $path .= sprintf('M%d %dh%dv1h-%dz', $start + $quiet, $y + $quiet, $x - $start, $x - $start);
            }
        }

        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %1$d %1$d" shape-rendering="crispEdges" role="img" aria-label="QR Code verifikasi"><rect width="%1$d" height="%1$d" fill="#fff"/><path d="%2$s" fill="#000"/></svg>',
            $total,
            $path,
        );
    }
}
