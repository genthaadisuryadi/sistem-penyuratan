<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

/** Memastikan isi berkas benar-benar PDF (cek magic bytes), bukan sekadar ekstensi/MIME dari klien. */
class BerkasPdf implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile || ! $value->isValid()) {
            $fail('Berkas tidak valid.');

            return;
        }

        $handle = @fopen($value->getRealPath(), 'rb');
        $awal = $handle ? (string) fread($handle, 1024) : '';
        if ($handle) {
            fclose($handle);
        }

        if (! str_contains($awal, '%PDF-')) {
            $fail('Isi berkas bukan dokumen PDF yang sah.');
        }
    }
}
