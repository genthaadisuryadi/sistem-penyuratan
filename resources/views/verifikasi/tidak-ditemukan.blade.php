<x-layouts.publik title="Dokumen tidak ditemukan">
    <header class="panel-unknown px-5 pb-28 pt-6 text-white">
        <div class="mx-auto flex max-w-xl items-center gap-3">
            <x-logo class="size-10 shrink-0 rounded-xl bg-white/90 p-0.5"/>
            <p class="text-sm font-semibold leading-tight">{{ config('penyuratan.instansi.nama') }}</p>
        </div>
        <div class="mx-auto mt-6 flex max-w-xl flex-col items-center text-center">
            <x-seal state="unknown" class="w-36 sm:w-40"/>
            <h1 class="mt-5 text-xl font-bold tracking-wide sm:text-2xl">DOKUMEN TIDAK DITEMUKAN</h1>
            <p class="mt-2 max-w-sm text-sm leading-relaxed text-slate-100">Tidak ada surat dengan tautan ini di sistem kami. QR Code bisa jadi palsu atau salah salin.</p>
        </div>
    </header>

    <main class="relative mx-auto -mt-16 max-w-xl space-y-5 px-4">
        <section class="rounded-2xl border border-line bg-white p-5 text-sm leading-relaxed shadow-[0_18px_40px_-24px_rgb(64_35_29/0.45)] sm:p-6">
            <h2 class="text-base font-semibold">Apa yang bisa dilakukan?</h2>
            <p class="mt-2 text-ink-soft">Jangan percaya surat ini sebelum dikonfirmasi. Coba ketik kode verifikasi yang tercetak di bawah QR, atau hubungi {{ config('penyuratan.instansi.nama') }}.</p>
        </section>
        <section class="rounded-2xl border border-line bg-white p-5 sm:p-6" aria-labelledby="judul-cari">
            <h2 id="judul-cari" class="mb-4 text-base font-semibold">Ketik kode verifikasi</h2>
            @include('verifikasi._pencarian')
        </section>
    </main>
</x-layouts.publik>
