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
            'nama_instansi' => ['required', 'string', 'max:255'],
            'jenis_surat_id' => ['required', 'integer', Rule::exists('jenis_surat', 'id')->where('is_active', true)],
            'nomor_surat' => ['required', 'string', 'max:100', Rule::unique('surat', 'nomor_surat')],
            'tanggal_surat' => ['required', 'date'],
            'perihal' => ['required', 'string', 'max:255'],
            'ditujukan_kepada' => ['required', 'string', 'max:255'],
            'penandatangan_id' => ['nullable', 'prohibited'],
            'penandatangan_nama' => $unit ? ['nullable', 'prohibited'] : ['required', 'string', 'max:150'],
            'penandatangan_jabatan' => $unit ? ['nullable', 'prohibited'] : ['required', 'string', 'max:150'],
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
            'nama_instansi' => 'nama instansi',
            'jenis_surat_id' => 'jenis surat',
            'nomor_surat' => 'nomor surat',
            'tanggal_surat' => 'tanggal surat',
            'ditujukan_kepada' => 'ditujukan kepada',
            'penandatangan_id' => 'penandatangan',
            'logo_qr' => 'logo tengah QR',
            'berkas' => 'berkas PDF',
        ];
    }
}