<?php

namespace Tests\Feature;

use App\Enums\StatusSurat;
use App\Models\Surat;
use App\Models\JenisSurat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Support\BuatPdf;
use Tests\TestCase;

class AlurSuratTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('surat');
        config(['penyuratan.verification_base_url' => 'https://surat.contoh.test']);
        JenisSurat::create(['nama' => 'Surat Undangan', 'kode' => 'UND', 'is_active' => true]);
        $this->admin = User::factory()->superadmin()->create();
    }

    private function data(array $ubah = []): array
    {
        return array_merge([
            'jenis_surat_id' => JenisSurat::firstOrFail()->id,
            'nomor_surat' => '123/UND/X/2026',
            'perihal' => 'Undangan rapat koordinasi',
            'ditujukan_kepada' => 'Kepala Bagian Umum',
            'status' => 'publish',
            'tanggal_surat' => '2026-10-19',
            'penandatangan_nama' => 'Dra. Siti Aminah',
            'penandatangan_jabatan' => 'Kepala Dinas',
            'berkas' => UploadedFile::fake()->createWithContent('draf.pdf', BuatPdf::isi(2)),
        ], $ubah);
    }

    private function terbitkan(array $ubah = []): Surat
    {
        $this->actingAs($this->admin)->post(route('admin.surat.store'), $this->data($ubah))
            ->assertSessionHasNoErrors();

        return Surat::firstOrFail();
    }

    public function test_menerbitkan_surat_membuat_uuid_v4_kode_hash_dan_berkas_privat(): void
    {
        $surat = $this->terbitkan();

        $this->assertTrue(Str::isUuid($surat->uuid));
        $this->assertSame('4', $surat->uuid[14], 'UUID harus versi 4 (acak), bukan v7 yang berurutan waktu');
        $this->assertMatchesRegularExpression('/^[A-HJ-NP-Z2-9]{10}$/', $surat->kode_verifikasi);
        $this->assertSame(StatusSurat::Aktif, $surat->status);
        $this->assertSame('kanan-bawah', $surat->qr_posisi->value);
        $this->assertSame('terakhir', $surat->qr_halaman->value);

        $disk = Storage::disk('surat');
        $this->assertTrue($disk->exists($surat->file_final_path));
        $this->assertTrue($disk->exists($surat->file_asli_path));
        $this->assertSame(hash_file('sha256', $disk->path($surat->file_final_path)), $surat->file_hash_sha256);
        $this->assertStringStartsWith('%PDF-', file_get_contents($disk->path($surat->file_final_path), false, null, 0, 8));

        $this->assertSame("https://surat.contoh.test/verify/123/UND/X/2026", $surat->urlVerifikasi());
        $this->assertDatabaseHas('audit_logs', ['action' => 'terbit', 'user_id' => $this->admin->id]);
    }

    public function test_berkas_bukan_pdf_ditolak_dan_tidak_ada_yang_tersimpan(): void
    {
        $palsu = UploadedFile::fake()->createWithContent('draf.pdf', 'ini bukan pdf sama sekali');

        $this->actingAs($this->admin)->post(route('admin.surat.store'), $this->data(['berkas' => $palsu]))
            ->assertSessionHasErrors('berkas');

        $this->assertDatabaseCount('surat', 0);
        $this->assertSame([], Storage::disk('surat')->allFiles());
    }

    public function test_nomor_surat_harus_unik_dan_tamu_tidak_boleh_menerbitkan(): void
    {
        $this->terbitkan();

        $this->actingAs($this->admin)->post(route('admin.surat.store'), $this->data())
            ->assertSessionHasErrors('nomor_surat');

        auth()->logout();
        $this->post(route('admin.surat.store'), $this->data(['nomor_surat' => 'baru']))
            ->assertRedirect(route('login'));
        $this->assertDatabaseCount('surat', 1);
    }

    public function test_halaman_publik_menampilkan_surat_sah_dan_mencatat_pemindaian(): void
    {
        $surat = $this->terbitkan();
        auth()->logout();

        $this->withHeaders(['User-Agent' => 'Mozilla/5.0 (iPhone) Safari'])
            ->get("/surat/{$surat->uuid}")
            ->assertOk()
            ->assertSee('DOKUMEN TERVERIFIKASI &amp; ASLI', false)
            ->assertSee('123/UND/X/2026')
            ->assertSee('Undangan rapat koordinasi')
            ->assertSee('Dra. Siti Aminah')
            ->assertSee('Unduh Dokumen Asli (PDF)')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');

        $surat->refresh();
        $this->assertSame(1, $surat->scan_count);
        $this->assertNotNull($surat->last_scanned_at);
        $this->assertDatabaseHas('scan_logs', ['surat_id' => $surat->id, 'is_bot' => false, 'status_saat_scan' => 'publish']);
    }

    public function test_alamat_ip_tidak_disimpan_mentah(): void
    {
        $surat = $this->terbitkan();
        auth()->logout();

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.9'])
            ->withHeaders(['User-Agent' => 'Mozilla/5.0 Safari'])
            ->get("/surat/{$surat->uuid}");

        $log = $surat->scanLogs()->firstOrFail();
        $this->assertSame(64, strlen($log->ip_hash));
        $this->assertStringNotContainsString('203.0.113.9', json_encode($log->getAttributes()));
    }

    public function test_pindai_berulang_dalam_jendela_dihitung_sekali_dan_bot_tidak_dihitung(): void
    {
        $surat = $this->terbitkan();
        auth()->logout();
        $ua = ['User-Agent' => 'Mozilla/5.0 Safari'];

        $this->withHeaders($ua)->get("/surat/{$surat->uuid}");
        $this->withHeaders($ua)->get("/surat/{$surat->uuid}");
        $this->withHeaders(['User-Agent' => 'WhatsApp/2.23 A'])->get("/surat/{$surat->uuid}");
        $this->withHeaders(['User-Agent' => 'Googlebot/2.1'])->get("/surat/{$surat->uuid}");

        $this->assertSame(1, $surat->fresh()->scan_count);
        $this->assertSame(1, $surat->scanLogs()->where('is_bot', true)->where('user_agent', 'like', 'WhatsApp%')->count());
    }

    public function test_unduh_publik_mengirim_pdf_final(): void
    {
        $surat = $this->terbitkan();
        auth()->logout();

        $res = $this->get("/surat/{$surat->uuid}/unduh")->assertOk();
        $this->assertSame('application/pdf', $res->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment', $res->headers->get('Content-Disposition'));
        $this->assertStringContainsString('123-und-x-2026.pdf', $res->headers->get('Content-Disposition'));
    }

    public function test_pencabutan_menampilkan_peringatan_dan_memblokir_unduhan(): void
    {
        $surat = $this->terbitkan();

        $this->actingAs($this->admin)->post(route('admin.surat.cabut', $surat), ['alasan' => 'x'])
            ->assertSessionHasErrors('alasan');
        $this->assertTrue($surat->fresh()->isAktif());

        $this->post(route('admin.surat.cabut', $surat), ['alasan' => 'Diganti dengan surat nomor 124/UND/X/2026'])
            ->assertSessionHas('status');

        $surat->refresh();
        $this->assertSame(StatusSurat::Dicabut, $surat->status);
        $this->assertSame($this->admin->id, $surat->revoked_by);
        $this->assertDatabaseHas('audit_logs', ['action' => 'cabut', 'surat_id' => $surat->id]);

        auth()->logout();

        // URL QR tetap aktif, tetapi memperlihatkan peringatan.
        $this->get("/surat/{$surat->uuid}")
            ->assertOk()
            ->assertSee('SURAT TELAH DITARIK')
            ->assertDontSee('DOKUMEN TERVERIFIKASI')
            ->assertDontSee('Unduh Dokumen Asli (PDF)')
            ->assertDontSee('Diganti dengan surat nomor', false);

        $this->get("/surat/{$surat->uuid}/unduh")->assertForbidden();
    }

    public function test_mencabut_dua_kali_ditolak_policy(): void
    {
        $surat = $this->terbitkan();
        $this->actingAs($this->admin)->post(route('admin.surat.cabut', $surat), ['alasan' => 'Alasan yang cukup panjang']);

        $this->post(route('admin.surat.cabut', $surat), ['alasan' => 'Alasan yang cukup panjang'])->assertForbidden();
    }

    public function test_anti_idor_id_auto_increment_tidak_bisa_dipakai(): void
    {
        $surat = $this->terbitkan();
        auth()->logout();

        $this->get('/surat/'.$surat->id)->assertNotFound();
        $this->get('/surat/'.$surat->id.'/unduh')->assertNotFound();

        $this->actingAs($this->admin)->get('/admin/surat/'.$surat->id)->assertNotFound();
        $this->get(route('admin.surat.show', $surat))->assertOk();

        $this->assertArrayNotHasKey('id', $surat->toArray());
        $this->assertArrayNotHasKey('file_final_path', $surat->toArray());
    }

    public function test_uuid_tidak_dikenal_menampilkan_halaman_tidak_ditemukan(): void
    {
        $this->get('/surat/'.Str::uuid())
            ->assertNotFound()
            ->assertSee('DOKUMEN TIDAK DITEMUKAN');
    }

    public function test_berkas_tidak_bisa_diakses_langsung_lewat_url_storage(): void
    {
        $surat = $this->terbitkan();
        auth()->logout();

        $this->get('/storage/'.$surat->file_final_path)->assertNotFound();
        $this->get('/storage/surat/'.$surat->file_final_path)->assertNotFound();
        $this->assertNull(config('filesystems.disks.surat.url'));
        $this->assertFalse(config('filesystems.disks.surat.serve'));
        $this->assertDirectoryDoesNotExist(public_path('storage/surat'));
    }

    public function test_pencarian_manual_dengan_kode_verifikasi(): void
    {
        $surat = $this->terbitkan();
        auth()->logout();

        $kode = strtolower($surat->kodeTampil()); // huruf kecil + tanda hubung tetap diterima
        $this->post('/cek', ['kode' => $kode])->assertRedirect("/surat/{$surat->uuid}");

        $this->from('/')->post('/cek', ['kode' => 'AAAAA-BBBBB'])
            ->assertRedirect('/')->assertSessionHasErrors('kode');
        $this->post('/cek', ['kode' => $surat->nomor_surat])->assertSessionHasErrors('kode');
    }

    public function test_pencarian_manual_dibatasi_laju(): void
    {
        for ($i = 0; $i < 8; $i++) {
            $this->post('/cek', ['kode' => 'AAAAA-BBBBB']);
        }

        $this->post('/cek', ['kode' => 'AAAAA-BBBBB'])->assertStatus(429);
    }

    public function test_admin_bisa_mengunduh_final_dan_draf_asli_serta_arsip_menampilkan_surat(): void
    {
        $surat = $this->terbitkan();

        $this->actingAs($this->admin)->get(route('admin.surat.unduh', $surat))->assertOk()->assertDownload('123-und-x-2026.pdf');
        $this->get(route('admin.surat.unduh', [$surat, 'versi' => 'asli']))->assertOk()->assertDownload('draf-asli-123-und-x-2026.pdf');

        $this->get(route('admin.surat.index'))->assertOk()->assertSee('123/UND/X/2026')->assertSee('Tarik');
        $this->get(route('admin.surat.index', ['q' => 'tidak-ada-ini']))->assertOk()->assertSee('Tidak ada surat yang cocok');
        $this->get(route('admin.dashboard'))->assertOk()->assertSee('Surat dibuat');
        $this->get(route('admin.surat.create'))
            ->assertOk()
            ->assertSee('Pratinjau QR Verifikasi')
            ->assertSee('Download QR Code')
            ->assertDontSee('Posisi QR di PDF')
            ->assertDontSee('Halaman QR');
        $this->get(route('admin.pengaturan'))->assertOk();
    }


    public function test_qr_nomor_surat_membuka_halaman_verifikasi_baru(): void
    {
        $surat = $this->terbitkan();
        auth()->logout();

        $this->get('/verify/123/UND/X/2026')
            ->assertOk()
            ->assertSee('DOKUMEN TERVERIFIKASI &amp; ASLI', false)
            ->assertSee('123/UND/X/2026');
    }

    public function test_unit_hanya_melihat_surat_sendiri_dan_penandatangan_diambil_dari_pengguna_login(): void
    {
        $unit = User::factory()->create(['role' => \App\Enums\RoleAdmin::Unit]);
        $dataUnit = $this->data([
            'nomor_surat' => 'UNIT/001/X/2026',
            'status' => 'draf',
        ]);
        unset($dataUnit['penandatangan_nama'], $dataUnit['penandatangan_jabatan']);

        $this->actingAs($unit)->post(route('admin.surat.store'), $dataUnit)->assertSessionHasNoErrors();
        $suratUnit = Surat::where('nomor_surat', 'UNIT/001/X/2026')->firstOrFail();
        $this->assertSame($unit->id, $suratUnit->created_by);
        $this->assertSame($unit->name, $suratUnit->penandatangan_nama);
        $this->assertSame($unit->jabatan, $suratUnit->penandatangan_jabatan);
        $this->assertSame(StatusSurat::Draf, $suratUnit->status);

        $this->actingAs($this->admin)->post(route('admin.surat.store'), $this->data())->assertSessionHasNoErrors();
        $suratAdmin = Surat::where('nomor_surat', '123/UND/X/2026')->firstOrFail();

        $this->actingAs($unit)->get(route('admin.surat.index'))
            ->assertOk()->assertSee('UNIT/001/X/2026')->assertDontSee('123/UND/X/2026');
        $this->get(route('admin.surat.show', $suratAdmin))->assertForbidden();
    }

    public function test_sekretariat_dapat_menerbitkan_atas_nama_orang_lain_dan_mengelola_pengguna(): void
    {
        $sekretariat = User::factory()->create(['role' => \App\Enums\RoleAdmin::Sekretariat]);

        $this->actingAs($sekretariat)->post(route('admin.surat.store'), $this->data([
            'nomor_surat' => 'SEK/001/X/2026',
            'penandatangan_nama' => 'Rektor Contoh',
            'penandatangan_jabatan' => 'Rektor',
        ]))->assertSessionHasNoErrors();

        $surat = Surat::where('nomor_surat', 'SEK/001/X/2026')->firstOrFail();
        $this->assertSame('Rektor Contoh', $surat->penandatangan_nama);
        $this->assertSame($sekretariat->id, $surat->created_by);
        $this->get(route('admin.pengguna.index'))->assertOk();
        $this->get(route('admin.jenis-surat.index'))->assertOk();
    }

    public function test_ganti_kata_sandi_butuh_sandi_lama_dan_aturan_kekuatan(): void
    {
        $this->actingAs($this->admin)->put(route('admin.pengaturan.sandi'), [
            'password_lama' => 'salah', 'password' => 'Abcdefghij12', 'password_confirmation' => 'Abcdefghij12',
        ])->assertSessionHasErrors('password_lama');

        $this->put(route('admin.pengaturan.sandi'), [
            'password_lama' => 'password', 'password' => 'lemah', 'password_confirmation' => 'lemah',
        ])->assertSessionHasErrors('password');

        $this->put(route('admin.pengaturan.sandi'), [
            'password_lama' => 'password', 'password' => 'SandiBaru2026', 'password_confirmation' => 'SandiBaru2026',
        ])->assertSessionHasNoErrors();
        $this->assertTrue(\Hash::check('SandiBaru2026', $this->admin->fresh()->password));
    }
}
