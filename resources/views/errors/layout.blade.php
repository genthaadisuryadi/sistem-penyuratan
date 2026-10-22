<x-layouts.publik :title="$judul">
    <main class="mx-auto flex min-h-[70vh] max-w-md flex-col items-center justify-center px-6 text-center">
        <x-logo class="size-14"/>
        <p class="mt-8 text-5xl font-semibold tracking-tight text-emerald-700">{{ $kode }}</p>
        <h1 class="mt-3 text-xl font-semibold">{{ $judul }}</h1>
        <p class="mt-2 text-sm leading-relaxed text-ink-soft">{{ $pesan }}</p>
        <div class="mt-7 flex flex-wrap justify-center gap-3">
            @isset($tautan)
                <a href="{{ $tautan[0] }}" class="btn-primary">{{ $tautan[1] }}</a>
            @endisset
            <a href="{{ route('beranda') }}" class="btn-secondary">Ke halaman utama</a>
        </div>
    </main>
</x-layouts.publik>
