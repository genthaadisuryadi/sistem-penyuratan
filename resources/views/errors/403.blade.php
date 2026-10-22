@include('errors.layout', [
    'kode' => '403',
    'judul' => 'Akses ditolak',
    'pesan' => ($exception->getMessage() && $exception->getMessage() !== 'This action is unauthorized.') ? $exception->getMessage() : 'Anda tidak memiliki izin untuk membuka halaman ini.',
])
