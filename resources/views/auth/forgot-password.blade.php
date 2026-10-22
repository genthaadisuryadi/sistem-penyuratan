<x-layouts.guest title="Lupa kata sandi">
    <h1 class="text-xl font-semibold tracking-tight">Atur ulang kata sandi</h1>
    <p class="mt-1.5 text-sm text-ink-soft">Masukkan email akun Anda. Kami kirim tautan untuk membuat kata sandi baru.</p>

    @if (session('status'))
        <div role="status" class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 px-3.5 py-3 text-sm text-emerald-900">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-5" data-loading-text="Mengirim...">
        @csrf
        <div>
            <label for="email" class="label">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="input" @error('email') aria-invalid="true" @enderror>
            @error('email')
                <p class="field-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="btn-primary w-full !py-3">Kirim tautan</button>

        <p class="text-center text-sm">
            <a href="{{ route('login') }}" class="font-medium text-emerald-700 hover:text-emerald-800 hover:underline">Kembali ke halaman masuk</a>
        </p>
    </form>
</x-layouts.guest>
