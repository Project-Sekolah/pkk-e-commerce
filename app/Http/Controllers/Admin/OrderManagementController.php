<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderManagementController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:pending,paid,shipped,completed,cancelled'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $orders = Order::with(['user', 'items.product', 'shippingAddress'])
            ->when($validated['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($validated['search'] ?? null, function ($query, $search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('id', 'like', "%{$search}%")
                        ->orWhere('midtrans_order_id', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($user) => $user
                            ->where('full_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.orders', [
            'judul' => 'Manajemen Pesanan - Admin',
            'orders' => $orders,
            'selectedStatus' => $validated['status'] ?? '',
            'search' => $validated['search'] ?? '',
            'statusCounts' => Order::select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status'),
        ]);
    }

    public function updateStatus(Request $request, string $id)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:shipped,completed,cancelled'],
        ]);

        DB::transaction(function () use ($validated, $id, $request) {
            $order = Order::with('items')->lockForUpdate()->findOrFail($id);
            $from = $order->status;
            $to = $validated['status'];

            $allowed = [
                'paid' => ['shipped'],
                'shipped' => ['completed'],
                'pending' => ['cancelled'],
            ];

            abort_unless(in_array($to, $allowed[$from] ?? [], true), 422, "Perubahan status {$from} ke {$to} tidak diizinkan.");

            if ($to === 'cancelled') {
                foreach ($order->items as $item) {
                    Product::whereKey($item->product_id)->lockForUpdate()->increment('stock', $item->quantity);
                }
            }

            $order->update(['status' => $to]);

            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'order.status_updated',
                'table_name' => 'orders',
                'record_id' => $order->id,
                'description' => "Status order diubah dari {$from} menjadi {$to}.",
                'old_data' => ['status' => $from],
                'new_data' => ['status' => $to],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        });

        return back()->with('alert', [
            'type' => 'success',
            'message' => 'Status pesanan berhasil diperbarui.',
        ]);
    }
}
