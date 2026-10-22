# Sistem Penyuratan dengan QR Code Verifikasi

Aplikasi penyuratan berbasis Laravel 13 untuk mencatat, menerbitkan, dan memverifikasi dokumen PDF menggunakan QR Code. Aplikasi tidak menyediakan menu registrasi publik; akun dibuat oleh Super Admin atau Sekretariat dari menu **Manajemen Pengguna**.

## Fitur utama

- **Super Admin**: akses semua menu, seluruh surat, pengelolaan pengguna, dan master Jenis Surat.
- **Sekretariat**: membuat surat dengan nama penandatangan pilihan, mengelola akun Sekretariat/Unit, mereset kata sandi, dan mengelola Jenis Surat.
- **Unit**: hanya melihat surat yang dibuat oleh akun sendiri; penandatangan otomatis mengikuti nama dan jabatan yang tersimpan pada sesi/akun login.
- Status surat: **Draf**, **Publish**, dan **Ditarik**. Perubahan menjadi Ditarik selalu meminta alasan penarikan.
- Dashboard menampilkan jumlah surat beserta ringkasan status sesuai cakupan akses akun.
- Master **Jenis Surat** menyimpan nama dan kode, dan digunakan pada form surat baru.
- Form surat: Jenis Surat, tanggal, nomor, perihal, tujuan, penandatangan, jabatan, unggah PDF, serta pengaturan posisi QR pada PDF.
- QR Code ditampilkan secara realtime setelah Jenis Surat dipilih dan nomor surat diisi. QR mengarah ke `https://domain-anda/verify/nomor_surat`.
- Surat Draf belum dapat diverifikasi sebagai surat berlaku dan PDF tidak disediakan untuk publik sampai status Publish.
- Jejak audit menyimpan pembuat surat dari sesi login dan aktivitas penting lainnya.

## Persyaratan

PHP 8.3+, Composer, Node.js/npm untuk membangun aset frontend, dan database MySQL/MariaDB atau SQLite dengan ekstensi PDO yang sesuai. Folder `vendor/` dan `public/build/` disertakan dalam arsip ini.

## Instalasi lokal

1. Ekstrak ZIP, buka folder proyek di terminal, lalu salin `.env.example` menjadi `.env` (PowerShell: `Copy-Item .env.example .env`; CMD: `copy .env.example .env`).
2. Atur `.env`, terutama `APP_URL`, koneksi database, kredensial akun awal, dan `VERIFICATION_BASE_URL`. Pada instalasi SQLite, pastikan file `database/database.sqlite` tersedia.
3. Jalankan:

   ```bash
   composer install
   php artisan key:generate
   php artisan migrate --seed
   php artisan serve
   ```

4. Buka `http://localhost:8000/masuk-admin` (atau nilai `ADMIN_LOGIN_PATH` yang diatur di `.env`). Gunakan akun awal yang dikonfigurasi lewat `ADMIN_EMAIL` dan `ADMIN_PASSWORD`; jika `ADMIN_PASSWORD` dibiarkan kosong, seeder membuat sandi acak dan menampilkannya satu kali di terminal. Segera ganti kata sandinya setelah masuk.

Untuk database baru yang boleh dihapus seluruh isinya, dapat menggunakan `php artisan migrate:fresh --seed`. **Jangan** menjalankan perintah tersebut pada database yang berisi data penting. Untuk membuat akun lewat CLI, gunakan `php artisan admin:buat "Nama Pengguna" email@instansi.go.id --username=nama --role=unit`.

Aset frontend dapat dibangun dengan `npm ci` lalu `npm run build`. Untuk mode pengembangan, gunakan `npm run dev`.

## Alur surat

1. Pengguna masuk melalui halaman login; tidak ada registrasi publik.
2. Pengguna memilih **Buat Surat Baru**, kemudian mengisi Jenis Surat, tanggal, nomor surat, perihal, tujuan, penandatangan, jabatan, dan PDF.
3. Saat nomor surat dan jenis telah dipilih, pratinjau QR diperbarui otomatis. Pastikan `VERIFICATION_BASE_URL` sudah menunjuk ke domain HTTPS produksi sebelum PDF resmi diterbitkan.
4. Surat bisa disimpan sebagai Draf atau langsung Publish. PDF yang tersimpan telah diberi QR dan ditempatkan di storage privat.
5. Surat berstatus Publish dapat diverifikasi melalui `/verify/{nomor_surat}`. Tautan verifikasi menggunakan nomor surat; nomor dengan karakter `/` tetap dipertahankan sebagai bagian alamat.
6. Untuk menarik surat, pengguna dengan hak akses yang sesuai mengisi alasan penarikan. Surat yang ditarik akan tampil tidak berlaku dan unduhan publik dinonaktifkan.

## Konfigurasi `.env`

| Variabel | Fungsi |
|---|---|
| `APP_URL` | URL dasar aplikasi. Isi domain dan HTTPS yang benar pada server produksi. |
| `VERIFICATION_BASE_URL` | URL publik yang dicetak ke QR; biasanya sama dengan `APP_URL`. Tetapkan sebelum menerbitkan surat karena PDF yang sudah beredar tidak diperbarui otomatis. |
| `INSTANSI_NAMA`, `INSTANSI_SINGKATAN`, `INSTANSI_KONTAK` | Identitas yang digunakan pada halaman aplikasi. |
| `INSTANSI_LOGO_NAVBAR` | Path relatif ke `public/` untuk logo di navbar, misalnya `images/logo-navbar.svg`. |
| `INSTANSI_LOGO_FOOTER` | Path relatif ke `public/` untuk logo di footer, misalnya `images/logo-footer.svg`. |
| `INSTANSI_LOGO` | Logo fallback untuk kompatibilitas konfigurasi lama. |
| `ADMIN_EMAIL`, `ADMIN_PASSWORD` | Kredensial Super Admin awal untuk proses seeding. Ganti setelah masuk. |
| `ADMIN_LOGIN_PATH` | Path login khusus (default `masuk-admin`). |
| `UPLOAD_MAX_KB` | Batas ukuran unggahan PDF, default 10240 KB. |
| `QR_SIZE_MM`, `QR_MARGIN_MM` | Ukuran QR dan jarak dari tepi halaman PDF. |
| `LOGIN_MAX_ATTEMPTS`, `LOGIN_DECAY_SECONDS` | Pembatasan percobaan login. |
| `SESSION_LIFETIME`, `SESSION_SECURE_COOKIE` | Masa aktif dan pengamanan cookie sesi. |

ZIP sumber tidak berisi logo instansi kustom yang terpisah, sehingga `public/images/logo-navbar.svg` dan `public/images/logo-footer.svg` adalah logo placeholder bertema `#e7553c`. Ganti file tersebut dengan kedua logo resmi, atau atur variabel logo di atas.

## Keamanan dan catatan operasional

- Registrasi publik tidak disediakan. Akses menu manajemen diproteksi berdasarkan role.
- Surat di storage privat, bukan tautan file publik langsung. PDF diunduh melalui controller dengan pemeriksaan izin.
- Upload memvalidasi tipe, ukuran, dan signature PDF; nama file disimpan secara acak.
- QR menggunakan `VERIFICATION_BASE_URL`, bukan header `Host` dari request.
- Pengguna Unit dibatasi pada surat miliknya dan penandatangan ditetapkan dari akun login, bukan input bebas dari browser.
- Penghapusan surat tidak disediakan untuk menjaga jejak verifikasi. Akun pembuat surat juga tidak dapat dihapus selama masih tercatat sebagai pembuat; nonaktifkan akun bila diperlukan.
- Sebelum produksi, gunakan `APP_ENV=production`, `APP_DEBUG=false`, HTTPS, database produksi, backup database dan `storage/app/private/surat`, serta konfigurasi email yang benar untuk reset sandi.

## Catatan PDF

Penempelan QR menggunakan FPDI; setiap halaman PDF diimpor kembali dan QR digambar sebagai vektor. Sebagian elemen interaktif PDF asli (misalnya formulir, bookmark, dan tautan) tidak dipertahankan. PDF terenkripsi ditolak. PDF versi tertentu yang menggunakan kompresi modern mungkin perlu dinormalisasi ke PDF 1.4 sebelum diunggah.

## Tes

Jalankan tes fitur dengan:

```bash
php artisan test
```

Pastikan ekstensi database PHP yang sesuai dengan koneksi pada `.env.testing` tersedia, misalnya `pdo_sqlite` untuk SQLite.
