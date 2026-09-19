<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('username', $credentials['username'])
            ->orWhere('email', $credentials['username'])
            ->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()->with('alert', [
                'type' => 'error',
                'message' => 'Username atau Password salah!',
            ])->withInput();
        }

        if ($user->is_blocked) {
            return back()->with('alert', [
                'type' => 'error',
                'message' => 'Akun Anda telah diblokir. Silakan hubungi admin.',
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended('/')->with('alert', [
            'type' => 'success',
            'message' => 'Selamat datang kembali, ' . $user->full_name . '!',
        ]);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone_number' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:6'],
            'confirm_password' => ['required', 'same:password'],
            'role' => ['nullable', 'in:buyer,seller'],
        ]);

        $user = User::create([
            'username' => $validated['username'],
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'] ?? 'buyer',
            'is_active' => true,
            'is_blocked' => false,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/')->with('alert', [
            'type' => 'success',
            'message' => 'Pendaftaran berhasil! Selamat berbelanja di Lunerburg & Co.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('alert', [
            'type' => 'success',
            'message' => 'Anda telah berhasil logout.',
        ]);
    }
}