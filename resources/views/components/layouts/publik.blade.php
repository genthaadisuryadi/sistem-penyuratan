@props(['title' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-layouts.head :title="$title" :noindex="true"/>
</head>
<body class="min-h-screen">
    {{ $slot }}

    <footer class="mx-auto mt-14 max-w-3xl px-4 pb-10 text-center text-xs leading-relaxed text-ink-soft">
        <div class="rounded-3xl border border-line bg-white px-6 py-7">
            <x-logo-mitra variant="footer"/>
            <div class="mx-auto my-5 h-px w-16 bg-line"></div>
            <p class="text-sm font-semibold text-ink">{{ config('penyuratan.instansi.nama') }}</p>
            @if (config('penyuratan.instansi.kontak'))
                <p class="mt-1">{{ config('penyuratan.instansi.kontak') }}</p>
            @endif
            <p class="mt-3">Pemeriksaan ini dicatat tanpa menyimpan alamat IP Anda.</p>
        </div>
    </footer>
</body>
</html>
