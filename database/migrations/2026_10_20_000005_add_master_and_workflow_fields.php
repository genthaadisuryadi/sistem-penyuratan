<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_surat', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100)->unique();
            $table->string('kode', 20)->unique();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('jabatan', 150)->nullable()->after('name');
            $table->string('role', 20)->default('unit')->change();
        });

        Schema::table('surat', function (Blueprint $table) {
            $table->foreignId('jenis_surat_id')->nullable()->after('id')->constrained('jenis_surat')->nullOnDelete();
            $table->string('ditujukan_kepada', 255)->nullable()->after('perihal');
            $table->foreignId('penandatangan_id')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable()->index()->after('status');
            $table->string('status', 10)->default('draf')->change();
        });

        // Konversi status versi sebelumnya tanpa menghilangkan data yang telah tercatat.
        DB::table('users')->where('role', 'admin')->update(['role' => 'sekretariat']);
        DB::table('surat')->where('status', 'aktif')->update(['status' => 'publish', 'published_at' => now()]);
        DB::table('surat')->where('status', 'dicabut')->update(['status' => 'ditarik']);
    }

    public function down(): void
    {
        Schema::table('surat', function (Blueprint $table) {
            $table->dropConstrainedForeignId('jenis_surat_id');
            $table->dropConstrainedForeignId('penandatangan_id');
            $table->dropColumn(['ditujukan_kepada', 'published_at']);
            $table->string('status', 10)->default('aktif')->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('jabatan');
        });

        Schema::dropIfExists('jenis_surat');
    }
};
