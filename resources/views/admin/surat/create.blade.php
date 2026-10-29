<x-layouts.admin title="Buat Surat Baru">
    <div class="mb-6">
        <p class="text-sm font-semibold uppercase tracking-[0.16em] text-[#e7553c]">Penerbitan dokumen</p>
        <h1 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900">Buat surat baru</h1>
        <p class="mt-1 text-sm text-slate-500">Isi data surat, unduh QR Code, lalu upload PDF surat Anda.</p>
    </div>

    <form method="POST" action="{{ route('admin.surat.store') }}" enctype="multipart/form-data" novalidate>
        @csrf

        <!-- ================= STEP 1: FORMULIR & QR CODE ================= -->
        <div id="step-1-container" class="grid items-start gap-6 lg:grid-cols-5">
            <section class="panel border-slate-200 p-6 lg:col-span-3" aria-labelledby="judul-data">
                <h2 id="judul-data" class="text-base font-semibold text-slate-900">Informasi surat</h2>

                <div class="mt-5">
                    <label for="nama_instansi" class="label text-slate-700">Nama Instansi Penerbit</label>
                    <input id="nama_instansi" name="nama_instansi" type="text" value="{{ old('nama_instansi') }}" required maxlength="255" class="input focus:border-[#e7553c] focus:ring-[#e7553c]/25" @error('nama_instansi') aria-invalid="true" @enderror>
                    @error('nama_instansi') <p class="field-error" role="alert">{{ $message }}</p> @enderror
                </div>

                <div class="mt-5">
                    <label for="jenis_surat_id" class="label text-slate-700">Jenis Surat</label>
                    <select id="jenis_surat_id" name="jenis_surat_id" class="input focus:border-[#e7553c] focus:ring-[#e7553c]/25" required>
                        <option value="">Pilih jenis surat</option>
                        @foreach ($jenisSurat as $jenis)
                            <option value="{{ $jenis->id }}" @selected((string) old('jenis_surat_id') === (string) $jenis->id)>{{ $jenis->nama }} ({{ $jenis->kode }})</option>
                        @endforeach
                    </select>
                    @error('jenis_surat_id') <p class="field-error" role="alert">{{ $message }}</p> @enderror
                </div>

                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="nomor_surat" class="label text-slate-700">Nomor surat</label>
                        <input id="nomor_surat" name="nomor_surat" type="text" value="{{ old('nomor_surat') }}" required maxlength="100" placeholder="001/UND/X/2026" class="input focus:border-[#e7553c] focus:ring-[#e7553c]/25" @error('nomor_surat') aria-invalid="true" @enderror>
                        @error('nomor_surat') <p class="field-error" role="alert">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="tanggal_surat" class="label text-slate-700">Tanggal</label>
                        <input id="tanggal_surat" name="tanggal_surat" type="date" value="{{ old('tanggal_surat', now()->toDateString()) }}" required class="input focus:border-[#e7553c] focus:ring-[#e7553c]/25" @error('tanggal_surat') aria-invalid="true" @enderror>
                        @error('tanggal_surat') <p class="field-error" role="alert">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-5">
                    <label for="perihal" class="label text-slate-700">Subject / Perihal</label>
                    <input id="perihal" name="perihal" type="text" value="{{ old('perihal') }}" required maxlength="255" class="input focus:border-[#e7553c] focus:ring-[#e7553c]/25" @error('perihal') aria-invalid="true" @enderror>
                    @error('perihal') <p class="field-error" role="alert">{{ $message }}</p> @enderror
                </div>

                <div class="mt-5">
                    <label for="ditujukan_kepada" class="label text-slate-700">Ditujukan Kepada</label>
                    <input id="ditujukan_kepada" name="ditujukan_kepada" type="text" value="{{ old('ditujukan_kepada') }}" required maxlength="255" class="input focus:border-[#e7553c] focus:ring-[#e7553c]/25" @error('ditujukan_kepada') aria-invalid="true" @enderror>
                    @error('ditujukan_kepada') <p class="field-error" role="alert">{{ $message }}</p> @enderror
                </div>

                <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <label for="penandatangan_id" class="label text-slate-700">Ditandatangani Oleh</label>
                    @if ($isUnit)
                        <div class="rounded-lg border border-slate-200 bg-white px-3.5 py-3 shadow-sm">
                            <p class="font-semibold text-slate-900">{{ auth()->user()->name }}</p>
                            <p class="mt-0.5 text-sm text-slate-500">{{ auth()->user()->jabatan ?: auth()->user()->role->label() }}</p>
                        </div>
                    @else
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="penandatangan_nama" class="label text-slate-700">Nama penandatangan</label>
                                <input id="penandatangan_nama" name="penandatangan_nama" type="text" value="{{ old('penandatangan_nama') }}" class="input focus:border-[#e7553c] focus:ring-[#e7553c]/25" required placeholder="Nama lengkap">
                            </div>
                            <div>
                                <label for="penandatangan_jabatan" class="label text-slate-700">Posisi / Jabatan</label>
                                <input id="penandatangan_jabatan" name="penandatangan_jabatan" type="text" value="{{ old('penandatangan_jabatan') }}" class="input focus:border-[#e7553c] focus:ring-[#e7553c]/25" required placeholder="Contoh: Rektor">
                            </div>
                        </div>
                    @endif
                </div>
            </section>

            <section class="panel border-slate-200 p-6 lg:col-span-2" aria-labelledby="judul-qr">
                <h2 id="judul-qr" class="text-base font-semibold text-slate-900">Desain & QR Code</h2>

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <label for="qr_color" class="text-xs font-semibold text-slate-800">Warna QR</label>
                        <div class="mt-2 flex items-center gap-3">
                            <input type="color" id="qr_color" name="qr_color" value="#000000" class="h-9 w-full cursor-pointer rounded border border-slate-300 p-1">
                        </div>
                    </div>
                    
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <label for="logo_qr" class="text-xs font-semibold text-slate-800">Logo Tengah</label>
                        <input type="file" id="logo_qr" name="logo_qr" accept="image/png, image/jpeg, image/jpg" class="mt-2 block w-full text-xs text-slate-500 file:mr-2 file:cursor-pointer file:rounded-md file:border-0 file:bg-slate-200 file:px-2 file:py-1.5 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-slate-300">
                    </div>
                </div>
                @error('qr_color') <p class="field-error mt-1">{{ $message }}</p> @enderror
                @error('logo_qr') <p class="field-error mt-1">{{ $message }}</p> @enderror

                <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4" aria-live="polite">
                    <p class="text-sm font-semibold text-slate-800">Pratinjau Hasil</p>
                    <div data-live-qr class="mx-auto mt-3 flex min-h-48 w-48 items-center justify-center overflow-hidden rounded-lg border border-dashed border-slate-300 bg-white p-2 text-center text-xs text-slate-500 shadow-sm transition-all">
                        Isi data & jenis surat untuk pratinjau.
                    </div>
                    
                    <button type="button" data-download-qr disabled class="btn-primary mt-4 w-full transition-all disabled:cursor-not-allowed disabled:opacity-50">
                        <x-icon name="unduh" class="size-4"/>
                        Download QR Code
                    </button>
                    <p class="mt-2 text-center font-medium text-[11px] text-slate-500">Wajib diunduh untuk melanjutkan.</p>
                </div>

                <button type="button" id="btn-next-step" disabled class="btn-primary btn-lg mt-6 w-full transition-all disabled:cursor-not-allowed disabled:opacity-50">
                    Selanjutnya: Upload PDF &nbsp; &rarr;
                </button>
            </section>
        </div>

        <!-- ================= STEP 2: UPLOAD PDF ================= -->
        <div id="step-2-container" class="mt-6 hidden grid items-start gap-6 lg:grid-cols-5">
            <section class="panel border-slate-200 p-6 lg:col-span-3 lg:col-start-2">
                <h2 class="text-center text-base font-semibold text-slate-900">Upload Surat Ber-QR Code</h2>
                <p class="mb-6 mt-2 text-center text-sm text-slate-500">Pastikan Anda telah memasukkan QR Code yang diunduh sebelumnya ke dalam dokumen surat asli sebelum menyimpannya sebagai PDF, lalu unggah file PDF tersebut di bawah ini.</p>

                <div class="mt-5">
                    <label data-dropzone data-max-kb="{{ $maksKb }}" for="berkas" class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center transition-colors hover:border-[#e7553c] hover:bg-slate-100 has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-[#e7553c]">
                        <input id="berkas" name="berkas" type="file" accept="application/pdf,.pdf" class="sr-only" required>
                        <div data-dz-empty>
                            <x-icon name="unggah" class="mx-auto size-8 text-[#e7553c]"/>
                            <p class="mt-3 text-sm font-medium text-slate-700">Tarik PDF ke sini atau <span class="text-[#e7553c] underline">pilih dari komputer</span></p>
                            <p class="mt-1 text-xs text-slate-500">Hanya PDF, maksimal {{ number_format($maksKb / 1024, 0) }} MB, tanpa kata sandi.</p>
                        </div>
                        <div data-dz-filled class="hidden w-full">
                            <x-icon name="cek" class="mx-auto size-8 text-[#e7553c]"/>
                            <p class="mt-3 break-all text-sm font-semibold text-slate-900" data-dz-name></p>
                            <p class="mt-0.5 text-xs text-slate-500" data-dz-size></p>
                            <button type="button" data-dz-clear class="mt-3 text-xs font-medium text-red-600 underline">Ganti berkas</button>
                        </div>
                    </label>
                    <p data-dz-error class="field-error" role="alert"></p>
                    @error('berkas') <p class="field-error" role="alert">{{ $message }}</p> @enderror
                </div>

                <div class="mt-6 flex flex-col items-center justify-center rounded-xl border border-slate-100 bg-slate-50 p-6">
                    <p class="mb-4 text-xs font-bold uppercase tracking-widest text-slate-400">Contoh Posisi QR Code</p>
                    <x-letak-qr class="w-32 drop-shadow-md sm:w-40"/>
                </div>

                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row">
                    <button type="button" id="btn-prev-step" class="btn-secondary w-full justify-center sm:w-1/3">&larr; &nbsp; Kembali</button>
                    <button type="submit" class="btn-primary w-full justify-center sm:w-2/3">
                        <x-icon name="qr" class="size-5"/> Simpan Surat (Draft)
                    </button>
                </div>
            </section>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnDownloadQr = document.querySelector('[data-download-qr]');
            const btnNext = document.getElementById('btn-next-step');
            const btnPrev = document.getElementById('btn-prev-step');
            const step1 = document.getElementById('step-1-container');
            const step2 = document.getElementById('step-2-container');

            if(btnNext && step1 && step2) {
                btnNext.addEventListener('click', function() { step1.classList.add('hidden'); step2.classList.remove('hidden'); window.scrollTo({ top: 0, behavior: 'smooth' }); });
                btnPrev.addEventListener('click', function() { step2.classList.add('hidden'); step1.classList.remove('hidden'); window.scrollTo({ top: 0, behavior: 'smooth' }); });
            }
            @if($errors->has('berkas')) if(step1 && step2) { step1.classList.add('hidden'); step2.classList.remove('hidden'); } @endif

            const nomorInput = document.getElementById('nomor_surat');
            const jenisInput = document.getElementById('jenis_surat_id');
            const logoInput = document.getElementById('logo_qr');
            const colorInput = document.getElementById('qr_color');
            const qrContainer = document.querySelector('[data-live-qr]');
            
            function updateQr() {
                if(!nomorInput.value || !jenisInput.value) return;
                
                const formData = new FormData();
                formData.append('nomor_surat', nomorInput.value);
                formData.append('jenis_surat_id', jenisInput.value);
                formData.append('qr_color', colorInput.value);
                if(logoInput.files.length > 0) formData.append('logo_qr', logoInput.files[0]);
                formData.append('_token', '{{ csrf_token() }}');
                
                qrContainer.innerHTML = '<span class="animate-pulse text-xs text-slate-500">Memuat gambar...</span>';
                
                fetch('{{ route('admin.qr-preview') }}', { method: 'POST', body: formData })
                .then(async res => {
                    if(!res.ok) throw new Error('Gagal');
                    const blob = await res.blob();
                    const url = URL.createObjectURL(blob);
                    
                    qrContainer.innerHTML = `<img src="${url}" class="w-full h-full object-contain drop-shadow-sm" alt="QR Code">`;
                    qrContainer.classList.remove('border-dashed', 'p-2');
                    
                    btnDownloadQr.disabled = false;
                    btnDownloadQr.onclick = (e) => {
                        e.preventDefault();
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = `QR_${nomorInput.value.replace(/[\/\\]/g, '-')}.svg`;
                        a.click();
                        if(btnNext) btnNext.disabled = false;
                    };
                })
                .catch(err => { qrContainer.innerHTML = '<span class="text-red-500 text-xs">Gagal memuat pratinjau.</span>'; });
            }

            let timeout;
            function debounceUpdateQr() { clearTimeout(timeout); timeout = setTimeout(updateQr, 500); }
            
            if(nomorInput) nomorInput.addEventListener('input', debounceUpdateQr);
            if(jenisInput) jenisInput.addEventListener('change', updateQr);
            if(logoInput) logoInput.addEventListener('change', updateQr);
            if(colorInput) colorInput.addEventListener('input', debounceUpdateQr);
        });
    </script>
</x-layouts.admin>