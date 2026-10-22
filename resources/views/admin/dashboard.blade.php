<x-layouts.admin title="Dashboard">
    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <div><p class="text-sm font-semibold uppercase tracking-[0.16em] text-emerald-700">Ringkasan sistem</p><h1 class="mt-1 text-2xl font-semibold tracking-tight">Dashboard</h1><p class="mt-1 text-sm text-ink-soft">Jumlah surat yang dibuat dan aktivitas verifikasi.</p></div>
        <a href="{{ route('admin.surat.create') }}" class="btn-primary"><x-icon name="surat-baru" class="size-4"/>Buat Surat Baru</a>
    </div>

    <dl class="panel grid grid-cols-2 divide-x divide-y divide-line lg:grid-cols-5 lg:divide-y-0">
        <div class="p-5"><dt class="text-sm text-ink-soft">Surat dibuat</dt><dd class="mt-1 text-3xl font-semibold tracking-tight">{{ number_format($total, 0, ',', '.') }}</dd><p class="mt-1 text-xs text-ink-soft">Sesuai akses akun</p></div>
        <div class="p-5"><dt class="text-sm text-ink-soft">Draf</dt><dd class="mt-1 text-3xl font-semibold tracking-tight text-amber-700">{{ number_format($draf, 0, ',', '.') }}</dd></div>
        <div class="p-5"><dt class="text-sm text-ink-soft">Publish</dt><dd class="mt-1 text-3xl font-semibold tracking-tight text-emerald-700">{{ number_format($aktif, 0, ',', '.') }}</dd></div>
        <div class="p-5"><dt class="text-sm text-ink-soft">Ditarik</dt><dd @class(['mt-1 text-3xl font-semibold tracking-tight', 'text-red-700' => $dicabut > 0])>{{ number_format($dicabut, 0, ',', '.') }}</dd></div>
        <div class="p-5"><dt class="text-sm text-ink-soft">Total pindai</dt><dd class="mt-1 text-3xl font-semibold tracking-tight">{{ number_format($totalScan, 0, ',', '.') }}</dd></div>
    </dl>

    <section class="panel mt-6 p-6" aria-labelledby="judul-grafik">
        <div class="flex items-baseline justify-between gap-4"><h2 id="judul-grafik" class="text-base font-semibold">Pemindaian 14 hari terakhir</h2><p class="text-sm text-ink-soft">{{ $grafik->sum('total') }} pemindaian</p></div>
        @if ($grafik->sum('total') === 0)
            <p class="mt-8 mb-4 text-center text-sm text-ink-soft">Belum ada pemindaian. Grafik akan terisi setelah QR Code dipindai.</p>
        @else
            <div class="mt-6 flex h-40 items-end gap-1.5 sm:gap-2" role="img" aria-label="Grafik pemindaian setiap hari selama 14 hari terakhir">
                @foreach ($grafik as $hari)<div class="group flex h-full flex-1 flex-col justify-end" title="{{ $hari['tanggal']->translatedFormat('j F') }}: {{ $hari['total'] }} pemindaian"><span class="mb-1 text-center text-[11px] font-medium text-ink-soft">{{ $hari['total'] ?: '' }}</span><div @class(['w-full rounded-t', 'bg-emerald-600' => $loop->last, 'bg-emerald-300' => ! $loop->last]) style="height: {{ max(2, round($hari['total'] / $grafikMaks * 100)) }}%"></div></div>@endforeach
            </div>
            <div class="mt-2 flex gap-1.5 sm:gap-2" aria-hidden="true">@foreach ($grafik as $hari)<span class="flex-1 text-center text-[11px] text-ink-soft">{{ $loop->iteration % 2 === 1 || $loop->last ? $hari['tanggal']->format('j/n') : '' }}</span>@endforeach</div>
        @endif
    </section>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <section class="panel" aria-labelledby="judul-terbaru"><h2 id="judul-terbaru" class="border-b border-line px-6 py-4 text-base font-semibold">Surat terbaru</h2>
            @forelse ($terbaru as $s)
                <a href="{{ route('admin.surat.show', $s) }}" class="flex items-center justify-between gap-4 border-b border-line px-6 py-3.5 last:border-b-0 hover:bg-pine-50"><div class="min-w-0"><p class="truncate text-sm font-semibold">{{ $s->nomor_surat }}</p><p class="truncate text-sm text-ink-soft">{{ $s->perihal }}</p><p class="mt-0.5 text-xs text-ink-soft">{{ $s->jenisSurat?->nama ?? 'Jenis surat' }}</p></div><x-status-pill :status="$s->status" class="shrink-0"/></a>
            @empty<div class="px-6 py-10 text-center"><p class="text-sm text-ink-soft">Belum ada surat yang dibuat.</p><a href="{{ route('admin.surat.create') }}" class="btn-primary mt-4">Buat surat pertama</a></div>@endforelse
        </section>
        <section class="panel" aria-labelledby="judul-populer"><h2 id="judul-populer" class="border-b border-line px-6 py-4 text-base font-semibold">Paling sering dipindai</h2>
            @forelse ($terpopuler as $s)<a href="{{ route('admin.surat.show', $s) }}" class="flex items-center justify-between gap-4 border-b border-line px-6 py-3.5 last:border-b-0 hover:bg-pine-50"><div class="min-w-0"><p class="truncate text-sm font-semibold">{{ $s->nomor_surat }}</p><p class="truncate text-sm text-ink-soft">{{ $s->perihal }}</p></div><p class="shrink-0 text-sm"><span class="font-semibold">{{ number_format($s->scan_count, 0, ',', '.') }}</span> <span class="text-ink-soft">pindai</span></p></a>
            @empty<p class="px-6 py-10 text-center text-sm text-ink-soft">Belum ada surat yang dipindai.</p>@endforelse
        </section>
    </div>
</x-layouts.admin>
