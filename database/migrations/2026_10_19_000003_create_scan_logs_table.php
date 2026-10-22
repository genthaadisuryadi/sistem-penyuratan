<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scan_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_id')->constrained('surat')->restrictOnDelete();
            $table->timestamp('scanned_at');
            $table->string('status_saat_scan', 10);
            $table->char('ip_hash', 64); // HMAC, bukan IP mentah
            $table->string('user_agent', 255)->nullable();
            $table->boolean('is_bot')->default(false);

            $table->index(['surat_id', 'scanned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scan_logs');
    }
};
