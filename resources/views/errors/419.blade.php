@include('errors.layout', [
    'kode' => '419',
    'judul' => 'Sesi telah berakhir',
    'pesan' => 'Halaman kedaluwarsa karena terlalu lama tidak aktif. Muat ulang atau masuk kembali, lalu ulangi.',
    'tautan' => [route('login'), 'Masuk kembali'],
])
