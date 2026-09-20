<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderExpiryService
{
    public function expirePendingOrders(): int
    {
        $orders = Order::with('items')
            ->where('status', 'pending')
            ->where(function ($query) {
                $query->whereNotNull('expires_at')->where('expires_at', '<=', now())
                    ->orWhere(function ($nested) {
                        $nested->whereNull('expires_at')->where('created_at', '<=', now()->subHours(24));
                    });
            })
            ->lock('for update')
            ->get();

        $expired = 0;
        foreach ($orders as $order) {
            DB::transaction(function () use ($order) {
                $lockedOrder = Order::with('items')->lockForUpdate()->find($order->id);
                if (!$lockedOrder || $lockedOrder->status !== 'pending') {
                    return;
                }

                foreach ($lockedOrder->items as $item) {
                    Product::whereKey($item->product_id)
                        ->lockForUpdate()
                        ->increment('stock', $item->quantity);
                }

                $lockedOrder->update([
                    'status' => 'cancelled',
                    'transaction_status' => 'expire',
                    'payment_payload' => array_merge($lockedOrder->payment_payload ?? [], [
                        'expired_by_system' => true,
                        'expired_at' => now()->toIso8601String(),
                    ]),
                ]);
            });
            $expired++;
        }

        return $expired;
    }
}
