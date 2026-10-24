<form method="POST" action="{{ route('verifikasi.cari') }}" class="space-y-2" role="search" data-loading-text="Mencari...">
    @csrf
    <label for="nomor_surat" class="label text-slate-700">Nomor Surat</label>
    <div class="flex flex-col sm:flex-row gap-2">
        {{-- Input diubah untuk menerima format nomor surat yang panjang dan bergaris miring --}}
        <input id="nomor_surat" name="nomor_surat" type="text" inputmode="text" autocomplete="off" spellcheck="false"
               value="{{ old('nomor_surat') }}" placeholder="Contoh: 001/UND/X/2026" required
               class="input flex-1 !rounded-xl !py-3 text-base font-medium placeholder:font-normal text-slate-900 focus:border-[#e7553c] focus:ring-[#e7553c]/25" 
               @error('nomor_surat') aria-invalid="true" @enderror>
               
        <button type="submit" class="btn-primary shrink-0 !rounded-xl px-6 !py-3">
            <x-icon name="cari" class="size-4"/>
            Cari Surat
        </button>
    </div>
    @error('nomor_surat')
        <p class="field-error" role="alert">{{ $message }}</p>
    @enderror
</form>