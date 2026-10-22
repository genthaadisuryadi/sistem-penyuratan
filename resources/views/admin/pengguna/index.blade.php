<x-layouts.admin title="Manajemen Pengguna">
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.16em] text-emerald-700">Hak akses</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight">Manajemen pengguna</h1>
            <p class="mt-1 text-sm text-ink-soft">Tambah akun, atur role dan jabatan, nonaktifkan akses, serta reset kata sandi.</p>
        </div>
        <a href="{{ route('admin.pengguna.create') }}" class="btn-primary">Tambah Pengguna</a>
    </div>

    @if (session('status'))
        <div role="status" class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{{ session('status') }}</div>
    @endif
    @if ($kataSandiBaru)
        <section class="mb-5 rounded-xl border border-amber-300 bg-amber-50 p-5" aria-live="polite">
            <h2 class="font-semibold text-amber-950">Kata sandi sementara berhasil dibuat</h2>
            <p class="mt-1 text-sm text-amber-900">Kirimkan informasi ini secara aman kepada {{ $kataSandiBaru['nama'] }}. Kata sandi ini hanya ditampilkan pada halaman ini.</p>
            <div class="mt-3 flex flex-wrap items-center gap-3">
                <code class="rounded-lg border border-amber-300 bg-white px-3 py-2 text-sm">{{ $kataSandiBaru['password'] }}</code>
                <button type="button" data-copy="{{ $kataSandiBaru['password'] }}" class="btn-secondary !py-2 !text-xs">Salin kata sandi</button>
            </div>
        </section>
    @endif

    <div class="panel overflow-x-auto">
        <table class="min-w-full divide-y divide-line">
            <thead class="bg-pine-50/70"><tr>
                <th scope="col" class="th">Pengguna</th><th scope="col" class="th">Jabatan</th><th scope="col" class="th">Role</th><th scope="col" class="th">Status</th><th scope="col" class="th">Surat dibuat</th><th scope="col" class="th text-right">Aksi</th>
            </tr></thead>
            <tbody class="divide-y divide-line">
                @forelse ($pengguna as $orang)
                    <tr class="hover:bg-pine-50/50">
                        <td class="td"><p class="font-semibold">{{ $orang->name }}</p><p class="mt-0.5 text-xs text-ink-soft">{{ $orang->username }} · {{ $orang->email }}</p></td>
                        <td class="td">{{ $orang->jabatan ?: '—' }}</td>
                        <td class="td"><span class="rounded-full bg-pine-100 px-2.5 py-1 text-xs font-semibold">{{ $orang->role->label() }}</span></td>
                        <td class="td"><span @class(['rounded-full px-2.5 py-1 text-xs font-semibold', 'bg-emerald-50 text-emerald-800' => $orang->is_active, 'bg-red-50 text-red-800' => ! $orang->is_active])>{{ $orang->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                        <td class="td">{{ number_format($orang->surat_count, 0, ',', '.') }}</td>
                        <td class="td"><div class="flex flex-wrap justify-end gap-2">
                            <a href="{{ route('admin.pengguna.edit', $orang) }}" class="btn-secondary !px-3 !py-1.5 !text-xs">Ubah</a>
                            <form method="POST" action="{{ route('admin.pengguna.reset-password', $orang) }}" onsubmit="return confirm('Reset kata sandi pengguna ini?')">
                                @csrf <button type="submit" class="btn-secondary !px-3 !py-1.5 !text-xs">Reset sandi</button>
                            </form>
                            @if (auth()->id() !== $orang->id)
                                <form method="POST" action="{{ route('admin.pengguna.destroy', $orang) }}" onsubmit="return confirm('Hapus pengguna ini? Jika sudah membuat surat, akun perlu dinonaktifkan saja.')">
                                    @csrf @method('DELETE') <button type="submit" class="btn-danger-outline !px-3 !py-1.5 !text-xs">Hapus</button>
                                </form>
                            @endif
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-sm text-ink-soft">Belum ada pengguna.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-5">{{ $pengguna->links() }}</div>
</x-layouts.admin>
