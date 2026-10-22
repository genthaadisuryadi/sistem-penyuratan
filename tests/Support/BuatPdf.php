<?php

namespace Tests\Support;

use FPDF;

class BuatPdf
{
    /** Membuat PDF sederhana n halaman dan mengembalikan isinya (byte). */
    public static function isi(int $halaman = 2): string
    {
        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->SetFont('Helvetica', '', 14);

        for ($i = 1; $i <= $halaman; $i++) {
            $pdf->AddPage();
            $pdf->Cell(0, 10, "Halaman $i - Draf surat");
        }

        return $pdf->Output('S');
    }
}
