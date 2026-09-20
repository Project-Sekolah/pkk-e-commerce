<?php

namespace App\Console\Commands;

use App\Services\OrderExpiryService;
use Illuminate\Console\Command;

class ExpirePendingOrders extends Command
{
    protected $signature = 'orders:expire-pending';
    protected $description = 'Expire pending orders older than their payment window and restore stock';

    public function handle(OrderExpiryService $expiryService): int
    {
        $expired = $expiryService->expirePendingOrders();
        $this->info("Expired {$expired} pending order(s).");

        return self::SUCCESS;
    }
}
