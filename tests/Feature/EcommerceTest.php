<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\UserAddress;
use Database\Seeders\DatabaseSeeder;
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
            'status' => 'paid',
        ]);
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::where('username', 'admin')->first();

        $response = $this->actingAs($admin)->get('/adminDashboard');
        $response->assertStatus(200);
        $response->assertSee('Dashboard Admin');
    }
}