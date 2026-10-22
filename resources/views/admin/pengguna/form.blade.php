<x-layouts.admin :title="$pengguna->exists ? 'Ubah Pengguna' : 'Tambah Pengguna'">
    <div class="mb-6">
        <a href="{{ route('admin.pengguna.index') }}" class="text-sm font-medium text-emerald-700 hover:underline">← Kembali ke pengguna</a>
        <h1 class="mt-3 text-2xl font-semibold tracking-tight">{{ $pengguna->exists ? 'Ubah pengguna' : 'Tambah pengguna' }}</h1>
        <p class="mt-1 text-sm text-ink-soft">Setiap akun dibuat oleh administrator. Tidak ada menu registrasi publik.</p>
    </div>

    <form method="POST" action="{{ $pengguna->exists ? route('admin.pengguna.update', $pengguna) : route('admin.pengguna.store') }}" class="panel max-w-3xl p-6">
        @csrf
        @if ($pengguna->exists) @method('PUT') @endif
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="name" class="label">Nama lengkap</label>
                <input id="name" name="name" value="{{ old('name', $pengguna->name) }}" class="input" required maxlength="255">
                @error('name')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            
            <div>
                <label for="jabatan" class="label">Jabatan / Posisi</label>
                <input id="jabatan" name="jabatan" value="{{ old('jabatan', $pengguna->jabatan) }}" class="input" required maxlength="150" placeholder="Contoh: Kepala Unit">
                @error('jabatan')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            
            <div>
                <label for="email" class="label">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $pengguna->email) }}" class="input" required>
                @error('email')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            
            <div>
                <label for="role" class="label">Role</label>
                <select id="role" name="role" class="input" required>
                    @foreach ($roles as $role)
                        <option value="{{ $role->value }}" @selected(old('role', $pengguna->exists ? $pengguna->role?->value : 'unit') === $role->value)>{{ $role->label() }}</option>
                    @endforeach
                </select>
                @error('role')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            
            <div class="flex items-center gap-3 rounded-lg border border-line bg-paper px-4 py-3 sm:col-span-2">
                <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $pengguna->exists ? $pengguna->is_active : true)) class="size-4 accent-emerald-600">
                <label for="is_active" class="text-sm font-medium">Akun aktif</label>
            </div>
            
            @if (!$pengguna->exists)
                <div class="sm:col-span-2">
                    <p class="hint">Catatan: Password secara otomatis akan disamakan dengan Alamat Email pengguna yang didaftarkan.</p>
                </div>
            @endif
        </div>
        <div class="mt-6 flex flex-wrap gap-3">
            <button type="submit" class="btn-primary">Simpan pengguna</button>
            <a href="{{ route('admin.pengguna.index') }}" class="btn-secondary">Batal</a>
        </div>
    </form>
</x-layouts.admin>