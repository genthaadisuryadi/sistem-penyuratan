<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat', function (Blueprint $table) {
            $table->id(); // hanya dipakai internal, tidak pernah muncul di URL
            $table->uuid('uuid')->unique(); // identitas publik (UUID v4)
            $table->string('kode_verifikasi', 12)->unique(); // untuk pencarian manual

            $table->string('nomor_surat')->unique();
            $table->string('perihal');
            $table->date('tanggal_surat')->index();
            $table->string('penandatangan_nama');
            $table->string('penandatangan_jabatan');

            $table->string('qr_posisi', 20);
            $table->string('qr_halaman', 10);

            $table->string('file_asli_path');
            $table->string('file_final_path');
            $table->char('file_hash_sha256', 64);
            $table->unsignedInteger('file_size');

            $table->string('status', 10)->default('aktif')->index();
            $table->text('alasan_pencabutan')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->foreignId('revoked_by')->nullable()->constrained('users')->nullOnDelete();

            $table->unsignedInteger('scan_count')->default(0);
            $table->timestamp('last_scanned_at')->nullable();

            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat');
    }
};
