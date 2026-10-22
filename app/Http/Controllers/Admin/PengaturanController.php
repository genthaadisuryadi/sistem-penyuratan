<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PengaturanController extends Controller
{
    public function edit(Request $request): View
    {
        return view('admin.pengaturan', ['user' => $request->user()]);
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
}
