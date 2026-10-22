<?php

return [
    'accepted' => ':attribute harus diterima.',
    'alpha_dash' => ':attribute hanya boleh berisi huruf, angka, strip, dan garis bawah.',
    'array' => ':attribute harus berupa array.',
    'before_or_equal' => ':attribute harus tanggal sebelum atau sama dengan :date.',
    'between' => [
        'numeric' => ':attribute harus di antara :min dan :max.',
        'file' => 'Ukuran :attribute harus di antara :min dan :max kilobita.',
        'string' => ':attribute harus di antara :min dan :max karakter.',
    ],
    'boolean' => ':attribute harus bernilai benar atau salah.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'current_password' => 'Kata sandi saat ini salah.',
    'date' => ':attribute bukan tanggal yang valid.',
    'different' => ':attribute dan :other harus berbeda.',
    'email' => ':attribute harus berupa alamat email yang valid.',
    'enum' => ':attribute yang dipilih tidak valid.',
    'file' => ':attribute harus berupa berkas.',
    'in' => ':attribute yang dipilih tidak valid.',
    'max' => [
        'numeric' => ':attribute tidak boleh lebih dari :max.',
        'file' => 'Ukuran :attribute tidak boleh lebih dari :max kilobita.',
        'string' => ':attribute tidak boleh lebih dari :max karakter.',
        'array' => ':attribute tidak boleh memiliki lebih dari :max item.',
    ],
    'mimes' => ':attribute harus berupa berkas bertipe: :values.',
    'mimetypes' => ':attribute harus berupa berkas PDF.',
    'min' => [
        'numeric' => ':attribute minimal :min.',
        'file' => 'Ukuran :attribute minimal :min kilobita.',
        'string' => ':attribute minimal :min karakter.',
        'array' => ':attribute minimal memiliki :min item.',
    ],
    'password' => [
        'letters' => ':attribute harus mengandung setidaknya satu huruf.',
        'mixed' => ':attribute harus mengandung huruf besar dan huruf kecil.',
        'numbers' => ':attribute harus mengandung setidaknya satu angka.',
        'symbols' => ':attribute harus mengandung setidaknya satu simbol.',
        'uncompromised' => ':attribute pernah bocor di internet. Pilih kata sandi lain.',
    ],
    'regex' => 'Format :attribute tidak valid.',
    'required' => ':attribute wajib diisi.',
    'string' => ':attribute harus berupa teks.',
    'unique' => ':attribute sudah digunakan.',
    'uploaded' => ':attribute gagal diunggah. Periksa ukuran berkas (maksimal sesuai batas server).',
    'url' => 'Format :attribute tidak valid.',

    'custom' => [],

    'attributes' => [
        'email' => 'email',
        'password' => 'kata sandi',
        'login' => 'email atau username',
        'name' => 'nama',
        'username' => 'username',
    ],
];
