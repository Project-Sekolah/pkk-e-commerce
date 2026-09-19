<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::latest()->get();

        return view('admin.user-management', [
            'judul' => 'Manajemen Pengguna - Admin',
            'users' => $users,
            'user' => Auth::user(),
        ]);
    }

    public function toggleBlock($id)
    {
        $user = User::findOrFail($id);
        if ($user->id === Auth::id()) {
            return back()->with('alert', [
                'type' => 'error',
                'message' => 'Anda tidak dapat memblokir akun Anda sendiri.',
            ]);
        }

        $user->is_blocked = !$user->is_blocked;
        $user->save();

        $msg = $user->is_blocked ? 'Akun berhasil diblokir.' : 'Akun berhasil diaktifkan kembali.';

        return back()->with('alert', [
            'type' => 'success',
            'message' => $msg,
        ]);
    }

    public function updateRole(Request $request, $id)
    {
        $validated = $request->validate([
            'role' => ['required', 'in:buyer,seller,admin'],
        ]);

        $user = User::findOrFail($id);
        $user->role = $validated['role'];
        $user->save();

        return back()->with('alert', [
            'type' => 'success',
            'message' => 'Peran pengguna berhasil diubah.',
        ]);
    }

    public function softDelete($id)
    {
        $user = User::findOrFail($id);
        if ($user->id === Auth::id()) {
            return back()->with('alert', [
                'type' => 'error',
                'message' => 'Anda tidak dapat menonaktifkan akun Anda sendiri.',
            ]);
        }

        $user->delete();

        return back()->with('alert', [
            'type' => 'success',
            'message' => 'Akun pengguna berhasil dinonaktifkan.',
        ]);
    }
}