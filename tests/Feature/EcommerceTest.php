<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductRating;
use App\Models\User;
use App\Models\UserAddress;
use Database\Seeders\DatabaseSeeder;
use App\Services\OrderExpiryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EcommerceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_home_page_returns_successful_response(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Lunerburg & Co');
    }

    public function test_products_catalog_returns_successful_response(): void
    {
        $response = $this->get('/product');
        $response->assertStatus(200);
        $response->assertSee('Koleksi Produk');
    }

    public function test_user_can_login_successfully(): void
    {
        $response = $this->post('/user/login', [
            'username' => 'buyer',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticated();
    }

    public function test_user_can_register_new_account(): void
    {
        $response = $this->post('/user/register', [
            'username' => 'newuser',
            'full_name' => 'Pengguna Baru',
            'email' => 'newuser@example.com',
            'phone_number' => '08123456789',
            'password' => 'secret123',
            'confirm_password' => 'secret123',
            'role' => 'buyer',
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('users', [
            'username' => 'newuser',
            'email' => 'newuser@example.com',
        ]);
    }

    public function test_authenticated_user_can_add_item_to_cart_and_get_cart(): void
    {
        $user = User::where('username', 'buyer')->first();
        $product = Product::first();

        $this->actingAs($user);

        $addResponse = $this->postJson('/cart/addItem', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $addResponse->assertStatus(200);
        $addResponse->assertJson(['success' => true]);

        $getResponse = $this->getJson('/cart/getCart');
        $getResponse->assertStatus(200);
        $getResponse->assertJsonStructure([
            'items' => [
                '*' => ['item_id', 'product_id', 'quantity', 'title', 'price'],
            ]
        ]);
    }

    public function test_authenticated_user_can_checkout(): void
    {
        $user = User::where('username', 'buyer')->first();
        $product = Product::first();

        $this->actingAs($user);

        // Add to cart
        $this->postJson('/cart/addItem', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        // Checkout
        $response = $this->post('/order/checkout');
        $response->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'status' => 'pending',
            'snap_token' => 'test-snap-token',
        ]);
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::where('username', 'admin')->first();

        $response = $this->actingAs($admin)->get('/adminDashboard');
        $response->assertStatus(200);
        $response->assertSee('Dashboard Admin');
    }

    public function test_seller_can_access_own_products_page(): void
    {
        $seller = User::where('username', 'seller')->first();

        $response = $this->actingAs($seller)->get('/product/seller');

        $response->assertStatus(200);
        $response->assertSee('Kelola Produk Saya');
    }

    public function test_seller_can_view_paid_purchase_history_for_own_product(): void
    {
        $seller = User::where('username', 'seller')->first();
        $buyer = User::where('username', 'buyer')->first();
        $product = Product::where('user_id', $seller->id)->firstOrFail();

        $order = \App\Models\Order::create([
            'user_id' => $buyer->id,
            'customer_address' => 'Jl. Test No. 1, Bandung 40111',
            'total' => 150000,
            'status' => 'paid',
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 75000,
        ]);

        $response = $this->actingAs($seller)->get('/product/seller/purchase-history');

        $response->assertOk();
        $response->assertSee($product->title);
        $response->assertSee($buyer->full_name);
        $response->assertSee('Dibayar');
    }

    public function test_order_detail_ajax_returns_payment_metadata(): void
    {
        $user = User::where('username', 'buyer')->first();
        $product = Product::first();

        $order = \App\Models\Order::create([
            'user_id' => $user->id,
            'customer_address' => 'Jl. Test No. 1, Bandung 40111',
            'total' => 150000,
            'status' => 'pending',
            'midtrans_order_id' => 'LUNER-TEST123',
            'snap_token' => 'test-snap-token',
            'transaction_status' => 'pending',
            'payment_type' => 'bank_transfer',
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 150000,
        ]);

        $response = $this->actingAs($user)->getJson('/order/detail/' . $order->id, ['Accept' => 'application/json']);

        $response->assertOk();
        $response->assertJsonPath('status', 'pending');
        $response->assertJsonPath('payment_status', 'Menunggu Pembayaran');
        $response->assertJsonPath('transaction_status', 'pending');
        $response->assertJsonPath('payment_type', 'bank_transfer');
    }

    public function test_settlement_transaction_is_displayed_as_completed(): void
    {
        $buyer = User::where('username', 'buyer')->first();
        $product = Product::first();
        $order = \App\Models\Order::create([
            'user_id' => $buyer->id,
            'customer_address' => 'Jl. Settlement No. 1',
            'total' => 150000,
            'status' => 'pending',
            'transaction_status' => 'settlement',
        ]);
        $order->items()->create(['product_id' => $product->id, 'quantity' => 1, 'price' => 150000]);

        $response = $this->actingAs($buyer)->getJson('/order/detail/' . $order->id);

        $response->assertOk();
        $response->assertJsonPath('status', 'completed');
        $response->assertJsonPath('payment_status', 'Selesai');
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'completed']);
    }

    public function test_authenticated_user_can_edit_own_product_comment(): void
    {
        $buyer = User::where('username', 'buyer')->first();
        $product = Product::first();
        ProductRating::where('user_id', $buyer->id)->where('product_id', $product->id)->delete();
        $rating = ProductRating::create([
            'user_id' => $buyer->id,
            'product_id' => $product->id,
            'rating' => 4,
            'review_text' => 'Komentar lama',
        ]);

        $response = $this->actingAs($buyer)->patchJson('/product/rating/' . $rating->id, [
            'rating' => 5,
            'review_text' => 'Komentar baru dengan tanda kutip: "bagus"',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('product_ratings', [
            'id' => $rating->id,
            'rating' => 5,
            'review_text' => 'Komentar baru dengan tanda kutip: "bagus"',
        ]);
    }

    public function test_pending_order_older_than_payment_window_expires_and_restores_stock(): void
    {
        $buyer = User::where('username', 'buyer')->first();
        $product = Product::first();
        $stockBefore = $product->stock;

        $order = \App\Models\Order::create([
            'user_id' => $buyer->id,
            'customer_address' => 'Jl. Expired No. 1',
            'total' => 150000,
            'status' => 'pending',
            'expires_at' => now()->subMinute(),
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 75000,
        ]);
        $product->decrement('stock', 2);

        $expired = app(OrderExpiryService::class)->expirePendingOrders();

        $this->assertSame(1, $expired);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'cancelled',
            'transaction_status' => 'expire',
        ]);
        $this->assertSame($stockBefore, Product::find($product->id)->stock);
    }

    public function test_authenticated_user_can_submit_product_rating(): void
    {
        $buyer = User::where('username', 'buyer')->first();
        $product = Product::first();

        $response = $this->actingAs($buyer)->post('/product/addRating', [
            'product_id' => $product->id,
            'rating' => 5,
            'review_text' => 'Produk sesuai dan kualitasnya bagus.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('product_ratings', [
            'user_id' => $buyer->id,
            'product_id' => $product->id,
            'rating' => 5,
        ]);
    }

    public function test_buyer_can_become_seller_after_required_verification(): void
    {
        $buyer = User::where('username', 'buyer')->first();
        $buyer->update(['image' => 'https://example.com/profile.jpg']);

        $response = $this->actingAs($buyer)->post('/user/become-seller', [
            'current_password' => 'password123',
            'seller_agreement' => '1',
            'shop_name' => 'Toko Buyer Test',
            'shop_address' => 'Jl. Seller Test No. 1',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $buyer->id,
            'role' => 'seller',
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $buyer->id,
            'shop_name' => 'Toko Buyer Test',
            'shop_address' => 'Jl. Seller Test No. 1',
        ]);
    }
}