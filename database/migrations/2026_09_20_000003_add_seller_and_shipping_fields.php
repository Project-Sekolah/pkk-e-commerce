<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('shop_name')->nullable()->after('role');
            $table->text('shop_address')->nullable()->after('shop_name');
            $table->string('shop_document')->nullable()->after('shop_address');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('courier')->nullable()->after('total');
            $table->decimal('shipping_fee', 12, 2)->default(0)->after('courier');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['courier', 'shipping_fee']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['shop_name', 'shop_address', 'shop_document']);
        });
    }
};
