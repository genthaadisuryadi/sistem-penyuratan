<x-layouts.guest title="Masuk">
    <h1 class="text-xl font-semibold tracking-tight">Masuk ke dasbor admin</h1>
    <p class="mt-1.5 text-sm text-ink-soft">Gunakan akun yang didaftarkan oleh superadmin.</p>

    @if (session('status'))
        <div role="status" class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 px-3.5 py-3 text-sm text-emerald-900">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ url(config('penyuratan.login_path')) }}" class="mt-6 space-y-5" data-loading-text="Memeriksa...">
        @csrf

        <div>
            <label for="login" class="label">Email atau username</label>
            <input id="login" name="login" type="text" value="{{ old('login') }}" required autofocus autocomplete="username" autocapitalize="none" spellcheck="false"
                   class="input" @error('login') aria-invalid="true" aria-describedby="login-error" @enderror>
            @error('login')
                <p id="login-error" class="field-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="label">Kata sandi</label>
            <div class="relative">
                <input id="password" name="password" type="password" required autocomplete="current-password" class="input pr-11">
                <button type="button" data-toggle-password="password" aria-pressed="false" aria-label="Tampilkan kata sandi"
                        class="absolute inset-y-0 right-0 flex w-11 items-center justify-center rounded-r-lg text-ink-soft hover:text-ink">
                    <span data-eye="on"><x-icon name="mata"/></span>
                    <span data-eye="off" class="hidden"><x-icon name="mata-coret"/></span>
                </button>
            </div>
        </div>

        <button type="submit" class="btn-primary w-full !py-3">Masuk</button>

        <p class="text-center text-sm">
            <a href="{{ route('password.request') }}" class="font-medium text-emerald-700 hover:text-emerald-800 hover:underline">Lupa kata sandi?</a>
        </p>
    </form>
</x-layouts.guest>
