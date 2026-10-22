<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisSurat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class JenisSuratController extends Controller
{
    public function index(): View
    {
        return view('admin.jenis-surat.index', [
            'jenis' => JenisSurat::withCount('surat')->orderBy('nama')->paginate(15),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge(['kode' => strtoupper((string) $request->input('kode', ''))]);
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100', 'unique:jenis_surat,nama'],
            'kode' => ['required', 'string', 'max:20', 'alpha_dash:ascii', 'uppercase', 'unique:jenis_surat,kode'],
        ]);

        JenisSurat::create($data + ['is_active' => true]);

        return back()->with('status', 'Jenis surat berhasil ditambahkan.');
    }

    public function update(Request $request, JenisSurat $jenisSurat): RedirectResponse
    {
        $request->merge(['kode' => strtoupper((string) $request->input('kode', ''))]);
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100', Rule::unique('jenis_surat', 'nama')->ignore($jenisSurat)],
            'kode' => ['required', 'string', 'max:20', 'alpha_dash:ascii', 'uppercase', Rule::unique('jenis_surat', 'kode')->ignore($jenisSurat)],
            'is_active' => ['required', 'boolean'],
        ]);

        $jenisSurat->update($data);

        return back()->with('status', 'Jenis surat berhasil diperbarui.');
    }

    public function destroy(JenisSurat $jenisSurat): RedirectResponse
    {
        if ($jenisSurat->surat()->exists()) {
            return back()->withErrors(['jenis_surat' => 'Jenis ini sudah digunakan oleh surat. Nonaktifkan saja agar riwayat tetap utuh.']);
        }

        $jenisSurat->delete();

        return back()->with('status', 'Jenis surat berhasil dihapus.');
    }
}
