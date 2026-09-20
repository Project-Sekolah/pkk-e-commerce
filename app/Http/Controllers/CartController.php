<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Discount;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    protected function getOrCreateUserCart(): Cart
    {
        return Cart::firstOrCreate(['user_id' => Auth::id()]);
    }

    public function getCart()
    {
        $cart = $this->getOrCreateUserCart();
        $items = CartItem::with(['product.images', 'product.activeDiscounts'])
            ->where('cart_id', $cart->id)
            ->get();

        $formatted = $items->map(function ($item) {
            $product = $item->product;
            $bestDiscount = $product->activeDiscounts->sortByDesc('percentage')->first();

            return [
                'item_id' => $item->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'title' => $product->title,
                'price' => (float) $product->price,
                'image' => $product->first_image_url,
                'discount_name' => $bestDiscount?->name,
                'discount_percentage' => $bestDiscount ? (float) $bestDiscount->percentage : 0,
            ];
        });

        return response()->json(['items' => $formatted]);
    }

    public function addItem(Request $request)
    {
        $productId = $request->input('product_id');
        $quantity = (int) $request->input('quantity', 1);

        if (!$productId || $quantity <= 0) {
            return response()->json(['error' => 'Invalid product_id or quantity'], 400);
        }

        $product = Product::where('is_active', true)->findOrFail($productId);

        if ($product->stock < $quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Stok produk tidak mencukupi (tersisa: ' . $product->stock . ').',
            ], 400);
        }

        $cart = $this->getOrCreateUserCart();

        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $productId)
            ->first();

        if ($cartItem) {
            if ($product->stock < ($cartItem->quantity + $quantity)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jumlah melebihi stok yang tersedia.',
                ], 400);
            }
            $cartItem->increment('quantity', $quantity);
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $productId,
                'quantity' => $quantity,
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Produk berhasil ditambahkan ke keranjang.']);
    }

    public function increaseItem(Request $request)
    {
        $itemId = $request->validate(['item_id' => ['required', 'uuid']])['item_id'];
        $cart = $this->getOrCreateUserCart();
        $item = CartItem::where('cart_id', $cart->id)->findOrFail($itemId);

        if ($item->product->stock < ($item->quantity + 1)) {
            return response()->json([
                'success' => false,
                'message' => 'Stok tidak mencukupi.',
            ], 400);
        }

        $item->increment('quantity');
        return response()->json(['success' => true]);
    }

    public function decreaseItem(Request $request)
    {
        $itemId = $request->validate(['item_id' => ['required', 'uuid']])['item_id'];
        $cart = $this->getOrCreateUserCart();
        $item = CartItem::where('cart_id', $cart->id)->findOrFail($itemId);

        if ($item->quantity > 1) {
            $item->decrement('quantity');
        } else {
            $item->delete();
        }

        return response()->json(['success' => true]);
    }

    public function deleteItem(Request $request)
    {
        $itemId = $request->validate(['item_id' => ['required', 'uuid']])['item_id'];
        $cart = $this->getOrCreateUserCart();
        CartItem::where('cart_id', $cart->id)->where('id', $itemId)->delete();

        return response()->json(['success' => true]);
    }

    public function clearCart()
    {
        $cart = $this->getOrCreateUserCart();
        CartItem::where('cart_id', $cart->id)->delete();

        return response()->json(['success' => true]);
    }

    public function validateDiscount(Request $request)
    {
        $name = trim($request->input('discount_name', ''));

        if (empty($name)) {
            return response()->json(['error' => 'Nama diskon harus diisi'], 400);
        }

        $now = now();
        $discount = Discount::where('name', $name)
            ->where('is_active', true)
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->first();

        if (!$discount) {
            return response()->json([
                'valid' => false,
                'message' => 'Diskon tidak valid atau telah kedaluwarsa.',
            ], 404);
        }

        return response()->json([
            'valid' => true,
            'discount' => [
                'id' => $discount->id,
                'name' => $discount->name,
                'percentage' => (float) $discount->percentage,
            ],
            'applicable_products' => $discount->products()->where('products.is_active', true)->pluck('products.id')->values(),
        ]);
    }
}