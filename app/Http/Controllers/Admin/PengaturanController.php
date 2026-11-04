<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RoleAdmin;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PengaturanController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();
        
        // PERBAIKAN: Dikosongkan (tidak pakai template lagi)
        $namaInstansi = '';
        if (Storage::disk('local')->exists('pengaturan_sistem.json')) {
            $config = json_decode(Storage::disk('local')->get('pengaturan_sistem.json'), true);
            if (isset($config['nama_instansi'])) {
                $namaInstansi = $config['nama_instansi'];
            }
        }

        return view('admin.pengaturan', [
            'user' => $user,
            'namaInstansi' => $namaInstansi,
            'isUnit' => $user->role === RoleAdmin::Unit,
        ]);
    }

    public function updateProfil(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'jabatan' => ['nullable', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'username' => ['nullable', 'alpha_dash', 'max:50', Rule::unique('users', 'username')->ignore($user)],
        ], [], ['name' => 'nama']);

        $user->update($data);

        return back()->with('status_profil', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'password_lama' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'different:password_lama', Password::defaults()],
        ], [], ['password_lama' => 'kata sandi saat ini', 'password' => 'kata sandi baru']);

        $request->user()->update(['password' => $data['password']]);
        $request->session()->regenerate();

        return back()->with('status_sandi', 'Kata sandi berhasil diganti.');
    }

    public function updateSistem(Request $request): RedirectResponse
    {
        abort_if($request->user()->role === RoleAdmin::Unit, 403);

        $data = $request->validate([
            'nama_instansi' => ['required', 'string', 'max:255'],
        ]);

        Storage::disk('local')->put('pengaturan_sistem.json', json_encode([
            'nama_instansi' => $data['nama_instansi']
        ]));

        return back()->with('status_sistem', 'Pengaturan sistem berhasil diperbarui.');
    }
}