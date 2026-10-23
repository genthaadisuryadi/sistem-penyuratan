@props(['title' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-layouts.head :title="$title" :noindex="true"/>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    {{ $slot }}

    <footer class="mx-auto mt-14 max-w-3xl px-4 pb-10 text-center text-xs leading-relaxed text-slate-500">
        <div class="rounded-3xl border border-slate-200 bg-white px-6 py-6 shadow-sm">
            <x-logo-mitra variant="footer"/>
        </div>
    </footer>
</body>
</html>