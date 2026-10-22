<form method="POST" action="{{ route('verifikasi.cari') }}" class="space-y-2" role="search" data-loading-text="Memeriksa...">
    @csrf
    <label for="kode" class="label">Kode verifikasi</label>
    <div class="flex gap-2">
        <input id="kode" name="kode" type="text" inputmode="text" autocomplete="off" autocapitalize="characters" spellcheck="false" maxlength="11"
               data-kode-input value="{{ old('kode') }}" placeholder="ABCDE-FGHJK" required
               class="input flex-1 !rounded-xl !py-3.5 text-lg font-semibold tracking-[0.16em] placeholder:font-normal placeholder:tracking-normal" @error('kode') aria-invalid="true" @enderror>
        <button type="submit" class="btn-primary shrink-0 !rounded-xl px-6">
            <x-icon name="cari" class="size-4"/>
            Periksa
        </button>
    </div>
    @error('kode')
        <p class="field-error" role="alert">{{ $message }}</p>
    @enderror
</form>
