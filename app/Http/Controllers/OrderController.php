<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Discount;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ShippingAddress;
use App\Services\MidtransService;
use App\Services\OrderExpiryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class OrderController extends Controller
{
    private function syncPaymentStatus(Order $order, MidtransService $midtrans): Order
    {
        if ($order->status === 'pending') {
            $remoteStatus = $midtrans->fetchTransactionStatus($order);
            if ($remoteStatus) {
                $order->forceFill([
                    'transaction_status' => $remoteStatus['transaction_status'] ?? $order->transaction_status,
                    'payment_type' => $remoteStatus['payment_type'] ?? $order->payment_type,
                    'fraud_status' => $remoteStatus['fraud_status'] ?? $order->fraud_status,
                    'payment_payload' => $remoteStatus,
                ])->save();
            }
        }

        $transactionStatus = strtolower((string) $order->transaction_status);
        $nextStatus = match ($transactionStatus) {
            'settlement', 'capture' => 'completed',
            'deny', 'cancel', 'expire', 'failure' => 'cancelled',
            default => $order->status,
        };

        if ($nextStatus !== $order->status && $nextStatus !== null) {
            $order->update([
                'status' => $nextStatus,
                'paid_at' => $nextStatus === 'completed' ? ($order->paid_at ?? now()) : $order->paid_at,
            ]);
        }

        return $order->refresh();
    }

    private function paymentStatusLabel(Order $order): string
    {
        return match ($order->status ?? 'pending') {
            'pending' => 'Menunggu Pembayaran',
            'paid' => 'Selesai',
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

    public function history(OrderExpiryService $expiryService, MidtransService $midtrans)
    {
        $expiryService->expirePendingOrders();
        $user = Auth::user();
        $orders = Order::with(['items.product.user', 'items.product.images', 'shippingAddress'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $formattedOrders = $orders->map(function ($order) use ($midtrans) {
            $order = $this->syncPaymentStatus($order, $midtrans);
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
                    'product_id' => $item->product_id,
                    'name' => $item->product?->title ?? 'Produk Dihapus',
                    'quantity' => $item->quantity,
                    'price' => (float) $item->price,
                    'image' => $item->product?->first_image_url,
                    'store_name' => $item->product?->user?->shop_name ?? $item->product?->user?->full_name ?? 'Toko',
                    'product_url' => $item->product ? route('products.show', $item->product_id) : null,
                ])->toArray(),
            ];
        });

        return view('order.history', [
            'judul' => 'Riwayat Order - Lunerburg & Co',
            'orders' => $formattedOrders,
            'user' => $user,
        ]);
    }

    public function detail($orderId, OrderExpiryService $expiryService, MidtransService $midtrans)
    {
        $expiryService->expirePendingOrders();
        $order = Order::with(['items.product.user', 'items.product.images', 'shippingAddress'])
            ->where('user_id', Auth::id())
            ->findOrFail($orderId);
        $order = $this->syncPaymentStatus($order, $midtrans);

        $status = $order->status ?? 'pending';
        $formattedOrder = [
            'id' => $order->id,
            'created_at' => $order->created_at->format('d M Y H:i'),
            'status' => $status,
            'status_label' => $this->paymentStatusLabel($order),
            'badge_class' => $this->paymentStatusBadgeClass($status),
            'total' => (float) $order->total,
            'courier' => $order->courier ?? 'Belum dipilih',
            'shipping_fee' => (float) ($order->shipping_fee ?? 0),
            'customer_address' => $order->customer_address,
            'snap_token' => $order->snap_token,
            'transaction_status' => $order->transaction_status ?? $status,
            'payment_status' => $this->paymentStatusLabel($order),
            'payment_type' => $order->payment_type ?? 'Belum dipilih',
            'payment_payload' => $order->payment_payload ?? [],
            'expires_at' => $order->expires_at?->format('d M Y H:i'),
            'items' => $order->items->map(fn ($item) => [
                'name' => $item->product?->title ?? 'Produk Dihapus',
                'quantity' => $item->quantity,
                'price' => (float) $item->price,
                'store_name' => $item->product?->user?->shop_name ?? $item->product?->user?->full_name ?? 'Toko',
            ])->values(),
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
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $courierRates = [
            'jne_reg' => ['label' => 'JNE Reguler', 'fee' => 15000],
            'jnt_reg' => ['label' => 'J&T Reguler', 'fee' => 14000],
            'sicepat_reg' => ['label' => 'SiCepat Reguler', 'fee' => 16000],
        ];
        $validated = $request->validate([
            'address_id' => ['nullable', 'uuid'],
            'courier' => ['nullable', 'in:' . implode(',', array_keys($courierRates))],
            'discount_name' => ['nullable', 'string', 'max:100'],
        ]);

        // 1. Get the selected address owned by the current user.
        $defaultAddress = !empty($validated['address_id'])
            ? $user->addresses()->findOrFail($validated['address_id'])
            : ($user->defaultAddress ?? $user->addresses()->first());
        $selectedCourier = $validated['courier'] ?? 'jne_reg';
        $shippingFee = $courierRates[$selectedCourier]['fee'];
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

        $promoDiscount = null;
        if (!empty($validated['discount_name'])) {
            $promoDiscount = Discount::with('products')
                ->where('name', trim($validated['discount_name']))
                ->where('is_active', true)
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->first();
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

        $order = DB::transaction(function () use ($user, $defaultAddress, $cart, $cartItems, $selectedCourier, $shippingFee, $courierRates, $promoDiscount) {
            $subtotal = 0;
            $itemsData = [];

            foreach ($cartItems as $item) {
            $product = Product::with('activeDiscounts')->lockForUpdate()->findOrFail($item->product_id);
            abort_if(!$product->is_active || $product->stock < $item->quantity, 422, 'Stok produk berubah. Silakan periksa keranjang Anda.');
                $price = (float) $product->price;

                $bestDiscount = $product->activeDiscounts->sortByDesc('percentage')->first();
                $discountPercentage = $bestDiscount ? (float) $bestDiscount->percentage : 0;
                if ($promoDiscount && $promoDiscount->products->contains('id', $product->id)) {
                    $discountPercentage = max($discountPercentage, (float) $promoDiscount->percentage);
                }
                if ($discountPercentage > 0) {
                    $price -= ($price * ($discountPercentage / 100));
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
                'total' => $subtotal + $shippingFee,
                'courier' => $courierRates[$selectedCourier]['label'],
                'shipping_fee' => $shippingFee,
                'status' => 'pending',
            ]);
            $order->update([
                'midtrans_order_id' => 'LUNER-' . strtoupper(str_replace('-', '', $order->id)),
                'expires_at' => now()->addHours(24),
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
                'is_default' => false,
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
            $canRestoreStock = $lockedOrder->status === 'pending';
            $newStatus = $payload['transaction_status'] ?? 'pending';
            $isPaid = in_array($newStatus, ['settlement', 'capture'], true)
                && (($newStatus !== 'capture') || ($payload['fraud_status'] ?? 'accept') === 'accept');
            $isFailed = in_array($newStatus, ['deny', 'cancel', 'expire', 'failure'], true);

            $lockedOrder->forceFill([
                'transaction_status' => $newStatus,
                'payment_type' => $payload['payment_type'] ?? $lockedOrder->payment_type,
                'fraud_status' => $payload['fraud_status'] ?? $lockedOrder->fraud_status,
                'payment_payload' => $payload,
                'status' => $isPaid ? 'completed' : ($isFailed ? 'cancelled' : $lockedOrder->status),
                'paid_at' => $isPaid ? ($lockedOrder->paid_at ?? now()) : $lockedOrder->paid_at,
            ])->save();

            if ($isFailed && !$wasCancelled && $canRestoreStock) {
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