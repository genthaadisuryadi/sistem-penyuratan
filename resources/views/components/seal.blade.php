@props(['state' => 'valid'])
@php
    $warna = [
        'valid' => ['rim' => ['#ffe5dd', '#f6aa9a', '#a53a28'], 'face' => ['#f58c78', '#e7553c', '#a53a28'], 'dasar' => '#40231d'],
        'revoked' => ['rim' => ['#fee2e2', '#f87171', '#991b1b'], 'face' => ['#fca5a5', '#ef4444', '#991b1b'], 'dasar' => '#450a0a'],
        'unknown' => ['rim' => ['#f1f5f9', '#94a3b8', '#334155'], 'face' => ['#cbd5e1', '#64748b', '#334155'], 'dasar' => '#0f172a'],
    ][$state];
    $id = 'seal-'.$state;
@endphp
{{-- Lencana keamanan 3D: tepi berlapis, wajah bergradasi, kilap, dan bayangan jatuh. --}}
<svg viewBox="0 0 220 258" role="img" aria-label="{{ ['valid' => 'Dokumen terverifikasi', 'revoked' => 'Dokumen tidak valid', 'unknown' => 'Dokumen tidak ditemukan'][$state] }}" {{ $attributes->merge(['class' => 'seal']) }}>
    <defs>
        <linearGradient id="{{ $id }}-rim" x1="0" y1="0" x2="0.2" y2="1">
            <stop offset="0" stop-color="{{ $warna['rim'][0] }}"/>
            <stop offset="0.4" stop-color="{{ $warna['rim'][1] }}"/>
            <stop offset="1" stop-color="{{ $warna['rim'][2] }}"/>
        </linearGradient>
        <linearGradient id="{{ $id }}-face" x1="0.15" y1="0" x2="0.8" y2="1">
            <stop offset="0" stop-color="{{ $warna['face'][0] }}"/>
            <stop offset="0.5" stop-color="{{ $warna['face'][1] }}"/>
            <stop offset="1" stop-color="{{ $warna['face'][2] }}"/>
        </linearGradient>
        <radialGradient id="{{ $id }}-gloss" cx="0.3" cy="0.12" r="0.75">
            <stop offset="0" stop-color="#fff" stop-opacity="0.7"/>
            <stop offset="0.55" stop-color="#fff" stop-opacity="0.08"/>
            <stop offset="1" stop-color="#fff" stop-opacity="0"/>
        </radialGradient>
        <linearGradient id="{{ $id }}-glyph" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0" stop-color="#fff"/>
            <stop offset="1" stop-color="#ffe5dd"/>
        </linearGradient>
        <filter id="{{ $id }}-drop" x="-30%" y="-20%" width="160%" height="150%">
            <feDropShadow dx="0" dy="12" stdDeviation="9" flood-color="{{ $warna['dasar'] }}" flood-opacity="0.5"/>
        </filter>
        <filter id="{{ $id }}-glyph-shadow" x="-30%" y="-30%" width="160%" height="170%">
            <feDropShadow dx="0" dy="4" stdDeviation="3" flood-color="{{ $warna['dasar'] }}" flood-opacity="0.55"/>
        </filter>
        <filter id="{{ $id }}-blur"><feGaussianBlur stdDeviation="5"/></filter>
    </defs>

    <ellipse cx="110" cy="244" rx="58" ry="8" fill="{{ $warna['dasar'] }}" opacity="0.4" filter="url(#{{ $id }}-blur)"/>

    <g filter="url(#{{ $id }}-drop)">
        <path d="M110 8c30 19 62 26 92 26v88c0 62-40 98-92 120-52-22-92-58-92-120V34c30 0 62-7 92-26z" fill="url(#{{ $id }}-rim)"/>
        <path d="M110 24c26 16 54 22 80 23v75c0 52-34 83-80 102-46-19-80-50-80-102V47c26-1 54-7 80-23z" fill="url(#{{ $id }}-face)"/>
        <path d="M110 24c26 16 54 22 80 23v75c0 52-34 83-80 102-46-19-80-50-80-102V47c26-1 54-7 80-23z" fill="none" stroke="#fff" stroke-opacity="0.35" stroke-width="1.5"/>
        <path d="M110 24c26 16 54 22 80 23v75c0 52-34 83-80 102-46-19-80-50-80-102V47c26-1 54-7 80-23z" fill="url(#{{ $id }}-gloss)"/>
    </g>
    <path d="M30 47c26-1 54-7 80-23 26 16 54 22 80 23" fill="none" stroke="#fff" stroke-opacity="0.55" stroke-width="2" stroke-linecap="round" transform="translate(0 -1)"/>

    <g filter="url(#{{ $id }}-glyph-shadow)" fill="none" stroke="url(#{{ $id }}-glyph)" stroke-width="17" stroke-linecap="round" stroke-linejoin="round">
        @if ($state === 'valid')
            <path d="M72 128l28 29 50-60"/>
        @elseif ($state === 'revoked')
            <path d="M82 96l56 56M138 96L82 152"/>
        @else
            <path d="M110 86v44"/>
            <circle cx="110" cy="160" r="1" stroke-width="20"/>
        @endif
    </g>
</svg>
