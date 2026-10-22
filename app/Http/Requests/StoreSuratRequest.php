<?php

namespace App\Http\Requests;

use App\Enums\RoleAdmin;
use App\Enums\StatusSurat;
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
            'penandatangan_nama' => $unit ? ['nullable', 'prohibited'] : ['required', 'string', 'max:150'],
            'penandatangan_jabatan' => $unit ? ['nullable', 'prohibited'] : ['required', 'string', 'max:150'],
            'status' => ['required', Rule::in([StatusSurat::Draf->value, StatusSurat::Publish->value])],
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
            'berkas' => 'berkas PDF',
        ];
    }
}
