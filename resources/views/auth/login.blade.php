<x-layouts.guest title="Masuk">
    <h1 class="text-xl font-semibold tracking-tight">Masuk ke dasbor admin</h1>
    <p class="mt-1.5 text-sm text-ink-soft">Gunakan email yang didaftarkan oleh administrator.</p>

    @if (session('status'))
        <div role="status" class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 px-3.5 py-3 text-sm text-emerald-900">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ url(config('penyuratan.login_path')) }}" class="mt-6 space-y-5" data-loading-text="Memeriksa...">
        @csrf

        <div>
            <label for="email" class="label">Alamat Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" autocapitalize="none" spellcheck="false"
                   class="input" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
            @error('email')
                <p id="email-error" class="field-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="btn-primary w-full !py-3">Masuk</button>
    </form>
</x-layouts.guest>