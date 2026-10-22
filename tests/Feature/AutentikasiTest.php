<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AutentikasiTest extends TestCase
{
    use RefreshDatabase;

    private function url(): string
    {
        return '/'.config('penyuratan.login_path');
    }

    public function test_halaman_login_tampil_dengan_elemen_wajib(): void
    {
        $this->get($this->url())
            ->assertOk()
            ->assertSee('Email atau username')
            ->assertSee('Kata sandi')
            ->assertSee('Lupa kata sandi?')
            ->assertSee('data-toggle-password', false);
    }

    public function test_login_dengan_email_dan_username_berhasil(): void
    {
        $user = User::factory()->create(['username' => 'budi', 'email' => 'budi@contoh.test']);

        $this->post($this->url(), ['login' => 'budi@contoh.test', 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->last_login_at);

        auth()->logout();

        $this->post($this->url(), ['login' => 'budi', 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_password_disimpan_dengan_hash_bcrypt(): void
    {
        $user = User::factory()->create();

        $this->assertStringStartsWith('$2y$', $user->getRawOriginal('password'));
        $this->assertTrue(Hash::check('password', $user->password));
    }

    public function test_kredensial_salah_ditolak(): void
    {
        User::factory()->create(['email' => 'a@b.test']);

        $this->from($this->url())
            ->post($this->url(), ['login' => 'a@b.test', 'password' => 'salah'])
            ->assertRedirect($this->url())
            ->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_akun_nonaktif_tidak_bisa_masuk(): void
    {
        User::factory()->nonaktif()->create(['email' => 'mati@b.test']);

        $this->post($this->url(), ['login' => 'mati@b.test', 'password' => 'password'])
            ->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_dibatasi_setelah_lima_percobaan_gagal(): void
    {
        RateLimiter::clear('x');
        User::factory()->create(['email' => 'brute@b.test']);

        foreach (range(1, 5) as $i) {
            $this->post($this->url(), ['login' => 'brute@b.test', 'password' => 'salah'.$i]);
        }

        // Percobaan ke-6 memakai kata sandi BENAR, tetapi tetap ditolak karena terkunci.
        $this->post($this->url(), ['login' => 'brute@b.test', 'password' => 'password'])
            ->assertSessionHasErrors('login');
        $this->assertGuest();
        $this->assertStringContainsString('Terlalu banyak percobaan', session('errors')->first('login'));
    }

    public function test_tamu_dialihkan_ke_login_dan_halaman_admin_tidak_dicache(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertHeader('Cache-Control');
    }

    public function test_logout_mengakhiri_sesi(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.logout'))
            ->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_akun_dinonaktifkan_saat_sesi_berjalan_langsung_keluar(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $user->update(['is_active' => false]);

        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_header_keamanan_terpasang(): void
    {
        $this->get('/')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_lupa_kata_sandi_tidak_membocorkan_email_terdaftar(): void
    {
        \Illuminate\Support\Facades\Notification::fake();
        $user = User::factory()->create(['email' => 'ada@b.test']);

        $a = $this->post('/lupa-sandi', ['email' => 'ada@b.test']);
        $b = $this->post('/lupa-sandi', ['email' => 'tidak-ada@b.test']);

        $a->assertSessionHas('status');
        $b->assertSessionHas('status');
        $this->assertSame(session('status'), $a->getSession()->get('status'));
        \Illuminate\Support\Facades\Notification::assertSentTo($user, \Illuminate\Auth\Notifications\ResetPassword::class);
        \Illuminate\Support\Facades\Notification::assertNothingSentTo(User::factory()->make(['email' => 'tidak-ada@b.test']));
    }

    public function test_atur_ulang_kata_sandi_dengan_token_valid(): void
    {
        $user = User::factory()->create(['email' => 'reset@b.test']);
        $token = \Illuminate\Support\Facades\Password::createToken($user);

        $this->get('/reset-sandi/'.$token.'?email=reset@b.test')->assertOk()->assertSee('Buat kata sandi baru');

        $this->post('/reset-sandi', [
            'token' => $token, 'email' => 'reset@b.test',
            'password' => 'SandiBaru2026', 'password_confirmation' => 'SandiBaru2026',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('SandiBaru2026', $user->fresh()->password));

        $this->post('/reset-sandi', [
            'token' => $token, 'email' => 'reset@b.test',
            'password' => 'LainLagi2026x', 'password_confirmation' => 'LainLagi2026x',
        ])->assertSessionHasErrors('email');
    }
}
