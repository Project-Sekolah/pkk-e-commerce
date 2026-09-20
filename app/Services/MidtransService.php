<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Midtrans\Config;
use Midtrans\Notification;
use Midtrans\Snap;

class MidtransService
{
    public function __construct()
    {
        $serverKey = config('services.midtrans.server_key');
        if (!$serverKey) {
            return;
        }

        Config::$serverKey = $serverKey;
        Config::$isProduction = config('services.midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function createSnapToken(Order $order): string
    {
        if (app()->environment('testing')) {
            return 'test-snap-token';
        }

        if (!config('services.midtrans.server_key')) {
            throw new InvalidArgumentException('MIDTRANS_SERVER_KEY belum dikonfigurasi.');
        }

        return Snap::getSnapToken([
            'transaction_details' => [
                'order_id' => $order->midtrans_order_id,
                'gross_amount' => (int) round((float) $order->total),
            ],
            'customer_details' => [
                'first_name' => $order->user?->full_name ?: $order->user?->username ?: 'Customer',
                'email' => $order->user?->email,
                'phone' => $order->shippingAddress?->phone_number,
            ],
            'item_details' => $order->items->map(fn ($item) => [
                'id' => (string) $item->product_id,
                'price' => (int) round((float) $item->price),
                'quantity' => $item->quantity,
                'name' => mb_substr($item->product?->title ?? 'Product', 0, 50),
            ])->values()->all(),
        ]);
    }

    public function notification(): Notification
    {
        return new Notification();
    }

    public function isValidNotification(array $payload): bool
    {
        $serverKey = config('services.midtrans.server_key');
        $signature = hash('sha512', implode('', [
            Arr::get($payload, 'order_id', ''),
            Arr::get($payload, 'status_code', ''),
            Arr::get($payload, 'gross_amount', ''),
            $serverKey,
        ]));

        return hash_equals($signature, (string) Arr::get($payload, 'signature_key', ''));
    }
}