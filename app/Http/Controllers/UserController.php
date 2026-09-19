<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserAddress;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function profile()
    {
        $user = Auth::user()->load('addresses');

        return view('user.profile', [
            'judul' => 'Profil Saya',
            'user' => $user,
            'addresses' => $user->addresses,
        ]);
    }

    public function updateProfile(Request $request, CloudinaryService $cloudinary)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:users,username,' . $user->id],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone_number' => ['required', 'string', 'max:20'],
            'image' => ['nullable', 'image', 'max:3072'],
        ]);

        if ($request->hasFile('image')) {
            $imageUrl = $cloudinary->upload($request->file('image'), 'user_profiles');
            $validated['image'] = $imageUrl;
        }

        $user->update($validated);

        return back()->with('alert', [
            'type' => 'success',
            'message' => 'Profil berhasil diperbarui.',
        ]);
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required'],
            'new_password' => ['required', 'min:6'],
            'confirm_password' => ['required', 'same:new_password'],
        ]);

        $user = Auth::user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->with('alert', [
                'type' => 'error',
                'message' => 'Password saat ini salah!',
            ]);
        }

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return back()->with('alert', [
            'type' => 'success',
            'message' => 'Password berhasil diubah.',
        ]);
    }

    public function addAddress(Request $request)
    {
        $validated = $request->validate([
            'label' => ['nullable', 'string', 'max:100'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $user = Auth::user();
        $isDefault = $request->boolean('is_default') || $user->addresses()->count() === 0;

        if ($isDefault) {
            $user->addresses()->update(['is_default' => false]);
        }

        $user->addresses()->create([
            'label' => $validated['label'] ?? 'Rumah',
            'address_line_1' => $validated['address_line_1'],
            'address_line_2' => $validated['address_line_2'] ?? null,
            'city' => $validated['city'],
            'postal_code' => $validated['postal_code'],
            'country' => $validated['country'] ?? 'Indonesia',
            'is_default' => $isDefault,
        ]);

        return back()->with('alert', [
            'type' => 'success',
            'message' => 'Alamat berhasil ditambahkan.',
        ]);
    }

    public function setDefaultAddress($id)
    {
        $user = Auth::user();
        $address = $user->addresses()->findOrFail($id);

        $user->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return back()->with('alert', [
            'type' => 'success',
            'message' => 'Alamat utama berhasil diubah.',
        ]);
    }

    public function deleteAddress($id)
    {
        $user = Auth::user();
        $address = $user->addresses()->findOrFail($id);
        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault) {
            $firstRemaining = $user->addresses()->first();
            if ($firstRemaining) {
                $firstRemaining->update(['is_default' => true]);
            }
        }

        return back()->with('alert', [
            'type' => 'success',
            'message' => 'Alamat berhasil dihapus.',
        ]);
    }
}