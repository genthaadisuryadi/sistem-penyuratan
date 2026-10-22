<dialog id="dialog-cabut" aria-labelledby="judul-cabut" @if($errors->has('alasan')) data-reopen @endif
        class="m-auto w-[calc(100%-2rem)] max-w-lg rounded-2xl border-0 bg-white p-0 text-ink shadow-2xl">
    <form method="POST" action="{{ $aksi ?? '' }}" class="p-6 sm:p-7" data-loading-text="Menyimpan status...">
        @csrf
        <div class="flex items-start gap-4">
            <span class="flex size-11 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-700">
                <x-icon name="cabut"/>
            </span>
            <div>
                <h2 id="judul-cabut" class="text-lg font-semibold">Tarik surat?</h2>
                <p class="mt-1 text-sm text-ink-soft">
                    Surat <strong class="text-ink" data-fill="nomor">{{ $nomor ?? '' }}</strong> akan berstatus <strong class="text-red-700">ditarik</strong>.
                    QR Code di kertas tetap bisa dipindai, tetapi halamannya menampilkan peringatan dan berkas tidak bisa diunduh lagi. Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>
        </div>

        <div class="mt-5">
            <label for="alasan" class="label">Alasan penarikan</label>
            <textarea id="alasan" name="alasan" rows="3" required minlength="10" maxlength="1000" class="input"
                      placeholder="Mis. surat diganti dengan nomor 123/ABC/2026 karena ada kesalahan penulisan." @error('alasan') aria-invalid="true" @enderror>{{ old('alasan') }}</textarea>
            <p class="hint">Hanya terlihat oleh admin, tidak ditampilkan di halaman publik.</p>
            @error('alasan')
                <p class="field-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <button type="button" data-modal-close class="btn-secondary">Batal</button>
            <button type="submit" class="btn-danger">Tarik surat</button>
        </div>
    </form>
</dialog>
