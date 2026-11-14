<?php

namespace App\Http\Requests;

use App\Enums\RoleAdmin;
use App\Models\Surat;
use App\Rules\BerkasPdf;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSuratRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Surat::class) ?? false;
    }

    public function rules(): array
    {
        $unit = $this->user()?->role === RoleAdmin::Unit;

        return [
            'jenis_surat_id' => ['required', 'integer', Rule::exists('jenis_surat', 'id')->where('is_active', true)],
            'nomor_surat' => ['required', 'string', 'max:100', Rule::unique('surat', 'nomor_surat')],
            'tanggal_surat' => ['required', 'date'],
            'perihal' => ['required', 'string', 'max:255'],
            'ditujukan_kepada' => ['required', 'string', 'max:255'],
            'penandatangan_id' => ['nullable', 'prohibited'],
            
            // Nama penandatangan untuk Unit tidak dikirim dari form, jadi tetap 'prohibited' (aman dari manipulasi)
            'penandatangan_nama' => $unit ? ['nullable', 'prohibited'] : ['required', 'string', 'max:150'],
            
            // PERBAIKAN: Jabatan sekarang 'required' (wajib) untuk semua tipe akun. 
            // Admin mengetik manual, sedangkan Unit mengirimkan dari dropdown.
            'penandatangan_jabatan' => ['required', 'string', 'max:150'],
            
            'qr_color' => ['nullable', 'string', 'max:7'], 
            'logo_qr' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            
            'berkas' => [
                'required', 'file',
                'mimetypes:application/pdf',
                'max:'.config('penyuratan.upload_max_kb'),
                new BerkasPdf,
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'jenis_surat_id' => 'jenis surat',
            'nomor_surat' => 'nomor surat',
            'tanggal_surat' => 'tanggal surat',
            'ditujukan_kepada' => 'ditujukan kepada',
            'penandatangan_id' => 'penandatangan',
            'penandatangan_jabatan' => 'jabatan penandatangan',
            'qr_color' => 'warna QR',
            'logo_qr' => 'logo tengah QR',
            'berkas' => 'berkas PDF',
        ];
    }
}