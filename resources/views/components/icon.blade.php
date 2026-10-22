@props(['name'])
@php
    $paths = [
        'dashboard' => '<rect x="3.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="3.5" width="7" height="7" rx="1.5"/><rect x="3.5" y="13.5" width="7" height="7" rx="1.5"/><rect x="13.5" y="13.5" width="7" height="7" rx="1.5"/>',
        'surat-baru' => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/><path d="M12 11.5v5M9.5 14h5"/>',
        'arsip' => '<path d="M4 4.5h16v3.5H4z"/><path d="M5.5 8v10a2 2 0 0 0 2 2h9a2 2 0 0 0 2-2V8"/><path d="M10 12.5h4"/>',
        'pengaturan' => '<path d="M4 7h9M17 7h3M4 17h3M11 17h9"/><path d="M15 4.5v5M9 14.5v5"/>',
        'keluar' => '<path d="M9 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h3"/><path d="M16 8l4 4-4 4M20 12H9"/>',
        'unduh' => '<path d="M12 4v11M7 11l5 5 5-5M5 20h14"/>',
        'unggah' => '<path d="M12 16V5M7.5 9.5L12 5l4.5 4.5M5 19h14"/>',
        'cabut' => '<circle cx="12" cy="12" r="8.5"/><path d="M6 6l12 12"/>',
        'mata' => '<path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
        'mata-coret' => '<path d="M3 3l18 18"/><path d="M10.6 6.2A9.6 9.6 0 0 1 12 6c6.4 0 10 6 10 6a17 17 0 0 1-3.2 3.7M6.4 7.9C3.6 9.7 2 12 2 12s3.6 7 10 7c1.4 0 2.7-.3 3.8-.8"/><path d="M9.9 10a3 3 0 0 0 4.1 4.1"/>',
        'cek' => '<path d="M5 12.5l4.5 4.5L19 7.5"/>',
        'cari' => '<circle cx="11" cy="11" r="6.5"/><path d="M16 16l4.5 4.5"/>',
        'salin' => '<rect x="8.5" y="8.5" width="11" height="11" rx="2"/><path d="M15.5 8.5V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v7.5a2 2 0 0 0 2 2h2.5"/>',
        'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'tutup' => '<path d="M6 6l12 12M18 6L6 18"/>',
        'qr' => '<rect x="3.5" y="3.5" width="6.5" height="6.5" rx="1"/><rect x="14" y="3.5" width="6.5" height="6.5" rx="1"/><rect x="3.5" y="14" width="6.5" height="6.5" rx="1"/><path d="M14 14h3v3h-3zM19 14h1.5M14 19.5h1.5M18 18.5h2.5v2h-2.5z"/>',
        'perisai' => '<path d="M12 3c3.2 2 6.4 2.7 8.5 2.7v6c0 5.3-3.6 8.6-8.5 10.3-4.9-1.7-8.5-5-8.5-10.3v-6C5.6 5.7 8.8 5 12 3z"/>',
        'jam' => '<circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/>',
        'peringatan' => '<path d="M12 4l9 16H3z"/><path d="M12 10v4.5M12 17.2v.1"/>',
        'tautan' => '<path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1"/><path d="M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1"/>',
    ];
@endphp
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" {{ $attributes->merge(['class' => 'size-5']) }}>{!! $paths[$name] ?? '' !!}</svg>
