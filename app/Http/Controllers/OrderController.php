<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ShippingAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function history()
    {
        $user = Auth::user();
        $orders = Order::with(['items.product', 'shippingAddress'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $formattedOrders = $orders->map(function ($order) {
            return [
                'id' => $order->id,
                'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                'status' => $order->status,
                'total' => (float) $order->total,
                'item_count' => $order->items->sum('quantity'),
                'customer_address' => $order->customer_address,
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

        $formattedOrder = [
            'id' => $order->id,
            'created_at' => $order->created_at->format('d M Y H:i'),
            'status' => $order->status,
            'total' => (float) $order->total,
            'customer_address' => $order->customer_address,
        ];

        $orderItems = $order->items->map(fn($item) => [
            'name' => $item->product?->title ?? 'Produk Dihapus',
            'quantity' => $item->quantity,
            'price' => (float) $item->price,
        ]);

        return view('order.detail', [
            'judul' => 'Detail Order #' . substr($order->id, 0, 8),
            'order' => $formattedOrder,
            'order_items' => $orderItems,
        ]);
    }

    public function checkout(Request $request)
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
                $product = $item->product;
                $price = (float) $product->price;

                $bestDiscount = $product->activeDiscounts->sortByDesc('percentage')->first();
                if ($bestDiscount) {
                    $price -= ($price * ($bestDiscount->percentage / 100));
                }

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
                'status' => 'paid',
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

        return redirect()->route('order.detail', $order->id)->with('alert', [
            'type' => 'success',
            'message' => 'Pesanan berhasil dibuat dan telah diproses!',
        ]);
    }

    public function success()
    {
        return view('order.success', [
            'judul' => 'Order Berhasil - Lunerburg & Co',
        ]);
    }
}