<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DiscountController extends Controller
{
    public function index()
    {
        $discounts = Discount::where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('discount.index', [
            'judul' => 'Daftar Diskon Saya - Lunerburg & Co',
            'discounts' => $discounts,
        ]);
    }

    public function create()
    {
        return view('discount.add', [
            'judul' => 'Tambah Diskon Baru',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'percentage' => ['required', 'numeric', 'min:1', 'max:100'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        Discount::create([
            'name' => $validated['name'],
            'percentage' => $validated['percentage'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'is_active' => true,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('discounts.index')->with('alert', [
            'type' => 'success',
            'message' => 'Diskon berhasil ditambahkan.',
        ]);
    }

    public function edit($id)
    {
        $discount = Discount::where('user_id', Auth::id())->findOrFail($id);

        return view('discount.edit', [
            'judul' => 'Edit Diskon',
            'discount' => $discount,
        ]);
    }

    public function update(Request $request, $id)
    {
        $discount = Discount::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'percentage' => ['required', 'numeric', 'min:1', 'max:100'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $discount->update($validated);

        return redirect()->route('discounts.index')->with('alert', [
            'type' => 'success',
            'message' => 'Diskon berhasil diperbarui.',
        ]);
    }

    public function destroy($id)
    {
        $discount = Discount::where('user_id', Auth::id())->findOrFail($id);
        $discount->delete();

        return back()->with('alert', [
            'type' => 'success',
            'message' => 'Diskon berhasil dihapus.',
        ]);
    }
}