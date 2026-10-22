@props(['class' => 'size-10', 'variant' => 'navbar'])
@php
    $logoInstansi = config('penyuratan.instansi.logo');
    $logo = match ($variant) {
        'footer' => config('penyuratan.instansi.logo_footer') ?: $logoInstansi ?: 'images/logo-footer.svg',
        'navbar' => config('penyuratan.instansi.logo_navbar') ?: $logoInstansi ?: 'images/logo-navbar.svg',
        default => $logoInstansi ?: config('penyuratan.instansi.logo_navbar') ?: 'images/logo-navbar.svg',
    };
@endphp
@if ($logo && file_exists(public_path($logo)))
    <img src="{{ asset($logo) }}" alt="Logo {{ config('penyuratan.instansi.nama') }}" {{ $attributes->merge(['class' => $class.' object-contain']) }}>
@else
    <svg viewBox="0 0 48 48" role="img" aria-label="Lambang {{ config('penyuratan.instansi.nama') }}" {{ $attributes->merge(['class' => $class]) }}>
        <rect width="48" height="48" rx="11" fill="#e7553c"/>
        <path d="M14 8h14l7 7v25H14z" fill="#fff"/>
        <path d="M28 8v8h7" fill="#ffd4ca"/>
        <path d="M19 23h11M19 28h11M19 33h7" fill="none" stroke="#e7553c" stroke-width="2" stroke-linecap="round"/>
    </svg>
@endif
