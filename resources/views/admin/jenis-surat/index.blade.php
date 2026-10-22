<x-layouts.admin title="Master Jenis Surat">
    <div class="mb-6">
        <p class="text-sm font-semibold uppercase tracking-[0.16em] text-emerald-700">Data master</p>
        <h1 class="mt-1 text-2xl font-semibold tracking-tight">Jenis Surat</h1>
        <p class="mt-1 text-sm text-ink-soft">Setiap jenis memiliki kode unik. Jenis yang sudah digunakan surat dapat dinonaktifkan, tetapi tidak dihapus.</p>
    </div>

    @if (session('status'))<div role="status" class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{{ session('status') }}</div>@endif
    @if ($errors->has('jenis_surat'))<div role="alert" class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $errors->first('jenis_surat') }}</div>@endif

    <section class="panel mb-6 p-6">
        <h2 class="text-base font-semibold">Tambah jenis surat</h2>
        <form method="POST" action="{{ route('admin.jenis-surat.store') }}" class="mt-4 grid gap-4 sm:grid-cols-[1fr_180px_auto] sm:items-end">
            @csrf
            <div><label for="nama" class="label">Nama jenis</label><input id="nama" name="nama" class="input" value="{{ old('nama') }}" required maxlength="100" placeholder="Contoh: Surat Undangan">@error('nama')<p class="field-error">{{ $message }}</p>@enderror</div>
            <div><label for="kode" class="label">Kode jenis</label><input id="kode" name="kode" class="input uppercase" value="{{ old('kode') }}" required maxlength="20" placeholder="UND">@error('kode')<p class="field-error">{{ $message }}</p>@enderror</div>
            <button type="submit" class="btn-primary">Tambah Jenis</button>
        </form>
    </section>

    <div class="grid gap-4">
        @forelse ($jenis as $item)
            <section class="panel p-5" aria-label="Edit {{ $item->nama }}">
                <form method="POST" action="{{ route('admin.jenis-surat.update', $item) }}" class="grid gap-4 sm:grid-cols-[1fr_160px_130px_auto] sm:items-end">
                    @csrf @method('PUT')
                    <div><label for="nama-{{ $item->id }}" class="label">Nama jenis</label><input id="nama-{{ $item->id }}" name="nama" class="input" value="{{ $item->nama }}" required maxlength="100"></div>
                    <div><label for="kode-{{ $item->id }}" class="label">Kode</label><input id="kode-{{ $item->id }}" name="kode" class="input uppercase" value="{{ $item->kode }}" required maxlength="20"></div>
                    <div><label for="aktif-{{ $item->id }}" class="label">Status</label><select id="aktif-{{ $item->id }}" name="is_active" class="input"><option value="1" @selected($item->is_active)>Aktif</option><option value="0" @selected(! $item->is_active)>Nonaktif</option></select></div>
                    <div class="flex items-center gap-2"><button type="submit" class="btn-primary !px-3">Simpan</button></div>
                </form>
                <div class="mt-3 flex items-center justify-between border-t border-line pt-3 text-xs text-ink-soft">
                    <span>{{ $item->surat_count }} surat menggunakan jenis ini</span>
                    @if ($item->surat_count === 0)
                        <form method="POST" action="{{ route('admin.jenis-surat.destroy', $item) }}" onsubmit="return confirm('Hapus jenis surat ini?')">@csrf @method('DELETE')<button class="font-semibold text-red-700 hover:underline" type="submit">Hapus</button></form>
                    @endif
                </div>
            </section>
        @empty
            <div class="panel px-6 py-12 text-center text-sm text-ink-soft">Belum ada data jenis surat.</div>
        @endforelse
    </div>
    <div class="mt-5">{{ $jenis->links() }}</div>
</x-layouts.admin>
