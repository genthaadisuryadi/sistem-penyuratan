<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:255']]);

        // Hasilnya sengaja diabaikan: respons selalu sama agar email terdaftar tidak bisa ditebak.
        Password::sendResetLink(['email' => $data['email'], 'is_active' => true]);

        return back()->with('status', 'Jika email tersebut terdaftar, tautan untuk mengatur ulang kata sandi sudah dikirim. Periksa kotak masuk Anda.');
    }
}
