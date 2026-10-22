<x-layouts.admin title="Buat Surat Baru">
    <div class="mb-6">
        <p class="text-sm font-semibold uppercase tracking-[0.16em] text-emerald-700">Penerbitan dokumen</p>
        <h1 class="mt-1 text-2xl font-semibold tracking-tight">Buat surat baru</h1>
        <p class="mt-1 text-sm text-ink-soft">Isi data surat, unduh QR Code, lalu unggah PDF surat Anda.</p>
    </div>

    <form method="POST" action="{{ route('admin.surat.store') }}" enctype="multipart/form-data"
          data-loading-text="Memproses PDF..." data-qr-live data-qr-endpoint="{{ route('admin.qr-preview') }}" 
          data-qr-base-url="{{ rtrim(config('penyuratan.verification_base_url'), '/') }}" novalidate>
        @csrf

        <!-- ================= STEP 1: FORMULIR & QR CODE ================= -->
        <div id="step-1-container" class="grid items-start gap-6 lg:grid-cols-5">
            <section class="panel p-6 lg:col-span-3" aria-labelledby="judul-data">
                <h2 id="judul-data" class="text-base font-semibold">Informasi surat</h2>

                <div class="mt-5">
                    <label for="jenis_surat_id" class="label">Jenis Surat</label>
                    <select id="jenis_surat_id" name="jenis_surat_id" class="input" required>
                        <option value="">Pilih jenis surat</option>
                        @foreach ($jenisSurat as $jenis)
                            <option value="{{ $jenis->id }}" @selected((string) old('jenis_surat_id') === (string) $jenis->id)>{{ $jenis->nama }} ({{ $jenis->kode }})</option>
                        @endforeach
                    </select>
                    @if ($jenisSurat->isEmpty())
                        <p class="field-error">Belum ada Jenis Surat aktif. Hubungi Sekretariat atau Super Admin untuk menambahkan data master.</p>
                    @endif
                    @error('jenis_surat_id') <p class="field-error" role="alert">{{ $message }}</p> @enderror
                </div>

                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="nomor_surat" class="label">Nomor surat</label>
                        <input id="nomor_surat" name="nomor_surat" type="text" value="{{ old('nomor_surat') }}" required maxlength="100" placeholder="001/UND/X/2026" class="input" @error('nomor_surat') aria-invalid="true" @enderror>
                        @error('nomor_surat') <p class="field-error" role="alert">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="tanggal_surat" class="label">Tanggal</label>
                        <input id="tanggal_surat" name="tanggal_surat" type="date" value="{{ old('tanggal_surat', now()->toDateString()) }}" required class="input" @error('tanggal_surat') aria-invalid="true" @enderror>
                        @error('tanggal_surat') <p class="field-error" role="alert">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-5">
                    <label for="perihal" class="label">Subject / Perihal</label>
                    <input id="perihal" name="perihal" type="text" value="{{ old('perihal') }}" required maxlength="255" placeholder="{{ $isUnit ? '' : 'Undangan rapat koordinasi' }}" class="input" @error('perihal') aria-invalid="true" @enderror>
                    @error('perihal') <p class="field-error" role="alert">{{ $message }}</p> @enderror
                </div>

                <div class="mt-5">
                    <label for="ditujukan_kepada" class="label">Ditujukan Kepada</label>
                    <input id="ditujukan_kepada" name="ditujukan_kepada" type="text" value="{{ old('ditujukan_kepada') }}" required maxlength="255" placeholder="{{ $isUnit ? '' : 'Kepala Unit / nama penerima' }}" class="input" @error('ditujukan_kepada') aria-invalid="true" @enderror>
                    @error('ditujukan_kepada') <p class="field-error" role="alert">{{ $message }}</p> @enderror
                </div>

                <div class="mt-5 rounded-xl border border-line bg-paper p-4">
                    <label for="penandatangan_id" class="label">Ditandatangani Oleh</label>
                    @if ($isUnit)
                        <div class="rounded-lg border border-line bg-white px-3.5 py-3">
                            <p class="font-semibold">{{ auth()->user()->name }}</p>
                            <p class="mt-0.5 text-sm text-ink-soft">{{ auth()->user()->jabatan ?: auth()->user()->role->label() }}</p>
                            <p class="mt-2 text-xs text-ink-soft">Sesuai role Unit, surat hanya dapat ditandatangani oleh akun yang sedang login.</p>
                        </div>
                    @else
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div><label for="penandatangan_nama" class="label">Nama penandatangan</label><input id="penandatangan_nama" name="penandatangan_nama" type="text" value="{{ old('penandatangan_nama') }}" maxlength="150" class="input" required placeholder="Nama lengkap">@error('penandatangan_nama') <p class="field-error" role="alert">{{ $message }}</p> @enderror</div>
                            <div><label for="penandatangan_jabatan" class="label">Posisi / Jabatan</label><input id="penandatangan_jabatan" name="penandatangan_jabatan" type="text" value="{{ old('penandatangan_jabatan') }}" maxlength="150" class="input" required placeholder="Contoh: Rektor">@error('penandatangan_jabatan') <p class="field-error" role="alert">{{ $message }}</p> @enderror</div>
                        </div>
                        <p class="hint">Sekretariat dan Super Admin dapat menerbitkan surat atas nama siapa pun, termasuk penandatangan yang belum terdaftar sebagai pengguna.</p>
                    @endif
                </div>
            </section>

            <section class="panel p-6 lg:col-span-2" aria-labelledby="judul-qr">
                <h2 id="judul-qr" class="text-base font-semibold">QR Code Verifikasi</h2>

                <div class="mt-5 rounded-xl border border-line bg-paper p-4" aria-live="polite">
                    <p class="text-sm font-semibold">Pratinjau QR Code</p>
                    <div data-live-qr class="mx-auto mt-3 flex min-h-40 w-40 items-center justify-center rounded-lg border border-dashed border-line bg-white p-3 text-center text-xs text-ink-soft">
                        Masukkan nomor surat dan pilih jenis surat untuk menampilkan QR.
                    </div>
                    <p data-live-qr-url class="mt-3 break-all text-xs text-ink-soft"></p>
                    
                    <button type="button" data-download-qr disabled class="btn-primary mt-4 w-full transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                        <x-icon name="unduh" class="size-4"/>
                        Download QR Code
                    </button>
                    <p class="mt-2 text-xs text-ink-soft text-center text-amber-700 font-medium">Anda wajib mengunduh QR Code terlebih dahulu untuk melanjutkan.</p>
                </div>

                <button type="button" id="btn-next-step" disabled class="btn-primary btn-lg mt-6 w-full transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                    Selanjutnya: Upload PDF &nbsp; &rarr;
                </button>
            </section>
        </div>

        <!-- ================= STEP 2: UPLOAD PDF ================= -->
        <div id="step-2-container" class="hidden grid items-start gap-6 lg:grid-cols-5 mt-6">
            <section class="panel p-6 lg:col-span-3 lg:col-start-2" aria-labelledby="judul-berkas">
                <h2 id="judul-berkas" class="text-base font-semibold text-center">Upload Surat Ber-QR Code</h2>
                <p class="mt-2 text-sm text-ink-soft text-center mb-6">Pastikan Anda telah memasukkan QR Code yang diunduh sebelumnya ke dalam dokumen surat asli sebelum menyimpannya sebagai PDF, lalu unggah file PDF tersebut di bawah ini.</p>

                <div class="mt-5">
                    <span class="label sr-only" id="label-berkas">Upload PDF Surat</span>
                    <label data-dropzone data-max-kb="{{ $maksKb }}" for="berkas" class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-pine-300 bg-pine-50/60 px-4 py-8 text-center transition-colors hover:border-emerald-600 hover:bg-emerald-50 has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-emerald-600">
                        <input id="berkas" name="berkas" type="file" accept="application/pdf,.pdf" class="sr-only" aria-labelledby="label-berkas" required>
                        <div data-dz-empty>
                            <x-icon name="unggah" class="mx-auto size-8 text-emerald-700"/>
                            <p class="mt-3 text-sm font-medium">Tarik PDF ke sini atau <span class="text-emerald-700 underline">pilih dari komputer</span></p>
                            <p class="mt-1 text-xs text-ink-soft">Hanya PDF, maksimal {{ number_format($maksKb / 1024, 0) }} MB, tanpa kata sandi.</p>
                        </div>
                        <div data-dz-filled class="hidden w-full">
                            <x-icon name="cek" class="mx-auto size-8 text-emerald-700"/>
                            <p class="mt-3 break-all text-sm font-semibold" data-dz-name></p>
                            <p class="mt-0.5 text-xs text-ink-soft" data-dz-size></p>
                            <button type="button" data-dz-clear class="mt-3 text-xs font-medium text-red-700 underline">Ganti berkas</button>
                        </div>
                    </label>
                    <p data-dz-error class="field-error" role="alert"></p>
                    @error('berkas') <p class="field-error" role="alert">{{ $message }}</p> @enderror
                </div>

                <div class="mt-5 flex items-center gap-4 rounded-lg bg-pine-50 p-4">
                    <x-letak-qr class="w-16 shrink-0 drop-shadow-sm"/>
                    <p class="text-sm text-ink-soft">Sistem kami juga akan memberikan cap cadangan otomatis (stamping) di <strong class="font-semibold text-ink">kanan bawah halaman terakhir</strong>. Biarkan area tersebut cukup longgar.</p>
                </div>

                <div class="mt-6 flex flex-col-reverse sm:flex-row gap-3">
                    <button type="button" id="btn-prev-step" class="btn-secondary w-full sm:w-1/3 justify-center">
                        &larr; &nbsp; Kembali
                    </button>
                    <button type="submit" class="btn-primary w-full sm:w-2/3 justify-center" @disabled($jenisSurat->isEmpty())>
                        <x-icon name="qr" class="size-5"/>
                        Simpan Surat (Draft)
                    </button>
                </div>
                <p class="hint text-center mt-4">Surat ini akan otomatis disimpan dengan status Draf.</p>
            </section>
        </div>
    </form>

    {{-- Script untuk transisi UI Multi-step form --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnDownloadQr = document.querySelector('[data-download-qr]');
            const btnNext = document.getElementById('btn-next-step');
            const btnPrev = document.getElementById('btn-prev-step');
            const step1 = document.getElementById('step-1-container');
            const step2 = document.getElementById('step-2-container');

            // Aktifkan tombol next jika QR sudah didownload
            if(btnDownloadQr && btnNext) {
                btnDownloadQr.addEventListener('click', function() {
                    btnNext.disabled = false;
                    btnNext.classList.remove('opacity-50', 'cursor-not-allowed', 'disabled:opacity-50', 'disabled:cursor-not-allowed');
                });
            }

            // Aksi tombol next (Sembunyikan step 1, munculkan step 2)
            if(btnNext && step1 && step2) {
                btnNext.addEventListener('click', function() {
                    step1.classList.add('hidden');
                    step2.classList.remove('hidden');
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            }

            // Aksi tombol kembali (Sembunyikan step 2, munculkan step 1)
            if(btnPrev && step1 && step2) {
                btnPrev.addEventListener('click', function() {
                    step2.classList.add('hidden');
                    step1.classList.remove('hidden');
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            }

            // Jika form memiliki error (misal file kebesaran) saat disubmit, langsung arahkan kembali ke Step 2
            @if($errors->has('berkas'))
                if(step1 && step2) {
                    step1.classList.add('hidden');
                    step2.classList.remove('hidden');
                }
            @endif
        });
    </script>
</x-layouts.admin>