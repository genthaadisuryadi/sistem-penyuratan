@props(['title' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-layouts.head :title="$title" :noindex="true"/>
</head>
<body class="min-h-screen bg-paper" style="background-image: radial-gradient(60rem 30rem at 50% -8rem, rgb(231 85 60 / 0.12), transparent 70%);">
    <main class="flex min-h-screen flex-col items-center justify-center px-4 py-10">
        <div class="w-full max-w-md">
            <div class="mb-7 flex flex-col items-center text-center">
                <x-logo variant="navbar" class="size-16"/>
                <p class="mt-4 text-sm font-medium text-ink-soft">{{ config('penyuratan.instansi.nama') }}</p>
            </div>

            <div class="rounded-2xl border border-line bg-white p-7 shadow-[0_18px_40px_-24px_rgb(64_35_29/0.35)] sm:p-9">
                {{ $slot }}
            </div>

            <x-logo-mitra variant="footer" class="mt-6"/>

            <p class="mt-6 flex items-center justify-center gap-1.5 text-center text-xs text-ink-soft">
                <x-icon name="perisai" class="size-4 text-emerald-700"/>
                Sesi berakhir otomatis setelah {{ config('session.lifetime') }} menit tidak aktif.
            </p>
        </div>
    </main>
</body>
</html>
