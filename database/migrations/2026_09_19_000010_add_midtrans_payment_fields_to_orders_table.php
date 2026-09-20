<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('midtrans_order_id')->nullable()->unique()->after('id');
            $table->string('snap_token')->nullable()->after('midtrans_order_id');
            $table->string('payment_type')->nullable()->after('snap_token');
            $table->string('transaction_status')->nullable()->after('payment_type');
            $table->string('fraud_status')->nullable()->after('transaction_status');
            $table->json('payment_payload')->nullable()->after('fraud_status');
            $table->timestamp('paid_at')->nullable()->after('payment_payload');
            $table->timestamp('expires_at')->nullable()->after('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['midtrans_order_id']);
            $table->dropColumn([
                'midtrans_order_id',
                'snap_token',
                'payment_type',
                'transaction_status',
                'fraud_status',
                'payment_payload',
                'paid_at',
                'expires_at',
            ]);
        });
    }
};