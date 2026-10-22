<x-layouts.guest title="Kata sandi baru">
    <h1 class="text-xl font-semibold tracking-tight">Buat kata sandi baru</h1>
    <p class="mt-1.5 text-sm text-ink-soft">Minimal 10 karakter, kombinasi huruf besar, huruf kecil, dan angka.</p>

    <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-5" data-loading-text="Menyimpan...">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label for="email" class="label">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required autocomplete="email" class="input" @error('email') aria-invalid="true" @enderror>
            @error('email')
                <p class="field-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="label">Kata sandi baru</label>
            <div class="relative">
                <input id="password" name="password" type="password" required autocomplete="new-password" class="input pr-11" @error('password') aria-invalid="true" @enderror>
                <button type="button" data-toggle-password="password" aria-pressed="false" aria-label="Tampilkan kata sandi"
                        class="absolute inset-y-0 right-0 flex w-11 items-center justify-center rounded-r-lg text-ink-soft hover:text-ink">
                    <span data-eye="on"><x-icon name="mata"/></span>
                    <span data-eye="off" class="hidden"><x-icon name="mata-coret"/></span>
                </button>
            </div>
            @error('password')
                <p class="field-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="label">Ulangi kata sandi baru</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="input">
        </div>

        <button type="submit" class="btn-primary w-full !py-3">Simpan kata sandi baru</button>
    </form>
</x-layouts.guest>
