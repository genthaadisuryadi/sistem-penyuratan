<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RoleAdmin;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PenggunaController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->dapatKelolaPengguna(), 403);

        return view('admin.pengguna.index', [
            'pengguna' => User::query()
                ->when(! auth()->user()->isSuperadmin(), fn ($query) => $query->where('role', '!=', RoleAdmin::Superadmin->value))
                ->withCount('surat')->orderBy('name')->paginate(15),
            'kataSandiBaru' => session('kata_sandi_baru'),
        ]);
    }

    public function create(): View
    {
        abort_unless(auth()->user()->dapatKelolaPengguna(), 403);

        return view('admin.pengguna.form', ['pengguna' => new User(), 'roles' => $this->rolesTersedia()]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->dapatKelolaPengguna(), 403);
        
        $data = $this->validasi($request, null);
        
        // PERBAIKAN: Enkripsi password menggunakan Hash::make()
        $data['password'] = Hash::make($data['email']); 
        
        // Dummy username untuk mencegah error DB jika kolom masih Not Null
        $data['username'] = Str::slug(explode('@', $data['email'])[0]) . '_' . Str::random(5); 

        User::create($data + ['is_active' => true]);

        return redirect()->route('admin.pengguna.index')->with('status', 'Pengguna berhasil ditambahkan dengan password default (email).');
    }

    public function edit(User $pengguna): View
    {
        abort_unless(auth()->user()->dapatKelolaPengguna(), 403);
        abort_if(! auth()->user()->isSuperadmin() && $pengguna->role === RoleAdmin::Superadmin, 403);

        return view('admin.pengguna.form', ['pengguna' => $pengguna, 'roles' => $this->rolesTersedia()]);
    }

    public function update(Request $request, User $pengguna): RedirectResponse
    {
        abort_unless(auth()->user()->dapatKelolaPengguna(), 403);
        abort_if(! auth()->user()->isSuperadmin() && $pengguna->role === RoleAdmin::Superadmin, 403);
        
        $data = $this->validasi($request, $pengguna);
        $data['is_active'] = $request->boolean('is_active');

        if (auth()->id() === $pengguna->id && $data['role'] !== $pengguna->role->value) {
            return back()->withInput()->withErrors(['role' => 'Role akun yang sedang digunakan tidak dapat diubah dari halaman ini.']);
        }

        $pengguna->update($data);

        return redirect()->route('admin.pengguna.index')->with('status', 'Data pengguna berhasil diperbarui.');
    }

    public function resetPassword(User $pengguna): RedirectResponse
    {
        abort_unless(auth()->user()->dapatKelolaPengguna(), 403);
        abort_if(! auth()->user()->isSuperadmin() && $pengguna->role === RoleAdmin::Superadmin, 403);

        // PERBAIKAN: Enkripsi saat reset password dikembalikan ke email
        $pengguna->update(['password' => Hash::make($pengguna->email)]);

        return redirect()->route('admin.pengguna.index')->with('status', "Kata sandi untuk {$pengguna->name} berhasil direset kembali ke email default.");
    }

    public function destroy(User $pengguna): RedirectResponse
    {
        abort_unless(auth()->user()->dapatKelolaPengguna(), 403);
        abort_if(! auth()->user()->isSuperadmin() && $pengguna->role === RoleAdmin::Superadmin, 403);

        if (auth()->id() === $pengguna->id) {
            return back()->withErrors(['pengguna' => 'Akun yang sedang digunakan tidak boleh dihapus.']);
        }

        if ($pengguna->surat()->exists()) {
            return back()->withErrors(['pengguna' => 'Pengguna ini tercatat sebagai pembuat surat. Nonaktifkan akun agar audit dan riwayat surat tetap terjaga.']);
        }

        $pengguna->delete();

        return back()->with('status', 'Pengguna berhasil dihapus.');
    }

    private function validasi(Request $request, ?User $pengguna): array
    {
        $rulesRole = auth()->user()->isSuperadmin()
            ? Rule::enum(RoleAdmin::class)
            : Rule::in([RoleAdmin::Sekretariat->value, RoleAdmin::Unit->value]);

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'jabatan' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($pengguna)],
            'role' => ['required', $rulesRole],
        ]);
    }

    private function rolesTersedia(): array
    {
        return auth()->user()->isSuperadmin()
            ? RoleAdmin::cases()
            : [RoleAdmin::Sekretariat, RoleAdmin::Unit];
    }
}