@php
    $sah = $surat->isAktif();
    $draf = $surat->isDraf();
    $ditarik = $surat->isDicabut();
    $instansi = config('penyuratan.instansi.nama');
    $judulStatus = $sah ? 'Dokumen terverifikasi' : ($draf ? 'Surat belum dipublikasikan' : 'Surat ditarik');
@endphp
<x-layouts.publik :title="$judulStatus">
    <header @class(['px-5 pb-28 pt-6 text-white', 'panel-valid' => $sah, 'panel-unknown' => $draf, 'panel-revoked' => $ditarik])>
        <div class="mx-auto flex max-w-xl items-center gap-3">
            <x-logo variant="navbar" class="size-10 shrink-0 rounded-xl bg-white/90 p-0.5"/>
            <p class="text-sm font-semibold leading-tight">{{ $instansi }}</p>
        </div>
        <div class="mx-auto mt-6 flex max-w-xl flex-col items-center text-center">
            <x-seal :state="$sah ? 'valid' : ($draf ? 'unknown' : 'revoked')" class="w-36 sm:w-40"/>
            <h1 class="mt-5 text-xl font-bold tracking-wide sm:text-2xl">
                {{ $sah ? 'DOKUMEN TERVERIFIKASI & ASLI' : ($draf ? 'SURAT BELUM DIPUBLIKASIKAN' : 'SURAT TELAH DITARIK') }}
            </h1>
            <p class="mt-2 max-w-sm text-sm leading-relaxed {{ $sah ? 'text-white' : 'text-white/90' }}">
                @if ($sah)
                    Surat ini diterbitkan resmi oleh {{ $instansi }} dan sampai saat ini berstatus Publish.
                @elseif ($draf)
                    Surat ini masih berstatus Draf dan belum dipublikasikan. Jangan menganggap dokumen ini sebagai surat resmi yang telah terverifikasi.
                @else
                    Surat ini ditarik oleh penerbit pada {{ $surat->revoked_at?->translatedFormat('j F Y') ?? 'tanggal yang tercatat dalam sistem' }}. Jangan dijadikan rujukan.
                @endif
            </p>
        </div>
    </header>

    <main class="relative mx-auto -mt-16 max-w-xl space-y-5 px-4">
        <section class="rounded-2xl border border-line bg-white shadow-[0_18px_40px_-24px_rgb(64_35_29/0.45)]" aria-labelledby="judul-rincian">
            <div class="border-b border-line px-5 py-4 sm:px-6"><h2 id="judul-rincian" class="text-base font-semibold">Cocokkan dengan surat di tangan Anda</h2><p class="mt-0.5 text-sm text-ink-soft">Jika ada data yang berbeda, hubungi instansi penerbit.</p></div>
            <dl class="divide-y divide-line">
                <div class="px-5 py-4 sm:px-6"><dt class="text-sm text-ink-soft">Nomor surat</dt><dd @class(['mt-0.5 break-words text-lg font-semibold', 'line-through decoration-red-500/70' => $ditarik])>{{ $surat->nomor_surat }}</dd></div>
                <div class="px-5 py-4 sm:px-6"><dt class="text-sm text-ink-soft">Jenis surat</dt><dd class="mt-0.5 font-medium">{{ $surat->jenisSurat?->nama ?? '—' }} ({{ $surat->jenisSurat?->kode ?? '—' }})</dd></div>
                <div class="px-5 py-4 sm:px-6"><dt class="text-sm text-ink-soft">Perihal</dt><dd class="mt-0.5 font-medium leading-snug">{{ $surat->perihal }}</dd></div>
                <div class="px-5 py-4 sm:px-6"><dt class="text-sm text-ink-soft">Ditujukan kepada</dt><dd class="mt-0.5 font-medium">{{ $surat->ditujukan_kepada ?: '—' }}</dd></div>
                <div class="px-5 py-4 sm:px-6"><dt class="text-sm text-ink-soft">Tanggal surat</dt><dd class="mt-0.5 font-medium">{{ $surat->tanggal_surat->translatedFormat('j F Y') }}</dd></div>
                <div class="px-5 py-4 sm:px-6"><dt class="text-sm text-ink-soft">Penandatangan</dt><dd class="mt-0.5 font-medium">{{ $surat->penandatangan_nama }}</dd><dd class="text-sm text-ink-soft">{{ $surat->penandatangan_jabatan }}</dd></div>
            </dl>
        </section>

        <section class="flex items-center gap-4 rounded-2xl border border-line bg-white p-5 sm:p-6" aria-labelledby="judul-letak">
            <x-letak-qr :posisi="$surat->qr_posisi" class="w-24 shrink-0 drop-shadow-sm"/>
            <div><h2 id="judul-letak" class="text-base font-semibold">Letak QR Code di surat</h2><p class="mt-1 text-sm leading-relaxed text-ink-soft">Pada surat asli, QR Code berada di {{ $surat->qr_posisi->frasa() }}, {{ strtolower($surat->qr_halaman->label()) }}. Kode verifikasi: <strong class="whitespace-nowrap tracking-widest text-ink">{{ $surat->kodeTampil() }}</strong>.</p></div>
        </section>

        @if ($sah)
            <div class="sticky bottom-0 z-10 -mx-4 bg-gradient-to-t from-paper via-paper/95 to-transparent px-4 pb-4 pt-6" style="padding-bottom: max(1rem, env(safe-area-inset-bottom));">
                <a href="{{ route('verifikasi.unduh', $surat->uuid) }}" class="btn-primary btn-lg w-full shadow-lg shadow-orange-900/20"><x-icon name="unduh" class="size-5"/>Unduh Dokumen Resmi (PDF)</a>
                <p class="mt-2 text-center text-xs text-ink-soft">{{ number_format($surat->file_size / 1024, 0, ',', '.') }} KB. Sidik jari berkas ada di bawah.</p>
            </div>
        @else
            <section class="flex items-start gap-3 rounded-2xl border {{ $ditarik ? 'border-red-200 bg-red-50 text-red-900' : 'border-amber-200 bg-amber-50 text-amber-950' }} p-5 text-sm leading-relaxed" role="alert">
                <x-icon name="peringatan" class="mt-0.5 size-5 shrink-0"/>
                <p>@if($draf) Surat ini belum dipublikasikan dan belum dapat diunduh sebagai dokumen resmi. @else Surat ini berstatus Ditarik dan tidak lagi dapat diunduh. @endif Hubungi {{ $instansi }} untuk konfirmasi lebih lanjut.</p>
            </section>
        @endif

        <details class="rounded-2xl border border-line bg-white p-5 text-sm sm:px-6"><summary class="cursor-pointer font-semibold">Sidik jari berkas (SHA-256)</summary><p class="mt-3 break-all text-xs font-medium leading-relaxed">{{ $surat->file_hash_sha256 }}</p><p class="mt-2 text-xs text-ink-soft">Hash dari PDF yang tersimpan untuk surat ini.</p><button type="button" data-copy="{{ $surat->file_hash_sha256 }}" class="btn-secondary mt-3 !py-2 !text-xs">Salin hash</button></details>
        <section class="rounded-2xl border border-line bg-white p-5 sm:p-6" aria-labelledby="judul-cari"><h2 id="judul-cari" class="mb-4 text-base font-semibold">Periksa surat lain</h2>@include('verifikasi._pencarian')</section>
    </main>
</x-layouts.publik>
