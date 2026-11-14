<?php

namespace App\Http\Controllers\Admin;

use App\Enums\HalamanQr;
use App\Enums\PosisiQr;
use App\Enums\RoleAdmin;
use App\Enums\StatusSurat;
use App\Http\Controllers\Controller;
use App\Http\Requests\RevokeSuratRequest;
use App\Http\Requests\StoreSuratRequest;
use App\Models\AuditLog;
use App\Models\JenisSurat;
use App\Models\Surat;
use App\Services\QrCodeService;
use App\Services\SuratPublisher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class SuratController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Surat::class);

        $user = $request->user();
        $status = StatusSurat::tryFrom((string) $request->query('status'));
        
        $daftar = Surat::query()
            ->with(['pembuat:id,name', 'jenisSurat:id,nama,kode'])
            ->when(! $user->dapatKelolaSemuaSurat(), fn ($q) => $q->where('created_by', $user->id))
            ->cari($request->query('q'))
            ->when($status, fn ($q) => $q->where('status', $status->value))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.surat.index', [
            'daftar' => $daftar,
            'q' => (string) $request->query('q'),
            'status' => $status,
        ]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', Surat::class);

        return view('admin.surat.create', [
            'maksKb' => config('penyuratan.upload_max_kb'),
            'jenisSurat' => JenisSurat::query()->where('is_active', true)->orderBy('nama')->get(),
            'isUnit' => $request->user()->role === RoleAdmin::Unit,
        ]);
    }

    public function previewQr(Request $request, QrCodeService $qr): Response
    {
        $data = $request->validate([
            'nomor_surat' => ['required', 'string', 'max:100'],
            'jenis_surat_id' => ['required', 'integer', 'exists:jenis_surat,id'],
            'qr_color' => ['nullable', 'string'],
            'logo_qr' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
        ]);

        $jenis = JenisSurat::query()->whereKey($data['jenis_surat_id'])->where('is_active', true)->exists();
        abort_unless($jenis, 422, 'Pilih jenis surat yang aktif.');

        $url = Surat::bangunUrlVerifikasiNomor($data['nomor_surat']);
        $logoBase64 = null;
        $color = $data['qr_color'] ?? '#000000';

        if ($request->hasFile('logo_qr')) {
            $file = $request->file('logo_qr');
            $logoBase64 = 'data:' . $file->getMimeType() . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));
        }

        // PERUBAHAN: Ekspor menjadi PNG dan ubah Content-Type ke image/png
        return response($qr->png($url, 2, $logoBase64, $color))
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'no-store');
    }

    public function store(StoreSuratRequest $request, SuratPublisher $publisher): RedirectResponse
    {
        set_time_limit(180);
        $data = $request->safe()->except('berkas');
        $user = $request->user();

        $namaInstansi = '';
        if (Storage::disk('local')->exists('pengaturan_sistem.json')) {
            $config = json_decode(Storage::disk('local')->get('pengaturan_sistem.json'), true);
            if (!empty($config['nama_instansi'])) {
                $namaInstansi = $config['nama_instansi'];
            }
        }
        
        if ($namaInstansi === '') {
            return back()->withInput()->withErrors(['berkas' => 'Nama Instansi penerbit belum diatur. Silakan isi terlebih dahulu di menu Pengaturan Sistem.']);
        }

        $data['nama_instansi'] = $namaInstansi;

        if ($request->hasFile('logo_qr')) {
            $data['logo_qr'] = $request->file('logo_qr');
        }

        $data['qr_posisi'] = PosisiQr::KananBawah->value;
        $data['qr_halaman'] = HalamanQr::Terakhir->value;
        $data['status'] = StatusSurat::Draf->value;

        // PERUBAHAN: Tangkap pilihan jabatan dari request (dropdown) jika Unit
        if ($user->role === RoleAdmin::Unit) {
            $data['penandatangan_id'] = $user->id;
            $data['penandatangan_nama'] = $user->name;
            
            // Ambil jabatan pertama sebagai jaga-jaga jika input kosong
            $jabatanUtama = explode(',', $user->jabatan ?? '')[0] ?? '';
            $jabatanUtama = trim($jabatanUtama) ?: $user->role->label();
            
            $data['penandatangan_jabatan'] = $request->input('penandatangan_jabatan') ?: $jabatanUtama;
        } else {
            $data['penandatangan_id'] = null;
        }

        try {
            $surat = $publisher->terbitkan($user, $data, $request->file('berkas'));
        } catch (Throwable $e) {
            report($e);
            return back()->withInput()->withErrors(['berkas' => 'Terjadi kesalahan saat menyimpan PDF. Data tidak disimpan, silakan coba lagi.']);
        }

        return redirect()->route('admin.surat.show', $surat)->with('status', 'Surat berhasil disimpan sebagai draf.');
    }

    public function show(Surat $surat, QrCodeService $qr): View
    {
        Gate::authorize('view', $surat);
        $surat->load(['pembuat:id,name', 'pencabut:id,name', 'jenisSurat:id,nama,kode', 'penandatangan:id,name,jabatan']);

        $logoBase64 = null;
        if ($surat->qr_logo_path && Storage::disk('surat')->exists($surat->qr_logo_path)) {
            $mime = Storage::disk('surat')->mimeType($surat->qr_logo_path);
            $content = Storage::disk('surat')->get($surat->qr_logo_path);
            $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode($content);
        }

        return view('admin.surat.show', [
            'surat' => $surat,
            'qrSvg' => $qr->svg($surat->urlVerifikasi(), 2, $logoBase64, $surat->qr_color ?? '#000000'),
            'riwayatScan' => $surat->scanLogs()->latest('scanned_at')->limit(15)->get(),
            'audit' => $surat->auditLogs()->with('user:id,name')->latest('created_at')->limit(10)->get(),
        ]);
    }

    public function publish(Request $request, Surat $surat, SuratPublisher $publisher): RedirectResponse
    {
        Gate::authorize('publish', $surat);
        $berhasil = $publisher->publish($surat, $request->user());

        return back()->with($berhasil ? 'status' : 'galat', $berhasil
            ? "Surat {$surat->nomor_surat} berhasil dipublikasikan."
            : 'Status surat berubah sebelumnya. Muat ulang halaman.');
    }

    public function download(Request $request, Surat $surat): StreamedResponse
    {
        Gate::authorize('download', $surat);
        $asli = $request->query('versi') === 'asli';
        $path = $asli ? $surat->file_asli_path : $surat->file_final_path;
        $nama = ($asli ? 'draf-asli-' : '').$surat->namaBerkas();
        
        abort_unless(Storage::disk('surat')->exists($path), 404, 'Berkas tidak ditemukan di penyimpanan.');
        
        AuditLog::catat('unduh', $request->user(), $surat, ['versi' => $asli ? 'asli' : 'final']);

        return Storage::disk('surat')->download($path, $nama, ['Content-Type' => 'application/pdf']);
    }

    public function revoke(RevokeSuratRequest $request, Surat $surat, SuratPublisher $publisher): RedirectResponse
    {
        Gate::authorize('revoke', $surat);
        $berhasil = $publisher->cabut($surat, $request->user(), $request->validated('alasan'));

        return back()->with($berhasil ? 'status' : 'galat', $berhasil
            ? "Status surat {$surat->nomor_surat} diubah menjadi ditarik."
            : 'Surat ini sudah ditarik sebelumnya.');
    }
}