@props(['variant' => 'footer'])
{{--
    Dua logo kemitraan: UCIC dan Rakit AI.
    - sidebar : ditumpuk dalam kartu putih agar tetap terbaca di latar gelap.
    - footer  : berdampingan, dipisah garis tipis.
--}}
@if ($variant === 'sidebar')
    <div {{ $attributes->merge(['class' => 'rounded-xl bg-white p-3.5 shadow-sm']) }}>
        <img src="{{ asset('images/logo-ucic.png') }}" alt="Logo Universitas Catur Insan Cendekia (UCIC)" class="mx-auto h-14 w-auto max-w-full object-contain" loading="lazy">
        <div class="my-2.5 h-px bg-line"></div>
        <img src="{{ asset('images/logo-rakit-ai.png') }}" alt="Logo Rakit AI" class="mx-auto h-6 w-auto max-w-full object-contain" loading="lazy">
    </div>
@else
    <div {{ $attributes->merge(['class' => 'flex flex-wrap items-center justify-center gap-x-5 gap-y-3']) }}>
        <img src="{{ asset('images/logo-ucic.png') }}" alt="Logo Universitas Catur Insan Cendekia (UCIC)" class="h-14 w-auto object-contain" loading="lazy">
        <span class="hidden h-7 w-px bg-line sm:block" aria-hidden="true"></span>
        <img src="{{ asset('images/logo-rakit-ai.png') }}" alt="Logo Rakit AI" class="h-7 w-auto object-contain" loading="lazy">
    </div>
@endif
