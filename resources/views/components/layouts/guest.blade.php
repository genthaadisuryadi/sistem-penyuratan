@props(['title' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-layouts.head :title="$title" :noindex="true"/>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 relative overflow-hidden">
    
    <!-- Latar Belakang Abstrak Oranye (Mesh Gradient + Glow) -->
    <div class="absolute -top-32 -left-32 w-[30rem] h-[30rem] rounded-full bg-[#e7553c] opacity-[0.08] blur-[80px] pointer-events-none"></div>
    <div class="absolute top-1/2 left-3/4 w-96 h-96 -translate-y-1/2 rounded-full bg-[#e7553c] opacity-[0.06] blur-[60px] pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-16 w-[40rem] h-[40rem] rounded-full bg-[#e7553c] opacity-[0.08] blur-[100px] pointer-events-none"></div>

    <!-- Motif Titik (Dot Matrix Overlay) -->
    <div class="absolute inset-0 opacity-50 pointer-events-none" style="background-image: radial-gradient(#e7553c 1px, transparent 1px); background-size: 24px 24px;"></div>

    <!-- ========================================== -->
    <!-- SHAPE HIASAN POJOK-POJOK (SOLID / BOLD)    -->
    <!-- ========================================== -->
    
    <!-- Pojok Kiri Atas: Blob Organik Halus -->
    <svg class="absolute top-0 left-0 w-72 h-72 -translate-x-12 -translate-y-12 text-[#e7553c] pointer-events-none" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
        <path fill="currentColor" d="M44.7,-76.4C58.8,-69.2,71.8,-59.1,79.6,-45.8C87.4,-32.6,90,-16.3,89.1,-0.5C88.1,15.2,83.5,30.4,74.2,42.4C64.9,54.4,50.8,63.1,36.2,71.2C21.6,79.4,6.4,87,-8.4,88.5C-23.2,90,-37.6,85.4,-50.2,77C-62.8,68.6,-73.6,56.5,-80.6,42.4C-87.6,28.3,-90.7,12.2,-89.6,-3.4C-88.5,-19,-83.1,-34,-73.8,-46.1C-64.4,-58.2,-51,-67.4,-36.8,-74.3C-22.6,-81.2,-7.5,-85.7,7.1,-86.6C21.7,-87.5,30.6,-83.7,44.7,-76.4Z" transform="translate(100 100)" />
    </svg>

    <!-- Pojok Kanan Bawah: Geometris Lingkaran (Konektivitas) -->
    <svg class="absolute bottom-0 right-0 w-80 h-80 translate-x-16 translate-y-16 text-[#e7553c] pointer-events-none" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
        <circle cx="100" cy="100" r="70" fill="currentColor" />
        <circle cx="160" cy="40" r="30" fill="currentColor" />
        <circle cx="40" cy="160" r="20" fill="currentColor" />
        <circle cx="170" cy="170" r="15" fill="none" stroke="currentColor" stroke-width="4" />
    </svg>

    <!-- Pojok Kanan Atas: Aksen Silang & Titik (Micro-tech elements) -->
    <svg class="absolute top-12 right-12 w-24 h-24 text-[#e7553c] pointer-events-none" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
        <path stroke="currentColor" stroke-width="3" stroke-linecap="round" d="M20,20 L40,40 M40,20 L20,40 M70,60 L90,80 M90,60 L70,80" />
        <circle cx="80" cy="20" r="4" fill="currentColor" />
        <circle cx="20" cy="80" r="6" fill="none" stroke="currentColor" stroke-width="2" />
    </svg>
    
    <!-- Pojok Kiri Bawah: Garis Gelombang (Wave lines) -->
    <svg class="absolute bottom-10 left-10 w-32 h-32 text-[#e7553c] pointer-events-none" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
        <path fill="none" stroke="currentColor" stroke-width="2" d="M0,20 Q25,40 50,20 T100,20 M0,40 Q25,60 50,40 T100,40 M0,60 Q25,80 50,60 T100,60" />
    </svg>
    <!-- ========================================== -->

    <!-- Konten Utama Halaman -->
    <main class="relative z-10 flex min-h-screen flex-col items-center justify-center px-4 py-10">
        <div class="w-full max-w-md">
            
            {{-- Kotak login --}}
            <div class="rounded-2xl border border-white/60 bg-white/95 p-7 shadow-[0_20px_50px_-12px_rgba(231,85,60,0.15)] backdrop-blur-xl sm:p-9">
                {{ $slot }}
            </div>

            <x-logo-mitra variant="footer" class="mt-8"/>

            <p class="mt-8 flex items-center justify-center gap-1.5 text-center text-xs font-medium text-slate-500">
                <x-icon name="perisai" class="size-4 text-[#e7553c]"/>
                Sesi berakhir otomatis setelah {{ config('session.lifetime') }} menit tidak aktif.
            </p>
        </div>
    </main>
</body>
</html>