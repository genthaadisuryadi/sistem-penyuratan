<x-layouts.guest title="Masuk">
    <h1 class="text-xl font-semibold tracking-tight text-slate-900">Masuk ke dasbor admin</h1>
    <p class="mt-1.5 text-sm text-slate-500">Gunakan email dan kata sandi yang didaftarkan oleh administrator.</p>

    @if (session('status'))
        <div role="status" class="mt-5 rounded-lg border border-red-200 bg-red-50 px-3.5 py-3 text-sm text-red-900">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ url(config('penyuratan.login_path')) }}" class="mt-6 space-y-5" data-loading-text="Memeriksa...">
        @csrf

        <div>
            <label for="email" class="label">Alamat Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" autocapitalize="none" spellcheck="false"
                   class="input focus:border-[#e7553c] focus:ring-[#e7553c]" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
            @error('email')
                <p id="email-error" class="field-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="label">Kata sandi</label>
            <div class="relative">
                <input id="password" name="password" type="password" required autocomplete="current-password" class="input pr-11 focus:border-[#e7553c] focus:ring-[#e7553c]">
                <button type="button" data-toggle-password="password" aria-pressed="false" aria-label="Tampilkan kata sandi"
                        class="absolute inset-y-0 right-0 flex w-11 items-center justify-center rounded-r-lg text-slate-400 hover:text-[#e7553c]">
                    <span data-eye="on"><x-icon name="mata"/></span>
                    <span data-eye="off" class="hidden"><x-icon name="mata-coret"/></span>
                </button>
            </div>
        </div>

        {{-- Tombol masuk sekarang menjadi elemen terakhir, tautan lupa kata sandi dihapus --}}
        <button type="submit" class="btn-primary w-full !py-3 mt-2">Masuk</button>
    </form>
</x-layouts.guest>