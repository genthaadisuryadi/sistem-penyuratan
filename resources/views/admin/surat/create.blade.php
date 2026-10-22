<x-layouts.admin title="Buat Surat Baru">
    <div class="mb-6">
        <p class="text-sm font-semibold uppercase tracking-[0.16em] text-emerald-700">Penerbitan dokumen</p>
        <h1 class="mt-1 text-2xl font-semibold tracking-tight">Buat surat baru</h1>
        <p class="mt-1 text-sm text-ink-soft">Isi data surat, unggah PDF, lalu simpan sebagai draf atau publikasikan.</p>
    </div>

    <form method="POST" action="{{ route('admin.surat.store') }}" enctype="multipart/form-data"
          class="grid items-start gap-6 lg:grid-cols-5" data-loading-text="Memproses PDF..."
          data-qr-live data-qr-endpoint="{{ route('admin.qr-preview') }}" data-qr-base-url="{{ rtrim(config('penyuratan.verification_base_url'), '/') }}" novalidate>
        @csrf

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
                <input id="perihal" name="perihal" type="text" value="{{ old('perihal') }}" required maxlength="255" placeholder="Undangan rapat koordinasi" class="input" @error('perihal') aria-invalid="true" @enderror>
                @error('perihal') <p class="field-error" role="alert">{{ $message }}</p> @enderror
            </div>

            <div class="mt-5">
                <label for="ditujukan_kepada" class="label">Ditujukan Kepada</label>
                <input id="ditujukan_kepada" name="ditujukan_kepada" type="text" value="{{ old('ditujukan_kepada') }}" required maxlength="255" placeholder="Kepala Unit / nama penerima" class="input" @error('ditujukan_kepada') aria-invalid="true" @enderror>
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

            <div class="mt-5">
                <label for="status" class="label">Status awal</label>
                <select id="status" name="status" class="input" required>
                    <option value="draf" @selected(old('status', 'draf') === 'draf')>Draf — belum tampil sebagai surat sah</option>
                    <option value="publish" @selected(old('status') === 'publish')>Publish — langsung aktif untuk verifikasi publik</option>
                </select>
                @error('status') <p class="field-error" role="alert">{{ $message }}</p> @enderror
            </div>
        </section>

        <section class="panel p-6 lg:col-span-2" aria-labelledby="judul-berkas">
            <h2 id="judul-berkas" class="text-base font-semibold">PDF dan QR Code</h2>

            <div class="mt-5">
                <span class="label" id="label-berkas">Upload PDF Surat</span>
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

            <div class="mt-5 rounded-xl border border-line bg-paper p-4" aria-live="polite">
                <p class="text-sm font-semibold">Pratinjau QR Verifikasi</p>
                <div data-live-qr class="mx-auto mt-3 flex min-h-40 w-40 items-center justify-center rounded-lg border border-dashed border-line bg-white p-3 text-center text-xs text-ink-soft">
                    Masukkan nomor surat dan pilih jenis surat untuk menampilkan QR.
                </div>
                <p data-live-qr-url class="mt-3 break-all text-xs text-ink-soft"></p>
                <button type="button" data-download-qr disabled class="btn-primary mt-4 w-full">
                    <x-icon name="unduh" class="size-4"/>
                    Download QR Code
                </button>
                <p class="mt-2 text-xs text-ink-soft">QR diperbarui otomatis. Tautan publik aktif setelah surat disimpan dan berstatus Publish.</p>
            </div>

            <div class="mt-5 flex items-center gap-4 rounded-lg bg-pine-50 p-4">
                <x-letak-qr class="w-16 shrink-0 drop-shadow-sm"/>
                <p class="text-sm text-ink-soft">QR pada PDF akan ditempel otomatis di <strong class="font-semibold text-ink">kanan bawah halaman terakhir</strong>. Pastikan area tersebut kosong.</p>
            </div>

            <button type="submit" class="btn-primary btn-lg mt-6 w-full" @disabled($jenisSurat->isEmpty())>
                <x-icon name="qr" class="size-5"/>
                Simpan Surat
            </button>
            <p class="hint text-center">Akun pembuat otomatis dicatat dari sesi login. Status Ditarik mengharuskan alasan.</p>
        </section>
    </form>
</x-layouts.admin>
