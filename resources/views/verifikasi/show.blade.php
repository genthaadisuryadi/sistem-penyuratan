@php
    $sah = $surat->isAktif();
    $draf = $surat->isDraf();
    $ditarik = $surat->isDicabut();
    
    $instansi = $surat->nama_instansi ?? 'Instansi Tidak Diketahui'; 
    
    $judulStatus = $sah ? 'Dokumen terverifikasi' : ($draf ? 'Surat belum dipublikasikan' : 'Surat ditarik');
@endphp
<x-layouts.publik :title="$judulStatus">
    {{-- HEADER DENGAN ORNAMEN ABSTRAK GLOWING --}}
    <header @class([
        'relative overflow-hidden px-5 pb-32 pt-16 text-white transition-all duration-300', 
        'bg-gradient-to-br from-blue-600 via-blue-500 to-blue-800' => $sah, 
        'bg-gradient-to-br from-[#e7553c] via-[#f07860] to-[#b63a28]' => $draf, 
        'bg-gradient-to-br from-red-600 via-red-500 to-red-900' => $ditarik
    ])>
        <!-- Hiasan Cahaya Transparan -->
        <div class="pointer-events-none absolute -right-20 -top-20 size-72 rounded-full bg-white/10 blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-24 -left-16 size-80 rounded-full bg-black/10 blur-3xl" aria-hidden="true"></div>

        <div class="relative mx-auto flex max-w-xl flex-col items-center text-center">
            <x-seal :state="$sah ? 'valid' : ($draf ? 'unknown' : 'revoked')" class="w-32 sm:w-40 drop-shadow-xl"/>
            
            <h1 class="mt-6 text-2xl font-bold tracking-tight sm:text-3xl">
                {{ $sah ? 'DOKUMEN TERVERIFIKASI & ASLI' : ($draf ? 'SURAT BELUM DIPUBLIKASIKAN' : 'SURAT TELAH DITARIK') }}
            </h1>
            <p class="mt-3 max-w-md text-sm leading-relaxed text-white/90">
                @if ($sah)
                    Surat ini diterbitkan resmi oleh <span class="font-semibold text-white">{{ $instansi }}</span> dan sampai saat ini berstatus Publish.
                @elseif ($draf)
                    Surat ini masih berstatus Draf dan belum dipublikasikan. Jangan menganggap dokumen ini sebagai surat resmi yang telah terverifikasi.
                @else
                    Surat ini ditarik oleh penerbit pada {{ $surat->revoked_at?->translatedFormat('j F Y') ?? 'tanggal yang tercatat dalam sistem' }}. Jangan dijadikan rujukan.
                @endif
            </p>
        </div>
    </header>

    <main class="relative mx-auto -mt-20 max-w-2xl space-y-6 px-4">
        
        {{-- KOTAK DATA UTAMA (Desain Dashboard Grid) --}}
        <section class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white/95 shadow-[0_20px_60px_-15px_rgba(0,0,0,0.1)] backdrop-blur-xl" aria-labelledby="judul-rincian">
            
            <div class="border-b border-slate-100 px-6 py-5 sm:px-8">
                <h2 id="judul-rincian" class="text-lg font-bold text-slate-900">Detail Dokumen</h2>
                <p class="mt-1 text-sm text-slate-500">Pastikan data di bawah ini sama persis dengan fisik surat Anda.</p>
            </div>
            
            <dl class="grid grid-cols-1 sm:grid-cols-2">
                <!-- Baris 1: Instansi (Lebar Penuh) -->
                <div class="col-span-1 border-b border-slate-100 px-6 py-4 sm:col-span-2 sm:px-8">
                    <dt class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Nama Instansi Penerbit</dt>
                    <dd class="mt-1.5 text-lg font-semibold text-[#e7553c]">{{ $instansi }}</dd>
                </div>
                
                <!-- Baris 2: Nomor & Jenis (Dibagi 2 kolom di layar besar) -->
                <div class="border-b border-slate-100 px-6 py-4 sm:border-r sm:px-8">
                    <dt class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Nomor Surat</dt>
                    <dd @class(['mt-1.5 font-medium text-slate-900', 'line-through decoration-red-500/70' => $ditarik])>{{ $surat->nomor_surat }}</dd>
                </div>
                <div class="border-b border-slate-100 px-6 py-4 sm:px-8">
                    <dt class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Jenis Surat</dt>
                    <dd class="mt-1.5 font-medium text-slate-900">{{ $surat->jenisSurat?->nama ?? '—' }} ({{ $surat->jenisSurat?->kode ?? '—' }})</dd>
                </div>

                <!-- Baris 3: Tanggal & Ditujukan (Dibagi 2 kolom) -->
                <div class="border-b border-slate-100 px-6 py-4 sm:border-r sm:px-8">
                    <dt class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Tanggal Surat</dt>
                    <dd class="mt-1.5 font-medium text-slate-900">{{ $surat->tanggal_surat->translatedFormat('j F Y') }}</dd>
                </div>
                <div class="border-b border-slate-100 px-6 py-4 sm:px-8">
                    <dt class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Ditujukan Kepada</dt>
                    <dd class="mt-1.5 font-medium text-slate-900">{{ $surat->ditujukan_kepada ?: '—' }}</dd>
                </div>

                <!-- Baris 4: Perihal (Lebar Penuh) -->
                <div class="col-span-1 border-b border-slate-100 px-6 py-4 sm:col-span-2 sm:px-8">
                    <dt class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Perihal</dt>
                    <dd class="mt-1.5 font-medium leading-snug text-slate-900">{{ $surat->perihal }}</dd>
                </div>

                <!-- Baris 5: Penandatangan (Lebar Penuh) -->
                <div class="col-span-1 px-6 py-4 sm:col-span-2 sm:px-8">
                    <dt class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Ditandatangani Oleh</dt>
                    <dd class="mt-1.5 font-semibold text-slate-900">{{ $surat->penandatangan_nama }}</dd>
                    <dd class="mt-0.5 text-sm text-slate-500">{{ $surat->penandatangan_jabatan }}</dd>
                </div>
            </dl>

            {{-- TOMBOL UNDUH YANG DIINTEGRASIKAN KE DALAM KOTAK --}}
            @if ($sah)
                <div class="bg-slate-50 px-6 py-5 sm:px-8">
                    <a href="{{ route('verifikasi.unduh', $surat->uuid) }}" class="btn-primary !bg-blue-600 !border-blue-600 hover:!bg-blue-700 btn-lg flex w-full items-center justify-center gap-2 shadow-lg shadow-blue-900/20 transition-transform active:scale-[0.98]">
                        <x-icon name="unduh" class="size-5"/>
                        Unduh Dokumen Resmi (PDF)
                    </a>
                    <p class="mt-3 text-center text-xs text-slate-400">Ukuran dokumen: {{ number_format($surat->file_size / 1024, 0, ',', '.') }} KB.</p>
                </div>
            @endif
        </section>

        {{-- PERINGATAN JIKA TIDAK SAH --}}
        @if (! $sah)
            <section class="flex items-start gap-3 rounded-3xl border {{ $ditarik ? 'border-red-200 bg-red-50 text-red-900' : 'border-orange-200 bg-orange-50 text-orange-950' }} p-6 text-sm leading-relaxed shadow-sm" role="alert">
                <x-icon name="peringatan" class="mt-0.5 size-6 shrink-0"/>
                <p>@if($draf) Surat ini belum dipublikasikan dan belum dapat diunduh sebagai dokumen resmi. @else Surat ini berstatus Ditarik dan tidak lagi dapat diunduh. @endif Hubungi pihak instansi untuk konfirmasi lebih lanjut.</p>
            </section>
        @endif

        {{-- DETAIL HASH BERKAS --}}
        <details class="rounded-3xl border border-slate-200 bg-white p-5 text-sm shadow-sm sm:px-6">
            <summary class="cursor-pointer font-semibold text-slate-700 hover:text-slate-900">Lihat sidik jari berkas (SHA-256)</summary>
            <div class="mt-4 rounded-xl bg-slate-50 p-4">
                <p class="break-all font-mono text-xs font-medium leading-relaxed text-slate-600">{{ $surat->file_hash_sha256 }}</p>
                <p class="mt-3 text-xs text-slate-500">Hash dari PDF yang tersimpan di sistem.</p>
                <button type="button" data-copy="{{ $surat->file_hash_sha256 }}" class="btn-secondary mt-3 !py-2 !text-xs">Salin hash</button>
            </div>
        </details>
        
        {{-- KOTAK CARI SURAT LAIN --}}
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8" aria-labelledby="judul-cari">
            <h2 id="judul-cari" class="mb-5 text-base font-bold text-slate-900">Periksa surat lain</h2>
            @include('verifikasi._pencarian')
        </section>
    </main>
</x-layouts.publik>