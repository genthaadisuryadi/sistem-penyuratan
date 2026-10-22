@props(['title' => null])
@php
    $nav = [
        ['admin.dashboard', 'dashboard', 'Dashboard', ['admin.dashboard']],
        ['admin.surat.create', 'surat-baru', 'Buat Surat', ['admin.surat.create']],
        ['admin.surat.index', 'arsip', 'Arsip Surat', ['admin.surat.index', 'admin.surat.show']],
    ];
    if (auth()->user()->dapatKelolaPengguna()) {
        $nav[] = ['admin.jenis-surat.index', 'arsip', 'Jenis Surat', ['admin.jenis-surat.index']];
        $nav[] = ['admin.pengguna.index', 'pengaturan', 'Manajemen Pengguna', ['admin.pengguna.index', 'admin.pengguna.create', 'admin.pengguna.edit']];
    }
    $nav[] = ['admin.pengaturan', 'pengaturan', 'Pengaturan', ['admin.pengaturan']];
    $user = auth()->user();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-layouts.head :title="$title" :noindex="true"/>
</head>
<body class="min-h-screen lg:pl-64">

    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col bg-pine-900 text-pine-100 shadow-xl transition-transform duration-200 lg:translate-x-0 lg:shadow-none" aria-label="Navigasi utama">
        <div class="px-4 pb-2 pt-5">
            <x-logo-mitra variant="sidebar"/>
            <p class="mt-3 text-center text-xs font-medium tracking-wide text-pine-300">Sistem Penyuratan</p>
        </div>

        <nav class="mt-2 flex-1 space-y-1 px-3">
            @foreach ($nav as [$rute, $ikon, $label, $aktifPada])
                @php($aktif = request()->routeIs($aktifPada))
                <a href="{{ route($rute) }}" @if($aktif) aria-current="page" @endif
                   @class([
                       'group relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors',
                       'bg-white/10 text-white' => $aktif,
                       'text-pine-100/80 hover:bg-white/5 hover:text-white' => ! $aktif,
                   ])>
                    @if ($aktif)
                        <span class="absolute inset-y-2 left-0 w-1 rounded-r bg-orange-300"></span>
                    @endif
                    <x-icon :name="$ikon" class="size-5 shrink-0 {{ $aktif ? 'text-orange-200' : 'text-pine-300 group-hover:text-orange-200' }}"/>
                    {{ $label }}
                </a>
            @endforeach
        </nav>

        <div class="border-t border-white/10 p-4">
            <a href="{{ route('beranda') }}" target="_blank" rel="noopener" class="flex items-center gap-2 rounded-lg px-2 py-2 text-xs text-pine-300 hover:text-white">
                <x-icon name="tautan" class="size-4"/>
                Lihat halaman publik
            </a>
        </div>
    </aside>

    <div class="flex min-h-screen flex-col">
        <header class="sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-line bg-paper/90 px-4 backdrop-blur lg:px-8">
            <button type="button" data-sidebar-toggle aria-controls="sidebar" aria-expanded="false" aria-label="Buka menu" class="-ml-1 rounded-lg p-2 text-ink hover:bg-pine-100 lg:hidden">
                <x-icon name="menu"/>
            </button>
            <p class="truncate text-sm font-medium text-ink-soft">{{ $title }}</p>

            <div class="ml-auto flex items-center gap-3">
                <div class="flex items-center gap-3">
                    <span class="flex size-9 items-center justify-center rounded-full bg-pine-800 text-xs font-semibold text-white" aria-hidden="true">{{ $user->inisial() }}</span>
                    <div class="hidden text-right leading-tight sm:block">
                        <p class="text-sm font-semibold">{{ $user->name }}</p>
                        <p class="text-xs text-ink-soft">{{ $user->role->label() }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="btn-secondary !px-3 !py-2">
                        <x-icon name="keluar" class="size-4"/>
                        <span class="hidden sm:inline">Keluar</span>
                        <span class="sr-only sm:hidden">Keluar</span>
                    </button>
                </form>
            </div>
        </header>

        <main class="flex-1 px-4 py-8 lg:px-8">
            <div class="mx-auto max-w-6xl">
                <x-flash/>
                {{ $slot }}
            </div>
        </main>
        <footer class="border-t border-line bg-white px-4 py-4 lg:px-8">
            <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-3 sm:flex-row sm:gap-4">
                <x-logo-mitra variant="footer" class="sm:justify-start"/>
                <p class="text-center text-xs text-ink-soft sm:text-right">Sistem Penyuratan · Akses sesuai hak pengguna</p>
            </div>
        </footer>
    </div>

    {{ $dialogs ?? '' }}
</body>
</html>
