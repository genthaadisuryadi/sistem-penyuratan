<x-layouts.admin title="Arsip Surat">
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.16em] text-emerald-700">Dokumen</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight">Arsip surat</h1>
            <p class="mt-1 text-sm text-ink-soft">@if(auth()->user()->dapatKelolaSemuaSurat()) Seluruh surat dalam sistem. @else Surat yang dibuat oleh akun Anda. @endif</p>
        </div>
        <a href="{{ route('admin.surat.create') }}" class="btn-primary"><x-icon name="surat-baru" class="size-4"/>Buat Surat Baru</a>
    </div>

    <form method="GET" action="{{ route('admin.surat.index') }}" class="mb-4 flex flex-col gap-3 sm:flex-row" role="search">
        <div class="relative flex-1"><x-icon name="cari" class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-pine-300"/><label for="q" class="sr-only">Cari surat</label><input id="q" name="q" type="search" value="{{ $q }}" placeholder="Cari nomor, perihal, penerima, atau kode" class="input pl-10"></div>
        <label for="status" class="sr-only">Status surat</label>
        <select id="status" name="status" class="input sm:w-44"><option value="">Semua status</option>@foreach (\App\Enums\StatusSurat::cases() as $opsi)<option value="{{ $opsi->value }}" @selected($status === $opsi)>{{ $opsi->label() }}</option>@endforeach</select>
        <button type="submit" class="btn-secondary">Cari</button>
        @if ($q !== '' || $status)<a href="{{ route('admin.surat.index') }}" class="btn-secondary border-transparent bg-transparent hover:bg-pine-100">Hapus filter</a>@endif
    </form>

    <div class="panel overflow-x-auto">
        <table class="min-w-full divide-y divide-line">
            <thead class="bg-pine-50/70"><tr>
                <th scope="col" class="th">Nomor / Jenis Surat</th><th scope="col" class="th">Tanggal / Penerima</th><th scope="col" class="th">Penandatangan</th><th scope="col" class="th">Dibuat Oleh</th><th scope="col" class="th">Status</th><th scope="col" class="th text-right">Aksi</th>
            </tr></thead>
            <tbody class="divide-y divide-line">
                @forelse ($daftar as $s)
                    <tr class="hover:bg-pine-50/50">
                        <td class="td max-w-xs"><a href="{{ route('admin.surat.show', $s) }}" class="font-semibold text-ink hover:text-emerald-700 hover:underline">{{ $s->nomor_surat }}</a><p class="mt-0.5 text-xs font-semibold text-emerald-700">{{ $s->jenisSurat?->nama ?? 'Jenis tidak tersedia' }}{{ $s->jenisSurat ? ' · '.$s->jenisSurat->kode : '' }}</p><p class="mt-1 line-clamp-2 text-ink-soft">{{ $s->perihal }}</p></td>
                        <td class="td"><p class="whitespace-nowrap">{{ $s->tanggal_surat->translatedFormat('j M Y') }}</p><p class="mt-1 text-xs text-ink-soft">Kepada: {{ $s->ditujukan_kepada ?: '—' }}</p></td>
                        <td class="td"><p class="font-medium">{{ $s->penandatangan_nama }}</p><p class="text-ink-soft">{{ $s->penandatangan_jabatan }}</p></td>
                        <td class="td">{{ $s->pembuat?->name ?? 'Pengguna dihapus' }}</td>
                        <td class="td"><x-status-pill :status="$s->status"/><p class="mt-1 text-xs text-ink-soft">{{ $s->scan_count }} pindai</p></td>
                        <td class="td whitespace-nowrap"><div class="flex flex-wrap items-center justify-end gap-2">
                            <a href="{{ route('admin.surat.show', $s) }}" class="btn-secondary !px-3 !py-1.5 !text-xs">Detail</a>
                            @can('publish', $s)
                                <form method="POST" action="{{ route('admin.surat.publish', $s) }}" onsubmit="return confirm('Publikasikan surat ini agar dapat diverifikasi publik?')">@csrf<button type="submit" class="btn-primary !px-3 !py-1.5 !text-xs">Publish</button></form>
                            @endcan
                            @can('revoke', $s)
                                <button type="button" data-modal-open="dialog-cabut" data-form-action="{{ route('admin.surat.cabut', $s) }}" data-nomor="{{ $s->nomor_surat }}" class="btn-danger-outline !px-3 !py-1.5 !text-xs">Tarik</button>
                            @endcan
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-14 text-center">
                        @if ($q !== '' || $status)<p class="text-sm font-medium">Tidak ada surat yang cocok.</p><p class="mt-1 text-sm text-ink-soft">Coba kata kunci lain atau hapus filter.</p>
                        @else<p class="text-sm font-medium">Belum ada surat.</p><a href="{{ route('admin.surat.create') }}" class="btn-primary mt-4">Buat surat pertama</a>@endif
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-5">{{ $daftar->links() }}</div>
    <x-slot:dialogs>@include('admin.surat._dialog-cabut')</x-slot:dialogs>
</x-layouts.admin>
