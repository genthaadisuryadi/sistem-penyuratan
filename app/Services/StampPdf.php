<?php

namespace App\Services;

use setasign\Fpdi\Fpdi;

/** FPDI dengan satu tambahan: mengisi banyak persegi dalam SATU operasi isi (tanpa celah antar-modul). */
class StampPdf extends Fpdi
{
    /**
     * @param  array<int, array{0: float, 1: float, 2: float, 3: float}>  $rects  [x, y, w, h] dalam mm
     */
    public function isiPersegi(array $rects): void
    {
        $ops = '';
        foreach ($rects as [$x, $y, $w, $h]) {
            $ops .= sprintf(
                '%.3F %.3F %.3F %.3F re ',
                $x * $this->k,
                ($this->h - $y - $h) * $this->k,
                $w * $this->k,
                $h * $this->k,
            );
        }

        if ($ops !== '') {
            $this->_out('0 g '.$ops.'f');
        }
    }
}
