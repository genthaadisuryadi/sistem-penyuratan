<?php

namespace App\Services;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;

class QrCodeService
{
    public function matrix(string $isi): array
    {
        $qr = Encoder::encode($isi, ErrorCorrectionLevel::H());
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

    // Fungsi SVG tetap dibiarkan agar tidak merusak halaman lain (misalnya halaman show.blade.php)
    public function svg(string $isi, int $quiet = 2, ?string $logoBase64 = null, string $color = '#000000'): string
    {
        $rows = $this->matrix($isi);
        $n = count($rows);
        $total = $n + 2 * $quiet;
        $path = '';

        foreach ($rows as $y => $row) {
            $x = 0;
            while ($x < $n) {
                if (! $row[$x]) { $x++; continue; }
                $start = $x;
                while ($x < $n && $row[$x]) { $x++; }
                $path .= sprintf('M%d %dh%dv1h-%dz', $start + $quiet, $y + $quiet, $x - $start, $x - $start);
            }
        }

        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %1$d %1$d" shape-rendering="crispEdges" role="img"><rect width="%1$d" height="%1$d" fill="#fff"/><path d="%2$s" fill="%3$s"/>',
            $total, $path, $color
        );

        if ($logoBase64) {
            $logoSize = $total * 0.15; 
            $logoPos = ($total - $logoSize) / 2;
            $svg .= sprintf(
                '<rect x="%1$f" y="%1$f" width="%2$f" height="%2$f" fill="#fff"/><image x="%1$f" y="%1$f" width="%2$f" height="%2$f" href="%3$s"/>',
                $logoPos, $logoSize, $logoBase64
            );
        }

        $svg .= '</svg>';
        return $svg;
    }

    // FUNGSI BARU: Generate PNG HD untuk didownload dan disisipkan ke Word
    public function png(string $isi, int $quiet = 2, ?string $logoBase64 = null, string $color = '#000000'): string
    {
        $rows = $this->matrix($isi);
        $n = count($rows);
        $scale = 10; // Skala 10 piksel per blok agar PNG tajam (HD) dan tidak pecah saat diprint
        $totalSize = ($n + 2 * $quiet) * $scale;

        // Buat kanvas gambar
        $image = imagecreatetruecolor($totalSize, $totalSize);

        // Ubah warna hex ke format RGB
        $color = ltrim($color, '#');
        if (strlen($color) === 3) {
            $r = hexdec(str_repeat(substr($color, 0, 1), 2));
            $g = hexdec(str_repeat(substr($color, 1, 1), 2));
            $b = hexdec(str_repeat(substr($color, 2, 1), 2));
        } else {
            $r = hexdec(substr($color, 0, 2));
            $g = hexdec(substr($color, 2, 2));
            $b = hexdec(substr($color, 4, 2));
        }

        $bgColor = imagecolorallocate($image, 255, 255, 255);
        $fgColor = imagecolorallocate($image, $r, $g, $b);

        // Isi latar belakang dengan warna putih
        imagefilledrectangle($image, 0, 0, $totalSize, $totalSize, $bgColor);

        // Gambar matriks QR Code
        foreach ($rows as $y => $row) {
            foreach ($row as $x => $isDark) {
                if ($isDark) {
                    $x1 = ($x + $quiet) * $scale;
                    $y1 = ($y + $quiet) * $scale;
                    $x2 = $x1 + $scale - 1;
                    $y2 = $y1 + $scale - 1;
                    imagefilledrectangle($image, $x1, $y1, $x2, $y2, $fgColor);
                }
            }
        }

        // Terapkan logo di tengah jika ada
        if ($logoBase64) {
            $logoData = explode(',', $logoBase64);
            $base64String = isset($logoData[1]) ? $logoData[1] : $logoData[0];
            $logoContent = base64_decode($base64String);
            
            if ($logoContent !== false) {
                $logoImg = @imagecreatefromstring($logoContent);

                if ($logoImg) {
                    imagealphablending($image, true);
                    
                    $logoWidth = imagesx($logoImg);
                    $logoHeight = imagesy($logoImg);

                    // Ukuran logo 20% dari luas total QR Code
                    $targetLogoSize = (int) ($totalSize * 0.20);
                    $targetX = ($totalSize - $targetLogoSize) / 2;
                    $targetY = ($totalSize - $targetLogoSize) / 2;

                    // Buat kotak putih penahan (margin) di belakang logo agar QR code tidak menembus
                    imagefilledrectangle($image, $targetX, $targetY, $targetX + $targetLogoSize, $targetY + $targetLogoSize, $bgColor);

                    // Tempel logo ke dalam kanvas QR Code
                    imagecopyresampled($image, $logoImg, $targetX, $targetY, 0, 0, $targetLogoSize, $targetLogoSize, $logoWidth, $logoHeight);
                    imagedestroy($logoImg);
                }
            }
        }

        // Tangkap output gambar mentah ke dalam string binary
        ob_start();
        imagepng($image);
        $pngData = ob_get_clean();
        imagedestroy($image);

        return $pngData;
    }
}