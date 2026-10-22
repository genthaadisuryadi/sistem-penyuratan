<x-layouts.admin title="Pengaturan">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold tracking-tight">Pengaturan</h1>
        <p class="mt-1 text-sm text-ink-soft">Kelola profil dan keamanan akun Anda.</p>
    </div>

    <div class="grid items-start gap-6 lg:grid-cols-2">
        <section class="panel p-6" aria-labelledby="judul-profil">
            <h2 id="judul-profil" class="text-base font-semibold">Profil</h2>
            @if (session('status_profil'))
                <p role="status" class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3.5 py-3 text-sm text-emerald-900">{{ session('status_profil') }}</p>
            @endif
            <form method="POST" action="{{ route('admin.pengaturan.profil') }}" class="mt-5 space-y-5">
                @csrf @method('PUT')
                <div>
                    <label for="name" class="label">Nama</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required class="input" @error('name') aria-invalid="true" @enderror>
                    @error('name') <p class="field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="jabatan" class="label">Jabatan / Posisi</label>
                    <input id="jabatan" name="jabatan" type="text" value="{{ old('jabatan', $user->jabatan) }}" maxlength="150" class="input">
                    @error('jabatan') <p class="field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="email" class="label">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required class="input" @error('email') aria-invalid="true" @enderror>
                    @error('email') <p class="field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="username" class="label">Username</label>
                    <input id="username" name="username" type="text" value="{{ old('username', $user->username) }}" autocapitalize="none" class="input" @error('username') aria-invalid="true" @enderror>
                    @error('username') <p class="field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <p class="text-sm text-ink-soft">Peran: <strong class="text-ink">{{ $user->role->label() }}</strong></p>
                <button type="submit" class="btn-primary">Simpan profil</button>
            </form>
        </section>

        <section class="panel p-6" aria-labelledby="judul-sandi">
            <h2 id="judul-sandi" class="text-base font-semibold">Ganti kata sandi</h2>
            @if (session('status_sandi'))
                <p role="status" class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3.5 py-3 text-sm text-emerald-900">{{ session('status_sandi') }}</p>
            @endif
            <form method="POST" action="{{ route('admin.pengaturan.sandi') }}" class="mt-5 space-y-5">
                @csrf @method('PUT')
                <div>
                    <label for="password_lama" class="label">Kata sandi saat ini</label>
                    <input id="password_lama" name="password_lama" type="password" autocomplete="current-password" required class="input" @error('password_lama') aria-invalid="true" @enderror>
                    @error('password_lama') <p class="field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password" class="label">Kata sandi baru</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" required class="input" @error('password') aria-invalid="true" @enderror>
                    <p class="hint">Minimal 10 karakter, mengandung huruf besar, huruf kecil, dan angka.</p>
                    @error('password') <p class="field-error" role="alert">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="label">Ulangi kata sandi baru</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required class="input">
                </div>
                <button type="submit" class="btn-primary">Ganti kata sandi</button>
            </form>
        </section>
    </div>
</x-layouts.admin>
