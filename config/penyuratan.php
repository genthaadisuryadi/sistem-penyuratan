<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Identitas Instansi
    |--------------------------------------------------------------------------
    | Logo opsional: letakkan file di folder `public/` (mis. public/logo.png)
    | lalu isi INSTANSI_LOGO=logo.png. Jika kosong, dipakai lambang bawaan.
    */
    'instansi' => [
        'nama' => env('INSTANSI_NAMA', 'Nama Instansi Anda'),
        'singkatan' => env('INSTANSI_SINGKATAN', 'Instansi'),
        'logo' => env('INSTANSI_LOGO'),
        'logo_navbar' => env('INSTANSI_LOGO_NAVBAR'),
        'logo_footer' => env('INSTANSI_LOGO_FOOTER'),
        'kontak' => env('INSTANSI_KONTAK', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | URL Verifikasi
    |--------------------------------------------------------------------------
    | Alamat dasar yang DICETAK ke dalam QR Code. Sengaja dibaca dari config,
    | bukan dari header Host request, supaya QR tidak bisa diarahkan ke domain
    | lain. WAJIB diisi domain produksi sebelum menerbitkan surat sungguhan.
    */
    'verification_base_url' => env('VERIFICATION_BASE_URL', env('APP_URL', 'http://localhost')),

    /* Path halaman login admin (tidak ditautkan dari halaman publik). */
    'login_path' => env('ADMIN_LOGIN_PATH', 'masuk-admin'),

    'upload_max_kb' => (int) env('UPLOAD_MAX_KB', 10240),

    /*
    |--------------------------------------------------------------------------
    | Penempelan QR (satuan milimeter)
    |--------------------------------------------------------------------------
    */
    'qr' => [
        'size_mm' => (float) env('QR_SIZE_MM', 24),
        'margin_mm' => (float) env('QR_MARGIN_MM', 12),
        'quiet_mm' => 1.8,
    ],

    /* Ghostscript dipakai hanya sebagai cadangan untuk PDF yang tidak terbaca FPDI. */
    'ghostscript' => env('GHOSTSCRIPT_BINARY'),

    'login' => [
        'max_attempts' => (int) env('LOGIN_MAX_ATTEMPTS', 5),
        'decay_seconds' => (int) env('LOGIN_DECAY_SECONDS', 60),
    ],

    'scan' => [
        /* Pindai berulang dari perangkat yang sama dalam jendela ini dihitung satu kali. */
        'dedupe_seconds' => (int) env('SCAN_DEDUPE_SECONDS', 30),
        'bot_pattern' => '/bot|crawl|spider|slurp|facebookexternalhit|whatsapp|telegram|preview|curl|wget|python-requests|httpclient|headless|lighthouse|monitor|uptime|scanner/i',
    ],
];
