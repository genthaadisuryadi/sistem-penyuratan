@php
    $instansi = 'Sistem Verifikasi Resmi'; 
@endphp
<x-layouts.publik title="Surat tidak ditemukan">
    {{-- Padding top diubah ke pt-16 agar seimbang setelah logo atas dihapus --}}
    <header class="bg-gradient-to-br from-red-600 to-red-900 px-5 pb-28 pt-16 text-white transition-all duration-300">
        
        {{-- BLOK LOGO DAN NAMA INSTANSI DI POJOK ATAS TELAH DIHAPUS --}}
        
        <div class="mx-auto flex max-w-xl flex-col items-center text-center">
            <div class="flex size-32 items-center justify-center rounded-full bg-white/10 p-6 backdrop-blur-md">
                <x-icon name="peringatan" class="size-full text-white/90"/>
            </div>
            
            <h1 class="mt-6 text-xl font-bold tracking-wide sm:text-2xl">
                SURAT TIDAK DITEMUKAN
            </h1>
            <p class="mt-2 max-w-sm text-sm leading-relaxed text-white/90">
                Peringatan! Dokumen ini tidak terdaftar di pangkalan data resmi kami atau kode QR yang Anda pindai tidak valid.
            </p>
        </div>
    </header>

    <main class="relative mx-auto -mt-16 max-w-xl space-y-5 px-4">
        <section class="flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 p-5 text-sm leading-relaxed text-red-900 shadow-[0_18px_40px_-24px_rgb(64_35_29/0.45)]" role="alert">
            <x-icon name="peringatan" class="mt-0.5 size-5 shrink-0"/>
            <p>Berhati-hatilah terhadap potensi pemalsuan dokumen. Pastikan Anda memindai kode QR dari dokumen yang diterbitkan secara resmi melalui {{ $instansi }}.</p>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6" aria-labelledby="judul-cari">
            <h2 id="judul-cari" class="mb-4 text-base font-semibold text-slate-900">Coba periksa surat lain</h2>
            @include('verifikasi._pencarian')
        </section>
    </main>
</x-layouts.publik>