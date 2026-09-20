<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('product_images', 'is_primary')) {
            Schema::table('product_images', function (Blueprint $table) {
                $table->boolean('is_primary')->default(false)->after('image_url');
            });
        }

        DB::table('products')->orderBy('id')->each(function ($product) {
            $firstImage = DB::table('product_images')
                ->where('product_id', $product->id)
                ->orderBy('created_at')
                ->first();

            if ($firstImage) {
                DB::table('product_images')->where('id', $firstImage->id)->update(['is_primary' => true]);
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('product_images', 'is_primary')) {
            Schema::table('product_images', function (Blueprint $table) {
                $table->dropColumn('is_primary');
            });
        }
    }
};
