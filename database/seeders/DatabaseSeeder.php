<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Discount;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductRating;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Users
        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'full_name' => 'Administrator Lunerburg',
                'email' => 'admin@lunerburg.com',
                'password' => Hash::make('password123'),
                'phone_number' => '081234567890',
                'role' => 'admin',
                'is_active' => true,
                'is_blocked' => false,
            ]
        );

        $seller = User::firstOrCreate(
            ['username' => 'seller'],
            [
                'full_name' => 'Seller Official Store',
                'email' => 'seller@lunerburg.com',
                'password' => Hash::make('password123'),
                'phone_number' => '081298765432',
                'role' => 'seller',
                'is_active' => true,
                'is_blocked' => false,
            ]
        );

        $buyer = User::firstOrCreate(
            ['username' => 'buyer'],
            [
                'full_name' => 'Customer Setia',
                'email' => 'buyer@lunerburg.com',
                'password' => Hash::make('password123'),
                'phone_number' => '081311223344',
                'role' => 'buyer',
                'is_active' => true,
                'is_blocked' => false,
            ]
        );

        // Buyer default address
        UserAddress::firstOrCreate(
            ['user_id' => $buyer->id, 'is_default' => true],
            [
                'label' => 'Rumah',
                'address_line_1' => 'Jl. Merdeka No. 45, Kebayoran Baru',
                'address_line_2' => 'Blok B3 No. 12',
                'city' => 'Jakarta Selatan',
                'postal_code' => '12160',
                'country' => 'Indonesia',
                'is_default' => true,
            ]
        );

        // 2. Categories
        $categoriesData = [
            ['name' => 'Baju Pria', 'slug' => 'baju-pria'],
            ['name' => 'Baju Wanita', 'slug' => 'baju-wanita'],
            ['name' => 'Jaket & Luaran', 'slug' => 'jaket-luaran'],
            ['name' => 'Aksesoris', 'slug' => 'aksesoris'],
            ['name' => 'Sepatu', 'slug' => 'sepatu'],
        ];

        $categories = [];
        foreach ($categoriesData as $c) {
            $categories[$c['slug']] = Category::firstOrCreate(
                ['slug' => $c['slug']],
                ['name' => $c['name'], 'is_active' => true]
            );
        }

        // 3. Discounts
        $discount10 = Discount::firstOrCreate(
            ['name' => 'DISKON10', 'user_id' => $seller->id],
            [
                'percentage' => 10,
                'start_date' => now()->subDays(5),
                'end_date' => now()->addDays(30),
                'is_active' => true,
            ]
        );

        $discount20 = Discount::firstOrCreate(
            ['name' => 'PROMO20', 'user_id' => $seller->id],
            [
                'percentage' => 20,
                'start_date' => now()->subDays(2),
                'end_date' => now()->addDays(20),
                'is_active' => true,
            ]
        );

        // 4. Products
        $productsData = [
            [
                'title' => 'Kemeja Linen Vintage Sand',
                'slug' => 'kemeja-linen-vintage-sand',
                'category_slug' => 'baju-pria',
                'price' => 189000,
                'stock' => 25,
                'gender' => 'pria',
                'description' => 'Kemeja linen premium dengan warna netral yang bernapas dan nyaman untuk segala cuaca.',
                'image' => 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=600&auto=format&fit=crop&q=80',
            ],
            [
                'title' => 'Floral Summer Breeze Dress',
                'slug' => 'floral-summer-breeze-dress',
                'category_slug' => 'baju-wanita',
                'price' => 245000,
                'stock' => 18,
                'gender' => 'wanita',
                'description' => 'Gaun anggun bermotif bunga lembut dengan siluet anggun untuk acara santai maupun formal.',
                'image' => 'https://images.unsplash.com/photo-1572804013309-59a88b7e92f1?w=600&auto=format&fit=crop&q=80',
            ],
            [
                'title' => 'Classic Corduroy Oversized Jacket',
                'slug' => 'classic-corduroy-oversized-jacket',
                'category_slug' => 'jaket-luaran',
                'price' => 320000,
                'stock' => 12,
                'gender' => 'all',
                'description' => 'Jaket korduroi hangat bergaya Skandinavia dengan jahitan kuat dan detail kancing eksklusif.',
                'image' => 'https://images.unsplash.com/photo-1551028719-00167b16eac5?w=600&auto=format&fit=crop&q=80',
            ],
            [
                'title' => 'Minimalist Canvas Tote Bag',
                'slug' => 'minimalist-canvas-tote-bag',
                'category_slug' => 'aksesoris',
                'price' => 95000,
                'stock' => 40,
                'gender' => 'all',
                'description' => 'Tas kanvas tebal ramah lingkungan dengan saku internal untuk laptop dan barang sehari-hari.',
                'image' => 'https://images.unsplash.com/photo-1544816155-12df9643f363?w=600&auto=format&fit=crop&q=80',
            ],
            [
                'title' => 'Retro Leather White Sneakers',
                'slug' => 'retro-leather-white-sneakers',
                'category_slug' => 'sepatu',
                'price' => 380000,
                'stock' => 15,
                'gender' => 'all',
                'description' => 'Sepatu kasual dengan bantalan empuk dan siluet retro yang tak lekang oleh waktu.',
                'image' => 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=600&auto=format&fit=crop&q=80',
            ],
            [
                'title' => 'Oversized Waffle Knit Sweater',
                'slug' => 'oversized-waffle-knit-sweater',
                'category_slug' => 'jaket-luaran',
                'price' => 210000,
                'stock' => 20,
                'gender' => 'all',
                'description' => 'Sweater rajut motif waffle yang sejuk di kulit dan memberikan kesan santai namun tetap rapi.',
                'image' => 'https://images.unsplash.com/photo-1583743814966-8936f5b7be1a?w=600&auto=format&fit=crop&q=80',
            ],
        ];

        foreach ($productsData as $pData) {
            $cat = $categories[$pData['category_slug']] ?? null;
            if (!$cat) continue;

            $product = Product::firstOrCreate(
                ['slug' => $pData['slug']],
                [
                    'category_id' => $cat->id,
                    'user_id' => $seller->id,
                    'title' => $pData['title'],
                    'price' => $pData['price'],
                    'stock' => $pData['stock'],
                    'gender' => $pData['gender'],
                    'description' => $pData['description'],
                    'is_active' => true,
                ]
            );

            ProductImage::firstOrCreate(
                ['product_id' => $product->id],
                ['image_url' => $pData['image']]
            );

            // Add sample rating
            ProductRating::firstOrCreate(
                ['user_id' => $buyer->id, 'product_id' => $product->id],
                [
                    'rating' => 5,
                    'review_text' => 'Kualitas bahan sangat bagus dan jahitan rapi sekali! Pengiriman cepat.',
                ]
            );

            // Attach discount to some products
            if ($pData['price'] > 200000) {
                $product->discounts()->syncWithoutDetaching([
                    $discount10->id => [
                        'id' => (string) Str::uuid(),
                        'start_date' => $discount10->start_date,
                        'end_date' => $discount10->end_date,
                        'is_active' => true,
                    ]
                ]);
            }
        }
    }
}