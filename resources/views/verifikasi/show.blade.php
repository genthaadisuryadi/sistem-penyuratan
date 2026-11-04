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

        {{-- CEK KEASLIAN BERKAS (HASHING) --}}
        <details class="rounded-3xl border border-slate-200 bg-white p-5 text-sm shadow-sm sm:px-6" {{ $sah ? 'open' : '' }}>
            <summary class="cursor-pointer font-semibold text-slate-800 hover:text-slate-900">Cek Keaslian & Sidik Jari Berkas (SHA-256)</summary>
            
            <p class="mb-4 mt-4 text-[13px] leading-relaxed text-slate-500">Unggah dokumen PDF fisik Anda ke sini. Sistem akan mengecek sidik jari (Hash) dokumen secara instan untuk memastikan tidak ada satu kata pun yang dimanipulasi.</p>
            
            <!-- AREA UPLOAD (Ditampilkan saat kosong) -->
            <div id="area_upload">
                <label for="cek_file_publik" class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center transition-colors hover:border-blue-500 hover:bg-blue-50">
                    <input id="cek_file_publik" type="file" accept="application/pdf,.pdf" class="sr-only">
                    <x-icon name="unggah" class="mb-2 size-7 text-slate-400" />
                    <p class="text-sm font-semibold text-slate-700">Pilih berkas PDF Anda</p>
                    <p class="mt-1 text-[11px] text-slate-500">Aman 100%. File diperiksa di perangkat Anda dan tidak dikirim ke server.</p>
                </label>
            </div>

            <!-- AREA INFO FILE (Ditampilkan setelah upload, menggantikan kotak upload) -->
            <div id="area_file_terpilih" class="hidden mt-2 flex items-center justify-between rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center gap-3 overflow-hidden">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p id="nama_file_terpilih" class="truncate text-sm font-semibold text-slate-800">nama-file.pdf</p>
                        <p id="ukuran_file_terpilih" class="text-[11px] text-slate-500">0 KB</p>
                    </div>
                </div>
                <button type="button" id="btn_ganti_file" class="ml-4 shrink-0 rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-600 transition-colors hover:bg-slate-100 hover:text-slate-900">
                    Ganti
                </button>
            </div>

            <div id="hasil_cek_publik" class="mt-4 hidden rounded-xl border p-4 text-center transition-all"></div>

            <div class="mt-6 border-t border-slate-100 pt-5">
                <p class="text-xs font-semibold text-slate-700">Hash Asli (Tersimpan di Server):</p>
                <div class="mt-3 rounded-xl bg-slate-50 p-4">
                    <p id="hash_sistem_publik" class="break-all font-mono text-[11px] font-medium leading-relaxed text-slate-600">{{ $surat->file_hash_sha256 }}</p>
                    <button type="button" data-copy="{{ $surat->file_hash_sha256 }}" class="btn-secondary mt-3 !py-1.5 !text-[11px] font-semibold uppercase tracking-wide">Salin Hash</button>
                </div>
            </div>
        </details>
        
        {{-- KOTAK CARI SURAT LAIN --}}
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8" aria-labelledby="judul-cari">
            <h2 id="judul-cari" class="mb-5 text-base font-bold text-slate-900">Periksa surat lain</h2>
            @include('verifikasi._pencarian')
        </section>
    </main>

    <!-- ================= SCRIPT VALIDASI CLIENT-SIDE ================= -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('cek_file_publik');
            const areaUpload = document.getElementById('area_upload');
            const areaFileTerpilih = document.getElementById('area_file_terpilih');
            const namaFileTerpilih = document.getElementById('nama_file_terpilih');
            const ukuranFileTerpilih = document.getElementById('ukuran_file_terpilih');
            const btnGantiFile = document.getElementById('btn_ganti_file');
            
            const hasilDiv = document.getElementById('hasil_cek_publik');
            const hashSistem = document.getElementById('hash_sistem_publik').innerText.trim().toLowerCase();

            // Fungsi untuk memformat ukuran file
            function formatBytes(bytes, decimals = 2) {
                if (!+bytes) return '0 Bytes';
                const k = 1024;
                const dm = decimals < 0 ? 0 : decimals;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`;
            }

            // Aksi saat tombol "Ganti" diklik
            btnGantiFile.addEventListener('click', function() {
                fileInput.value = '';
                areaUpload.classList.remove('hidden');
                areaFileTerpilih.classList.add('hidden');
                hasilDiv.classList.add('hidden');
            });

            if (fileInput) {
                fileInput.addEventListener('change', async function(e) {
                    const file = e.target.files[0];
                    if (!file) return;

                    // Sembunyikan area upload & tampilkan info file
                    areaUpload.classList.add('hidden');
                    areaFileTerpilih.classList.remove('hidden');
                    namaFileTerpilih.textContent = file.name;
                    ukuranFileTerpilih.textContent = formatBytes(file.size);

                    // Tampilkan status loading
                    hasilDiv.classList.remove('hidden');
                    hasilDiv.className = 'mt-4 rounded-xl border border-slate-200 bg-slate-50 p-5 text-center shadow-inner';
                    hasilDiv.innerHTML = '<p class="text-sm font-semibold text-slate-500 animate-pulse">Memeriksa keaslian dokumen...</p>';

                    try {
                        // Proses baca file dan konversi ke SHA-256
                        const arrayBuffer = await file.arrayBuffer();
                        const hashBuffer = await crypto.subtle.digest('SHA-256', arrayBuffer);
                        const hashArray = Array.from(new Uint8Array(hashBuffer));
                        const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('').toLowerCase();

                        // Bandingkan Hash Upload vs Hash Database
                        if (hashHex === hashSistem) {
                            // PERBAIKAN: Ubah warna menjadi BIRU
                            hasilDiv.className = 'mt-4 rounded-xl border border-blue-200 bg-blue-50 p-5 text-center text-blue-900 shadow-sm ring-1 ring-blue-500/20';
                            hasilDiv.innerHTML = `
                                <div class="mb-1 flex items-center justify-center gap-2">
                                    <svg class="size-7 text-blue-600 drop-shadow-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="text-lg font-bold">Dokumen Valid & Asli</span>
                                </div>
                                <p class="mt-2 text-[13px] font-medium leading-relaxed text-blue-700/90">Sidik jari dokumen ini cocok 100% dengan database kami. Terjamin tidak ada manipulasi.</p>
                            `;
                        } else {
                            hasilDiv.className = 'mt-4 rounded-xl border border-red-200 bg-red-50 p-5 text-center text-red-900 shadow-sm ring-1 ring-red-500/20';
                            hasilDiv.innerHTML = `
                                <div class="mb-1 flex items-center justify-center gap-2">
                                    <svg class="size-7 text-red-600 drop-shadow-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="text-lg font-bold">Dokumen Tidak Valid</span>
                                </div>
                                <p class="mt-2 text-[13px] font-medium leading-relaxed text-red-700/90">Peringatan: Dokumen ini berbeda dari aslinya dan terindikasi telah dimodifikasi atau dipalsukan.</p>
                                <div class="mt-4 rounded-lg border border-red-200 bg-white/70 p-3 text-left">
                                    <p class="text-[11px] font-bold uppercase tracking-wider text-red-800">Hash Terdeteksi (Berbeda):</p>
                                    <p class="mt-1 break-all font-mono text-[11px] text-red-700/80">${hashHex}</p>
                                </div>
                            `;
                        }
                    } catch (error) {
                        hasilDiv.className = 'mt-4 rounded-xl border border-amber-200 bg-amber-50 p-5 text-center text-amber-900';
                        hasilDiv.innerHTML = '<p class="text-sm font-bold">Gagal memproses berkas PDF.</p><p class="mt-1 text-xs text-amber-700/80">Pastikan file yang diunggah tidak rusak.</p>';
                    }
                });
            }
        });
    </script>
</x-layouts.publik>