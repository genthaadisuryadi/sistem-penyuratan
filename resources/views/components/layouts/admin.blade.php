@props(['title' => null])
@php
    $user = auth()->user();
    
    // 1. Menu Dasar (Tampil untuk semua role)
    $nav = [
        ['admin.dashboard', 'dashboard', 'Dashboard', ['admin.dashboard']],
        ['admin.surat.create', 'surat-baru', 'Buat Surat', ['admin.surat.create']],
        ['admin.surat.index', 'arsip', 'Arsip Surat', ['admin.surat.index', 'admin.surat.show']],
    ];
    
    // 2. Pembatasan Menu Khusus Admin & Sekretariat (Sembunyikan dari Unit)
    // Asumsi: nilai value dari enum role Unit adalah 'unit'
    if ($user->role->value !== 'unit') {
        $nav[] = ['admin.jenis-surat.index', 'arsip', 'Jenis Surat', ['admin.jenis-surat.index']];
        $nav[] = ['admin.pengguna.index', 'pengaturan', 'Manajemen Pengguna', ['admin.pengguna.index', 'admin.pengguna.create', 'admin.pengguna.edit']];
    }
    
    // 3. Pengaturan (Tampil untuk semua role)
    $nav[] = ['admin.pengaturan', 'pengaturan', 'Pengaturan', ['admin.pengaturan']];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-layouts.head :title="$title" :noindex="true"/>
</head>
<body class="min-h-screen lg:pl-64 bg-slate-50">

    {{-- SIDEBAR: Dark Mode ala Rakit AI --}}
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col bg-slate-900 text-slate-100 shadow-xl transition-transform duration-200 lg:translate-x-0 lg:shadow-none" aria-label="Navigasi utama">
        <div class="px-4 pb-2 pt-5">
            <p class="mt-3 text-center text-sm font-semibold tracking-wide text-slate-100">Sistem Penyuratan</p>
        </div>

        <nav class="mt-2 flex-1 space-y-1 px-3">
            @foreach ($nav as [$rute, $ikon, $label, $aktifPada])
                @php($aktif = request()->routeIs($aktifPada))
                <a href="{{ route($rute) }}" @if($aktif) aria-current="page" @endif
                   @class([
                       'group relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors',
                       'bg-white/10 text-white' => $aktif,
                       'text-slate-300 hover:bg-white/5 hover:text-white' => ! $aktif,
                   ])>
                    @if ($aktif)
                        {{-- INDIKATOR AKTIF: Oranye Rakit AI --}}
                        <span class="absolute inset-y-2 left-0 w-1 rounded-r bg-[#e7553c]"></span>
                    @endif
                    <x-icon :name="$ikon" class="size-5 shrink-0 {{ $aktif ? 'text-[#e7553c]' : 'text-slate-400 group-hover:text-[#e7553c]' }}"/>
                    {{ $label }}
                </a>
            @endforeach
        </nav>

        <div class="border-t border-white/10 p-4">
            <a href="{{ route('beranda') }}" target="_blank" rel="noopener" class="flex items-center gap-2 rounded-lg px-2 py-2 text-xs text-slate-400 hover:text-white transition-colors">
                <x-icon name="tautan" class="size-4"/>
                Lihat halaman publik
            </a>
        </div>
    </aside>

    <div class="flex min-h-screen flex-col">
        <header class="sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-slate-200 bg-white/90 px-4 backdrop-blur lg:px-8">
            <button type="button" data-sidebar-toggle aria-controls="sidebar" aria-expanded="false" aria-label="Buka menu" class="-ml-1 rounded-lg p-2 text-slate-600 hover:bg-slate-100 lg:hidden">
                <x-icon name="menu"/>
            </button>
            <p class="truncate text-sm font-medium text-slate-600">{{ $title }}</p>

            <div class="ml-auto flex items-center gap-3">
                <div class="flex items-center gap-3">
                    <span class="flex size-9 items-center justify-center rounded-full bg-[#e7553c] text-xs font-semibold text-white shadow-sm" aria-hidden="true">{{ $user->inisial() }}</span>
                    <div class="hidden text-right leading-tight sm:block">
                        <p class="text-sm font-semibold text-slate-800">{{ $user->name }}</p>
                        <p class="text-xs text-slate-500">{{ $user->role->label() }}</p>
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
        <footer class="border-t border-slate-200 bg-white px-4 py-4 lg:px-8">
            <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-3 sm:flex-row sm:gap-4">
                <p class="text-center text-xs text-slate-500 sm:text-right">Sistem Penyuratan · Akses sesuai hak pengguna</p>
            </div>
        </footer>
    </div>

    {{ $dialogs ?? '' }}
</body>
</html>