<x-layouts.publik title="Periksa keaslian surat">
    {{-- Hero --}}
    <header class="panel-valid hero-pattern relative overflow-hidden px-5 pb-36 pt-7 text-white sm:pb-40">
        <div class="pointer-events-none absolute -right-24 -top-24 size-80 rounded-full bg-white/10 blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-32 -left-20 size-96 rounded-full bg-pine-900/30 blur-3xl" aria-hidden="true"></div>

        <div class="relative mx-auto max-w-3xl">
            <div class="inline-flex items-center gap-2.5 rounded-full bg-white/15 py-1.5 pl-1.5 pr-4 ring-1 ring-white/25 backdrop-blur">
                <x-logo class="size-8 shrink-0 rounded-full bg-white p-1"/>
                <span class="text-sm font-semibold leading-none">{{ config('penyuratan.instansi.nama') }}</span>
            </div>

            <p class="mt-14 inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-[0.18em] text-white/80">
                <x-icon name="perisai" class="size-4"/> Verifikasi dokumen resmi
            </p>
            <h1 class="mt-3 max-w-xl text-4xl font-bold leading-[1.08] tracking-tight sm:text-5xl">
                Periksa keaslian surat dalam hitungan detik.
            </h1>
            <p class="mt-4 max-w-lg text-base leading-relaxed text-white/90 sm:text-lg">
                Pindai QR Code pada surat, atau masukkan kode verifikasinya. Kami tunjukkan apakah surat itu asli dan masih berlaku.
            </p>
        </div>
    </header>

    <main class="relative mx-auto -mt-24 max-w-3xl px-4 sm:-mt-28">
        {{-- Kartu pencarian --}}
        <section class="rounded-3xl border border-white/70 bg-white p-6 shadow-[0_30px_60px_-28px_rgb(64_35_29/0.55)] sm:p-8" aria-labelledby="judul-cari">
            <div class="flex items-start gap-4">
                <span class="hidden size-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 sm:flex">
                    <x-icon name="qr" class="size-6"/>
                </span>
                <div class="min-w-0 flex-1">
                    <h2 id="judul-cari" class="text-lg font-semibold tracking-tight">Tidak bisa memindai? Ketik kodenya</h2>
                    <p class="mt-1 text-sm text-ink-soft">Kode terdiri dari 10 karakter dan tercetak tepat di bawah QR Code.</p>
                    <div class="mt-5">
                        @include('verifikasi._pencarian')
                    </div>
                </div>
            </div>
        </section>

        {{-- Langkah --}}
        <section class="mt-10" aria-labelledby="judul-cara">
            <h2 id="judul-cara" class="text-center text-sm font-semibold uppercase tracking-[0.18em] text-ink-soft">Cara memeriksa</h2>
            <ol class="mt-5 grid gap-4 sm:grid-cols-3">
                @foreach ([
                    ['qr', 'Pindai QR Code', 'Buka kamera ponsel dan arahkan ke QR Code pada surat.'],
                    ['tautan', 'Buka tautannya', 'Pastikan alamat yang muncul adalah situs resmi '.config('penyuratan.instansi.singkatan').'.'],
                    ['cek', 'Cocokkan data', 'Samakan nomor, perihal, tanggal, dan penandatangan dengan surat di tangan Anda.'],
                ] as $i => [$ikon, $judul, $isi])
                    <li class="group relative rounded-2xl border border-line bg-white p-5 transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-[0_18px_30px_-22px_rgb(231_85_60/0.6)]">
                        <span class="absolute right-4 top-3 text-4xl font-bold leading-none text-emerald-100 transition group-hover:text-emerald-300" aria-hidden="true">{{ $i + 1 }}</span>
                        <span class="flex size-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                            <x-icon :name="$ikon" class="size-5"/>
                        </span>
                        <h3 class="mt-4 text-base font-semibold">{{ $judul }}</h3>
                        <p class="mt-1.5 text-sm leading-relaxed text-ink-soft">{{ $isi }}</p>
                    </li>
                @endforeach
            </ol>
        </section>

        {{-- Jaminan --}}
        <ul class="mt-8 flex flex-wrap items-center justify-center gap-2.5 text-xs font-medium text-ink-soft">
            @foreach (['Tanpa login', 'Data langsung dari sistem', 'Alamat IP tidak disimpan'] as $butir)
                <li class="inline-flex items-center gap-1.5 rounded-full border border-line bg-white px-3 py-1.5">
                    <x-icon name="cek" class="size-3.5 text-emerald-600"/> {{ $butir }}
                </li>
            @endforeach
        </ul>
    </main>
</x-layouts.publik>
