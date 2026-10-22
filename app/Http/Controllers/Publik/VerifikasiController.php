<?php

namespace App\Http\Controllers\Publik;

use App\Http\Controllers\Controller;
use App\Jobs\CatatPemindaian;
use App\Models\Surat;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VerifikasiController extends Controller
{
    public function beranda(): View
    {
        return view('verifikasi.beranda');
    }

    public function verifikasiNomor(Request $request, string $nomor_surat): Response
    {
        $nomor = trim($nomor_surat);
        $surat = Surat::where('nomor_surat', $nomor)->first();

        if (! $surat) {
            return $this->tanpaCache(response()->view('verifikasi.tidak-ditemukan', [], 404));
        }

        return $this->tampilkanSurat($request, $surat);
    }

    public function cari(Request $request): RedirectResponse
    {
        $data = $request->validate(['kode' => ['required', 'string', 'max:30']], [], ['kode' => 'kode verifikasi']);

        $kode = Surat::normalisasiKode($data['kode']);
        $surat = strlen($kode) === 10 ? Surat::where('kode_verifikasi', $kode)->first() : null;

        if (! $surat) {
            return back()->withInput()->withErrors(['kode' => 'Kode tidak ditemukan. Periksa kembali penulisan kode di bawah QR pada surat Anda.']);
        }

        return redirect()->route('verifikasi.show', $surat->uuid);
    }

    public function show(Request $request, string $uuid): Response
    {
        $surat = Surat::where('uuid', $uuid)->first();

        if (! $surat) {
            return $this->tanpaCache(response()->view('verifikasi.tidak-ditemukan', [], 404));
        }

        return $this->tampilkanSurat($request, $surat);
    }

    public function unduh(string $uuid): StreamedResponse
    {
        $surat = Surat::where('uuid', $uuid)->firstOrFail();

        abort_unless($surat->isAktif(), 403, 'Surat ini belum dipublikasikan atau sudah ditarik, sehingga berkasnya tidak dapat diunduh.');
        abort_unless(Storage::disk('surat')->exists($surat->file_final_path), 404);

        return Storage::disk('surat')->download($surat->file_final_path, $surat->namaBerkas(), [
            'Content-Type' => 'application/pdf',
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }

    private function tampilkanSurat(Request $request, Surat $surat): Response
    {
        $ua = $request->userAgent();
        $catat = new CatatPemindaian(
            suratId: $surat->getKey(),
            ipHash: hash_hmac('sha256', (string) $request->ip(), (string) config('app.key')),
            userAgent: $ua,
            bot: $ua === null || $ua === '' || preg_match(config('penyuratan.scan.bot_pattern'), $ua) === 1,
            status: $surat->status,
            waktu: CarbonImmutable::now(),
        );
        defer(fn () => $catat->handle());

        return $this->tanpaCache(response()->view('verifikasi.show', ['surat' => $surat]));
    }

    /** Status keabsahan bisa berubah kapan saja, jadi halaman ini tidak boleh dicache. */
    private function tanpaCache(Response $response): Response
    {
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, private');
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

        return $response;
    }
}
