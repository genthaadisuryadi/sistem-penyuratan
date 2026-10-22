<x-layouts.admin :title="$surat->nomor_surat">
    <a href="{{ route('admin.surat.index') }}" class="mb-4 inline-flex items-center gap-1.5 text-sm font-medium text-ink-soft hover:text-ink">
        <x-icon name="arsip" class="size-4"/>
        Kembali ke arsip
    </a>

    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="break-all text-2xl font-semibold tracking-tight">{{ $surat->nomor_surat }}</h1>
                <x-status-pill :status="$surat->status"/>
            </div>
            <p class="mt-1 text-ink-soft">{{ $surat->perihal }}</p><p class="mt-1 text-xs font-semibold text-emerald-700">{{ $surat->jenisSurat?->nama ?? 'Jenis tidak tersedia' }} · Ditujukan kepada {{ $surat->ditujukan_kepada ?: '—' }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.surat.unduh', $surat) }}" class="btn-primary"><x-icon name="unduh" class="size-4"/>Unduh PDF ber-QR</a>
            <a href="{{ route('admin.surat.unduh', [$surat, 'versi' => 'asli']) }}" class="btn-secondary">Draf asli</a>
            @can('publish', $surat)
                <form method="POST" action="{{ route('admin.surat.publish', $surat) }}" onsubmit="return confirm('Publikasikan surat ini?')">@csrf<button type="submit" class="btn-primary">Publish Surat</button></form>
            @endcan
            @can('revoke', $surat)
                <button type="button" data-modal-open="dialog-cabut" data-form-action="{{ route('admin.surat.cabut', $surat) }}" data-nomor="{{ $surat->nomor_surat }}" class="btn-danger-outline">Tarik Surat</button>
            @endcan
        </div>
    </div>

    @if ($surat->isDraf())
        <div role="status" class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-950">
            <p class="font-semibold">Surat masih berstatus Draf.</p>
            <p class="mt-1">Publikasikan surat untuk mengaktifkan verifikasi publik. QR sudah ditempel pada PDF, tetapi belum menyatakan surat sebagai dokumen sah.</p>
        </div>
    @endif

    @if ($surat->isDicabut())
        <div role="alert" class="mb-6 flex items-start gap-4 rounded-xl border border-red-200 bg-red-50 p-5">
            <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-600 text-white"><x-icon name="cabut"/></span>
            <div class="text-sm">
                <p class="font-semibold text-red-900">Surat ini ditarik pada {{ $surat->revoked_at?->translatedFormat('j F Y, H:i') ?? 'tanggal tidak tercatat' }} oleh {{ $surat->pencabut?->name ?? 'admin' }}.</p>
                <p class="mt-1 text-red-900/80">Alasan: {{ $surat->alasan_pencabutan }}</p>
            </div>
        </div>
    @endif

    <div class="grid items-start gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <section class="panel" aria-labelledby="judul-rincian">
                <h2 id="judul-rincian" class="border-b border-line px-6 py-4 text-base font-semibold">Rincian surat</h2>
                <dl class="grid gap-x-6 gap-y-5 p-6 sm:grid-cols-2">
                    <div><dt class="text-sm text-ink-soft">Tanggal surat</dt><dd class="mt-0.5 font-medium">{{ $surat->tanggal_surat->translatedFormat('j F Y') }}</dd></div>
                    <div><dt class="text-sm text-ink-soft">Dibuat oleh</dt><dd class="mt-0.5 font-medium">{{ $surat->created_at->translatedFormat('j F Y, H:i') }} oleh {{ $surat->pembuat?->name }}</dd></div>
                    <div><dt class="text-sm text-ink-soft">Jenis Surat</dt><dd class="mt-0.5 font-medium">{{ $surat->jenisSurat?->nama ?? '—' }} ({{ $surat->jenisSurat?->kode ?? '—' }})</dd></div>
                    <div><dt class="text-sm text-ink-soft">Ditujukan kepada</dt><dd class="mt-0.5 font-medium">{{ $surat->ditujukan_kepada ?: '—' }}</dd></div>
                    <div><dt class="text-sm text-ink-soft">Status</dt><dd class="mt-0.5 font-medium">{{ $surat->status->label() }} @if($surat->published_at) · {{ $surat->published_at->translatedFormat('j M Y, H:i') }} @endif</dd></div>
                    <div><dt class="text-sm text-ink-soft">Penandatangan</dt><dd class="mt-0.5 font-medium">{{ $surat->penandatangan_nama }}</dd></div>
                    <div><dt class="text-sm text-ink-soft">Jabatan</dt><dd class="mt-0.5 font-medium">{{ $surat->penandatangan_jabatan }}</dd></div>
                    <div><dt class="text-sm text-ink-soft">Letak QR</dt><dd class="mt-0.5 font-medium">{{ $surat->qr_posisi->label() }}, {{ strtolower($surat->qr_halaman->label()) }}</dd></div>
                    <div><dt class="text-sm text-ink-soft">Ukuran berkas</dt><dd class="mt-0.5 font-medium">{{ number_format($surat->file_size / 1024, 0, ',', '.') }} KB</dd></div>
                </dl>
            </section>

            <section class="panel" aria-labelledby="judul-scan">
                <div class="flex items-baseline justify-between border-b border-line px-6 py-4">
                    <h2 id="judul-scan" class="text-base font-semibold">Riwayat pemindaian</h2>
                    <p class="text-sm text-ink-soft">{{ number_format($surat->scan_count, 0, ',', '.') }} total</p>
                </div>
                @if ($riwayatScan->isEmpty())
                    <p class="px-6 py-10 text-center text-sm text-ink-soft">Belum ada yang memindai surat ini.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-line">
                            <thead class="bg-pine-50/70"><tr><th scope="col" class="th">Waktu</th><th scope="col" class="th">Status saat itu</th><th scope="col" class="th">Sumber</th></tr></thead>
                            <tbody class="divide-y divide-line">
                                @foreach ($riwayatScan as $log)
                                    <tr>
                                        <td class="td whitespace-nowrap">{{ $log->scanned_at->translatedFormat('j M Y, H:i:s') }}</td>
                                        <td class="td">{{ match ($log->status_saat_scan) { 'publish', 'aktif' => 'Publish', 'draf' => 'Draf', 'ditarik', 'dicabut' => 'Ditarik', default => $log->status_saat_scan } }}</td>
                                        <td class="td text-ink-soft">{{ $log->is_bot ? 'Robot / pratinjau tautan (tidak dihitung)' : 'Pengunjung' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <section class="panel" aria-labelledby="judul-audit">
                <h2 id="judul-audit" class="border-b border-line px-6 py-4 text-base font-semibold">Jejak aktivitas</h2>
                <ul class="divide-y divide-line">
                    @forelse ($audit as $a)
                        <li class="flex items-center justify-between gap-4 px-6 py-3 text-sm">
                            <span><span class="font-medium">{{ $a->label() }}</span> <span class="text-ink-soft">oleh {{ $a->user?->name ?? 'sistem' }}</span></span>
                            <time class="shrink-0 text-ink-soft" datetime="{{ $a->created_at->toIso8601String() }}">{{ $a->created_at->translatedFormat('j M Y, H:i') }}</time>
                        </li>
                    @empty
                        <li class="px-6 py-8 text-center text-sm text-ink-soft">Belum ada aktivitas.</li>
                    @endforelse
                </ul>
            </section>
        </div>

        <aside class="space-y-6" aria-label="Verifikasi">
            <section class="panel p-6">
                <h2 class="text-base font-semibold">Tautan verifikasi</h2>
                <div class="mx-auto mt-4 w-44 overflow-hidden rounded-lg border border-line">{!! $qrSvg !!}</div>
                <p class="mt-4 break-all text-xs text-ink-soft">{{ $surat->urlVerifikasi() }}</p>
                <button type="button" data-copy="{{ $surat->urlVerifikasi() }}" class="btn-secondary mt-3 w-full !py-2 !text-xs">Salin tautan</button>

                <p class="mt-5 text-sm text-ink-soft">Kode verifikasi (tercetak di bawah QR)</p>
                <p class="mt-0.5 text-xl font-semibold tracking-[0.18em]">{{ $surat->kodeTampil() }}</p>
                <a href="{{ $surat->urlVerifikasi() }}" target="_blank" rel="noopener" class="mt-4 inline-flex items-center gap-1.5 text-sm font-medium text-emerald-700 hover:underline">
                    <x-icon name="mata" class="size-4"/>
                    Lihat halaman publik
                </a>
            </section>

            <section class="panel p-6">
                <h2 class="text-base font-semibold">Letak QR di surat</h2>
                <x-letak-qr :posisi="$surat->qr_posisi" class="mx-auto mt-4 w-28"/>
            </section>

            <section class="panel p-6">
                <h2 class="text-base font-semibold">Sidik jari berkas</h2>
                <p class="mt-1 text-xs text-ink-soft">SHA-256 dari PDF ber-QR. Berubah jika satu byte pun diubah.</p>
                <p class="mt-3 break-all text-xs font-medium leading-relaxed">{{ $surat->file_hash_sha256 }}</p>
                <button type="button" data-copy="{{ $surat->file_hash_sha256 }}" class="btn-secondary mt-3 w-full !py-2 !text-xs">Salin hash</button>
            </section>
        </aside>
    </div>

    <x-slot:dialogs>
        @include('admin.surat._dialog-cabut', ['aksi' => route('admin.surat.cabut', $surat), 'nomor' => $surat->nomor_surat])
    </x-slot:dialogs>
</x-layouts.admin>
