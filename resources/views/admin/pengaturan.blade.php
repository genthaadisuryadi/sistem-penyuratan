<x-layouts.admin title="Pengaturan">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Pengaturan</h1>
        <p class="mt-1 text-sm text-slate-500">Kelola profil, keamanan akun, dan sistem Anda.</p>
    </div>

    <!-- PENGATURAN SISTEM (Disembunyikan dari Unit) -->
    @if (!$isUnit)
        <div class="mb-6">
            <section class="panel border-slate-200 p-6 shadow-sm" aria-labelledby="judul-sistem">
                <h2 id="judul-sistem" class="text-base font-semibold text-slate-900">Pengaturan Sistem</h2>
                <p class="mt-1 text-sm text-slate-500">Nama instansi ini akan digunakan sebagai penerbit pada setiap surat ber-QR Code.</p>
                
                @if (session('status_sistem'))
                    <p role="status" class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3.5 py-3 text-sm text-emerald-900">{{ session('status_sistem') }}</p>
                @endif
                
                <form method="POST" action="{{ route('admin.pengaturan.sistem') }}" class="mt-5 max-w-xl space-y-5">
                    @csrf @method('PUT')
                    <div>
                        <label for="nama_instansi" class="label text-slate-700">Nama Instansi / Lembaga</label>
                        <input id="nama_instansi" name="nama_instansi" type="text" value="{{ old('nama_instansi', $namaInstansi) }}" required class="input focus:border-[#e7553c] focus:ring-[#e7553c]/25" @error('nama_instansi') aria-invalid="true" @enderror>
                        @error('nama_instansi') <p class="field-error mt-1" role="alert">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit" class="btn-primary">Simpan Pengaturan Sistem</button>
                </form>
            </section>
        </div>
    @endif

    <!-- PENGATURAN PROFIL & KATA SANDI -->
    <div class="grid items-start gap-6 lg:grid-cols-2">
        <section class="panel border-slate-200 p-6 shadow-sm" aria-labelledby="judul-profil">
            <h2 id="judul-profil" class="text-base font-semibold text-slate-900">Profil</h2>
            @if (session('status_profil'))
                <p role="status" class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3.5 py-3 text-sm text-emerald-900">{{ session('status_profil') }}</p>
            @endif
            <form method="POST" action="{{ route('admin.pengaturan.profil') }}" class="mt-5 space-y-5">
                @csrf @method('PUT')
                <div>
                    <label for="name" class="label text-slate-700">Nama</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required class="input focus:border-[#e7553c] focus:ring-[#e7553c]/25" @error('name') aria-invalid="true" @enderror>
                    @error('name') <p class="field-error mt-1" role="alert">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="jabatan" class="label text-slate-700">Jabatan / Posisi</label>
                    <input id="jabatan" name="jabatan" type="text" value="{{ old('jabatan', $user->jabatan) }}" maxlength="150" class="input focus:border-[#e7553c] focus:ring-[#e7553c]/25">
                    @error('jabatan') <p class="field-error mt-1" role="alert">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="email" class="label text-slate-700">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required class="input focus:border-[#e7553c] focus:ring-[#e7553c]/25" @error('email') aria-invalid="true" @enderror>
                    @error('email') <p class="field-error mt-1" role="alert">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="username" class="label text-slate-700">Username</label>
                    <input id="username" name="username" type="text" value="{{ old('username', $user->username) }}" autocapitalize="none" class="input focus:border-[#e7553c] focus:ring-[#e7553c]/25" @error('username') aria-invalid="true" @enderror>
                    @error('username') <p class="field-error mt-1" role="alert">{{ $message }}</p> @enderror
                </div>
                <p class="text-sm text-slate-500">Peran: <strong class="text-slate-900">{{ $user->role->label() }}</strong></p>
                <button type="submit" class="btn-primary">Simpan Profil</button>
            </form>
        </section>

        <section class="panel border-slate-200 p-6 shadow-sm" aria-labelledby="judul-sandi">
            <h2 id="judul-sandi" class="text-base font-semibold text-slate-900">Ganti Kata Sandi</h2>
            @if (session('status_sandi'))
                <p role="status" class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3.5 py-3 text-sm text-emerald-900">{{ session('status_sandi') }}</p>
            @endif
            <form method="POST" action="{{ route('admin.pengaturan.sandi') }}" class="mt-5 space-y-5">
                @csrf @method('PUT')
                <div>
                    <label for="password_lama" class="label text-slate-700">Kata sandi saat ini</label>
                    <input id="password_lama" name="password_lama" type="password" autocomplete="current-password" required class="input focus:border-[#e7553c] focus:ring-[#e7553c]/25" @error('password_lama') aria-invalid="true" @enderror>
                    @error('password_lama') <p class="field-error mt-1" role="alert">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password" class="label text-slate-700">Kata sandi baru</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" required class="input focus:border-[#e7553c] focus:ring-[#e7553c]/25" @error('password') aria-invalid="true" @enderror>
                    <p class="mt-1 text-xs text-slate-500">Minimal 10 karakter, mengandung huruf besar, huruf kecil, dan angka.</p>
                    @error('password') <p class="field-error mt-1" role="alert">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="label text-slate-700">Ulangi kata sandi baru</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required class="input focus:border-[#e7553c] focus:ring-[#e7553c]/25">
                </div>
                <button type="submit" class="btn-primary">Ganti Kata Sandi</button>
            </form>
        </section>
    </div>
</x-layouts.admin>