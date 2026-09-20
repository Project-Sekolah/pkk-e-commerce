<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ShippingAddress;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class OrderController extends Controller
{
    private function paymentStatusLabel(Order $order): string
    {
        return match ($order->status ?? 'pending') {
            'pending' => 'Menunggu Pembayaran',
            'paid' => 'Pembayaran Berhasil',
            'shipped' => 'Sedang Dikirim',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => ucfirst((string) $order->status),
        };
    }

    private function paymentStatusBadgeClass(string $status): string
    {
        return match ($status) {
            'pending' => 'bg-warning text-dark',
            'paid' => 'bg-success',
            'shipped' => 'bg-info text-dark',
            'completed' => 'bg-primary',
            'cancelled' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    public function history()
    {
        $user = Auth::user();
        $orders = Order::with(['items.product', 'shippingAddress'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $formattedOrders = $orders->map(function ($order) {
            $status = $order->status ?? 'pending';

            return [
                'id' => $order->id,
                'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                'status' => $status,
                'status_label' => $this->paymentStatusLabel($order),
                'badge_class' => $this->paymentStatusBadgeClass($status),
                'total' => (float) $order->total,
                'item_count' => $order->items->sum('quantity'),
                'customer_address' => $order->customer_address,
                'payment_type' => $order->payment_type ?? 'Belum dipilih',
                'transaction_status' => $order->transaction_status ?? 'pending',
                'payment_payload' => $order->payment_payload ?? [],
                'items' => $order->items->map(fn($item) => [
                    'name' => $item->product?->title ?? 'Produk Dihapus',
                    'quantity' => $item->quantity,
                    'price' => (float) $item->price,
                ])->toArray(),
            ];
        });

        return view('order.history', [
            'judul' => 'Riwayat Order - Lunerburg & Co',
            'orders' => $formattedOrders,
            'user' => $user,
        ]);
    }

    public function detail($orderId)
    {
        $order = Order::with(['items.product', 'shippingAddress'])
            ->where('user_id', Auth::id())
            ->findOrFail($orderId);

        $status = $order->status ?? 'pending';
        $formattedOrder = [
            'id' => $order->id,
            'created_at' => $order->created_at->format('d M Y H:i'),
            'status' => $status,
            'status_label' => $this->paymentStatusLabel($order),
            'badge_class' => $this->paymentStatusBadgeClass($status),
            'total' => (float) $order->total,
            'customer_address' => $order->customer_address,
            'snap_token' => $order->snap_token,
            'transaction_status' => $order->transaction_status ?? $status,
            'payment_status' => $this->paymentStatusLabel($order),
            'payment_type' => $order->payment_type ?? 'Belum dipilih',
            'payment_payload' => $order->payment_payload ?? [],
            'expires_at' => $order->expires_at?->format('d M Y H:i'),
        ];

        $orderItems = $order->items->map(fn($item) => [
            'name' => $item->product?->title ?? 'Produk Dihapus',
            'quantity' => $item->quantity,
            'price' => (float) $item->price,
        ]);

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json($formattedOrder);
        }

        return view('order.detail', [
            'judul' => 'Detail Order #' . substr($order->id, 0, 8),
            'order' => $formattedOrder,
            'order_items' => $orderItems,
        ]);
    }

    public function checkout(Request $request, MidtransService $midtrans)
    {
        $user = Auth::user();

        // 1. Get default address
        $defaultAddress = $user->defaultAddress ?? $user->addresses()->first();
        if (!$defaultAddress) {
            return redirect()->route('user.profile')->with('alert', [
                'type' => 'error',
                'message' => 'Alamat utama belum diatur! Silakan tambahkan alamat di profil Anda.',
            ]);
        }

        $cart = Cart::where('user_id', $user->id)->first();
        if (!$cart) {
            return back()->with('alert', [
                'type' => 'error',
                'message' => 'Keranjang Anda masih kosong.',
            ]);
        }

        $cartItems = CartItem::with(['product.activeDiscounts'])
            ->where('cart_id', $cart->id)
            ->get();

        if ($cartItems->isEmpty()) {
            return back()->with('alert', [
                'type' => 'error',
                'message' => 'Keranjang Anda masih kosong.',
            ]);
        }

        // 2. Validate stock for all items
        foreach ($cartItems as $item) {
            if ($item->product->stock < $item->quantity) {
                return back()->with('alert', [
                    'type' => 'error',
                    'message' => 'Stok produk "' . $item->product->title . '" tidak mencukupi.',
                ]);
            }
        }

        $order = DB::transaction(function () use ($user, $defaultAddress, $cart, $cartItems) {
            $subtotal = 0;
            $itemsData = [];

            foreach ($cartItems as $item) {
            $product = Product::with('activeDiscounts')->lockForUpdate()->findOrFail($item->product_id);
            abort_if(!$product->is_active || $product->stock < $item->quantity, 422, 'Stok produk berubah. Silakan periksa keranjang Anda.');
                $price = (float) $product->price;

                $bestDiscount = $product->activeDiscounts->sortByDesc('percentage')->first();
                if ($bestDiscount) {
                    $price -= ($price * ($bestDiscount->percentage / 100));
                }

                $price = round($price);

                $subtotal += ($price * $item->quantity);

                $itemsData[] = [
                    'product_id' => $product->id,
                    'quantity' => $item->quantity,
                    'price' => $price,
                ];

                // Reduce stock
                $product->decrement('stock', $item->quantity);
            }

            $order = Order::create([
                'user_id' => $user->id,
                'customer_address' => $defaultAddress->address_line_1 . ', ' . $defaultAddress->city . ' ' . $defaultAddress->postal_code,
                'total' => $subtotal,
                'status' => 'pending',
            ]);
            $order->update([
                'midtrans_order_id' => 'LUNER-' . strtoupper(str_replace('-', '', $order->id)),
                'expires_at' => now()->addHours(2),
            ]);

            foreach ($itemsData as $data) {
                $order->items()->create($data);
            }

            ShippingAddress::create([
                'user_id' => $user->id,
                'order_id' => $order->id,
                'address_line_1' => $defaultAddress->address_line_1,
                'address_line_2' => $defaultAddress->address_line_2,
                'city' => $defaultAddress->city,
                'postal_code' => $defaultAddress->postal_code,
                'country' => $defaultAddress->country,
                'phone_number' => $user->phone_number ?? '-',
                'is_default' => true,
            ]);

            // Clear cart
            CartItem::where('cart_id', $cart->id)->delete();

            return $order;
        });

        try {
            $order->load(['user', 'items.product', 'shippingAddress']);
            $order->update(['snap_token' => $midtrans->createSnapToken($order)]);
        } catch (Throwable $exception) {
            Log::error('Midtrans Snap token creation failed.', [
                'order_id' => $order->id,
                'exception' => $exception->getMessage(),
            ]);

            $this->cancelAndRestoreStock($order, 'payment_initialization_failed');

            return redirect()->route('order.detail', $order->id)->with('alert', [
                'type' => 'error',
                'message' => 'Pembayaran belum dapat disiapkan. Silakan coba lagi.',
            ]);
        }

        return redirect()->route('order.detail', $order->id)->with('alert', [
            'type' => 'success',
            'message' => 'Pesanan dibuat. Silakan selesaikan pembayaran Anda.',
        ]);
    }

    public function paymentNotification(Request $request, MidtransService $midtrans)
    {
        $payload = $request->json()->all();

        if (!$payload || !$midtrans->isValidNotification($payload)) {
            Log::warning('Rejected Midtrans notification signature.', ['ip' => $request->ip()]);
            return response()->json(['message' => 'Invalid notification signature.'], 403);
        }

        $order = Order::where('midtrans_order_id', $payload['order_id'] ?? '')->first();
        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        $grossAmount = number_format((float) ($payload['gross_amount'] ?? 0), 2, '.', '');
        $expectedAmount = number_format((float) $order->total, 2, '.', '');
        if ($grossAmount !== $expectedAmount) {
            Log::warning('Rejected Midtrans notification amount mismatch.', [
                'order_id' => $order->id,
                'expected' => $expectedAmount,
                'received' => $grossAmount,
            ]);
            return response()->json(['message' => 'Amount mismatch.'], 422);
        }

        DB::transaction(function () use ($order, $payload) {
            $lockedOrder = Order::lockForUpdate()->findOrFail($order->id);
            $wasCancelled = $lockedOrder->status === 'cancelled';
            $newStatus = $payload['transaction_status'] ?? 'pending';
            $isPaid = in_array($newStatus, ['settlement', 'capture'], true)
                && (($newStatus !== 'capture') || ($payload['fraud_status'] ?? 'accept') === 'accept');
            $isFailed = in_array($newStatus, ['deny', 'cancel', 'expire', 'failure'], true);

            $lockedOrder->forceFill([
                'transaction_status' => $newStatus,
                'payment_type' => $payload['payment_type'] ?? $lockedOrder->payment_type,
                'fraud_status' => $payload['fraud_status'] ?? $lockedOrder->fraud_status,
                'payment_payload' => $payload,
                'status' => $isPaid ? 'paid' : ($isFailed ? 'cancelled' : $lockedOrder->status),
                'paid_at' => $isPaid ? ($lockedOrder->paid_at ?? now()) : $lockedOrder->paid_at,
            ])->save();

            if ($isFailed && !$wasCancelled) {
                foreach ($lockedOrder->items()->lockForUpdate()->get() as $item) {
                    Product::whereKey($item->product_id)->lockForUpdate()->increment('stock', $item->quantity);
                }
            }
        });

        return response()->json(['message' => 'Notification processed.']);
    }

    private function cancelAndRestoreStock(Order $order, string $reason): void
    {
        DB::transaction(function () use ($order, $reason) {
            $lockedOrder = Order::with('items')->lockForUpdate()->find($order->id);
            if (!$lockedOrder || $lockedOrder->status === 'cancelled') {
                return;
            }

            foreach ($lockedOrder->items as $item) {
                Product::whereKey($item->product_id)->lockForUpdate()->increment('stock', $item->quantity);
            }

            $lockedOrder->update([
                'status' => 'cancelled',
                'transaction_status' => $reason,
            ]);
        });
    }

    public function success()
    {
        return view('order.success', [
            'judul' => 'Order Berhasil - Lunerburg & Co',
        ]);
    }
}