<x-layouts.publik title="Periksa keaslian surat">
    {{-- Hero dengan Tema Gradasi Oranye Rakit AI --}}
    <header class="hero-pattern relative overflow-hidden bg-gradient-to-br from-[#f07860] via-[#e7553c] to-[#b63a28] px-5 pb-36 pt-16 text-white sm:pb-40">
        <div class="pointer-events-none absolute -right-24 -top-24 size-80 rounded-full bg-white/10 blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-32 -left-20 size-96 rounded-full bg-black/10 blur-3xl" aria-hidden="true"></div>

        <div class="relative mx-auto max-w-3xl">
            {{-- Lencana "Nama Instansi Anda" dan Logo sudah dihapus dari sini --}}

            <p class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-[0.18em] text-white/90">
                <x-icon name="perisai" class="size-4"/> Verifikasi dokumen resmi
            </p>
            <h1 class="mt-4 max-w-xl text-4xl font-bold leading-[1.08] tracking-tight sm:text-5xl">
                Periksa keaslian surat dalam hitungan detik.
            </h1>
            <p class="mt-5 max-w-lg text-base leading-relaxed text-white/90 sm:text-lg">
                Pindai QR Code pada surat, atau masukkan kode verifikasinya. Kami tunjukkan apakah surat itu asli dan masih berlaku.
            </p>
        </div>
    </header>

    <main class="relative mx-auto -mt-24 max-w-3xl px-4 sm:-mt-28">
        {{-- Kartu pencarian --}}
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_30px_60px_-28px_rgba(231,85,60,0.25)] sm:p-8" aria-labelledby="judul-cari">
            <div class="flex items-start gap-4">
                {{-- Ikon QR dengan aksen oranye --}}
                <span class="hidden size-12 shrink-0 items-center justify-center rounded-2xl bg-[#e7553c]/10 text-[#e7553c] sm:flex">
                    <x-icon name="qr" class="size-6"/>
                </span>
                <div class="min-w-0 flex-1">
                    <h2 id="judul-cari" class="text-lg font-semibold tracking-tight text-slate-900">Tidak bisa memindai? Ketik kodenya</h2>
                    <p class="mt-1 text-sm text-slate-500">Kode terdiri dari 10 karakter dan tercetak tepat di bawah QR Code.</p>
                    <div class="mt-5">
                        @include('verifikasi._pencarian')
                    </div>
                </div>
            </div>
        </section>

        {{-- Langkah --}}
        <section class="mt-10" aria-labelledby="judul-cara">
            <h2 id="judul-cara" class="text-center text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">Cara memeriksa</h2>
            <ol class="mt-5 grid gap-4 sm:grid-cols-3">
                @foreach ([
                    ['qr', 'Pindai QR Code', 'Buka kamera ponsel dan arahkan ke QR Code pada surat.'],
                    ['tautan', 'Buka tautannya', 'Pastikan alamat yang muncul adalah situs resmi instansi.'],
                    ['cek', 'Cocokkan data', 'Samakan nomor, perihal, tanggal, dan penandatangan dengan surat di tangan Anda.'],
                ] as $i => [$ikon, $judul, $isi])
                    <li class="group relative rounded-2xl border border-slate-200 bg-white p-5 transition hover:-translate-y-0.5 hover:border-[#e7553c]/40 hover:shadow-[0_18px_30px_-22px_rgba(231,85,60,0.4)]">
                        {{-- Angka step besar di kanan atas --}}
                        <span class="absolute right-4 top-3 text-4xl font-bold leading-none text-slate-100 transition group-hover:text-[#e7553c]/15" aria-hidden="true">{{ $i + 1 }}</span>
                        {{-- Ikon step --}}
                        <span class="flex size-11 items-center justify-center rounded-xl bg-[#e7553c]/10 text-[#e7553c]">
                            <x-icon :name="$ikon" class="size-5"/>
                        </span>
                        <h3 class="mt-4 text-base font-semibold text-slate-900">{{ $judul }}</h3>
                        <p class="mt-1.5 text-sm leading-relaxed text-slate-500">{{ $isi }}</p>
                    </li>
                @endforeach
            </ol>
        </section>

        {{-- Jaminan --}}
        <ul class="mt-8 flex flex-wrap items-center justify-center gap-2.5 text-xs font-medium text-slate-500">
            @foreach (['Tanpa login', 'Data langsung dari sistem', 'Alamat IP tidak disimpan'] as $butir)
                <li class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-3 py-1.5">
                    <x-icon name="cek" class="size-3.5 text-[#e7553c]"/> {{ $butir }}
                </li>
            @endforeach
        </ul>
    </main>
</x-layouts.publik>