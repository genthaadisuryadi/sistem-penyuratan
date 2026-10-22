@props(['posisi' => \App\Enums\PosisiQr::KananBawah, 'interaktif' => false])
@php
    $x = [0 => 9, 1 => 49, 2 => 89][$posisi->kolom()];
    $y = $posisi->atas() ? 9 : 123;
@endphp
{{-- Ilustrasi mini selembar surat dengan posisi QR Code yang disorot. --}}
<svg viewBox="0 0 120 160" role="img" aria-label="Ilustrasi letak QR Code: {{ $posisi->frasa() }}" @if($interaktif) data-qr-preview @endif {{ $attributes->merge(['class' => 'h-auto']) }}>
    <rect x="2" y="2" width="116" height="156" rx="4" fill="#fff" stroke="#e3cfc8" stroke-width="1.2"/>
    <rect x="38" y="13" width="44" height="3.5" rx="1.75" fill="#d9bfb6"/>
    <rect x="14" y="29" width="92" height="1" fill="#efdfd9"/>
    @foreach ([40, 48, 56, 64, 72, 80] as $i => $yy)
        <rect x="14" y="{{ $yy }}" width="{{ $i === 5 ? 54 : 92 }}" height="3" rx="1.5" fill="#efdfd9"/>
    @endforeach
    <rect x="70" y="98" width="36" height="3" rx="1.5" fill="#efdfd9"/>
    <rect x="76" y="104" width="30" height="3" rx="1.5" fill="#efdfd9"/>
    <rect x="80" y="110" width="26" height="3" rx="1.5" fill="#e3cfc8"/>

    <g data-qr-marker transform="translate({{ $x }} {{ $y }})">
        <rect x="-3.5" y="-3.5" width="29" height="29" rx="4.5" fill="#fff0ec" stroke="#e7553c" stroke-width="1.4" stroke-dasharray="3 2.2"/>
        <rect x="0" y="0" width="22" height="22" rx="1.5" fill="#fff"/>
        <g fill="#2b1c18">
            <path d="M1 1h7v7H1zM14 1h7v7h-7zM1 14h7v7H1z"/>
            <path d="M10 1h2v2h-2zM10 5h2v3h-2zM1 10h3v2H1zM6 10h4v2H6zM14 10h2v2h-2zM18 10h3v2h-3zM10 14h3v2h-3zM15 15h3v3h-3zM19 18h2v3h-2zM10 19h3v2h-3z"/>
        </g>
        <g fill="#fff"><path d="M2.4 2.4h4.2v4.2H2.4zM15.4 2.4h4.2v4.2h-4.2zM2.4 15.4h4.2v4.2H2.4z"/></g>
        <g fill="#2b1c18"><path d="M3.6 3.6h1.8v1.8H3.6zM16.6 3.6h1.8v1.8h-1.8zM3.6 16.6h1.8v1.8H3.6z"/></g>
    </g>
</svg>
