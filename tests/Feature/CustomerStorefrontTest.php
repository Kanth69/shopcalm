<?php

namespace Tests\Feature;

use App\Enums\CouponType;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Pincode;
use App\Models\Product;
use App\Models\User;
use App\Models\Wallet;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerStorefrontTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected Category $category;
    protected Brand $brand;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'mobile_number' => '9876543210',
            'status' => 'Active',
        ]);

        $this->category = Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'status' => 'Active',
        ]);

        $this->brand = Brand::create([
            'name' => 'ShopCalm Brand',
            'slug' => 'shopcalm-brand',
            'category_id' => $this->category->id,
            'status' => true,
        ]);

        $this->product = Product::create([
            'name' => 'Wireless Bluetooth Earbuds',
            'slug' => 'wireless-bluetooth-earbuds',
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
            'sku' => 'EARBUDS-001',
            'price' => 1500.00,
            'stock' => 50,
            'status' => 'Active',
            'is_approved' => true,
            'short_description' => 'High quality wireless sound.',
            'description' => 'Full detailed description of bluetooth earbuds.',
        ]);

        Pincode::create([
            'pincode' => '560001',
            'is_serviceable' => true,
            'is_cod_available' => true,
            'state' => 'Karnataka',
            'city' => 'Bengaluru',
            'district' => 'Bengaluru',
        ]);
    }

    /** 1. Homepage & Catalog Tests */
    public function test_customer_can_access_homepage(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_customer_can_view_product_detail_page(): void
    {
        $response = $this->get(route('product.show', $this->product->slug));
        $response->assertStatus(200);
        $response->assertSee($this->product->name);
    }

    /** 2. Cart Operations Tests */
    public function test_customer_can_add_product_to_cart(): void
    {
        $response = $this->actingAs($this->customer)->post('/cart/add', [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response->assertStatus(302);
    }

    public function test_customer_can_view_cart(): void
    {
        $this->actingAs($this->customer)->post('/cart/add', [
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($this->customer)->get('/cart');
        $response->assertStatus(200);
        $response->assertSee($this->product->name);
    }

    /** 3. Checkout Page Accessibility */
    public function test_customer_can_access_checkout_page_with_cart_items(): void
    {
        // Add item to cart
        $this->actingAs($this->customer)->post('/cart/add', [
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        // Save delivery address
        $this->customer->addresses()->create([
            'name' => 'Test Customer',
            'phone' => '9876543210',
            'address' => '123 MG Road',
            'city' => 'Bengaluru',
            'state' => 'Karnataka',
            'zip' => '560001',
            'country' => 'India',
        ]);

        $response = $this->actingAs($this->customer)->get('/checkout');
        $response->assertStatus(200);
        $response->assertSee('Select Delivery Address');
        $response->assertSee('Price Details');
    }

    /** 4. Coupon Application Tests */
    public function test_customer_can_apply_and_remove_coupon(): void
    {
        $coupon = Coupon::create([
            'name' => 'Welcome Discount',
            'code' => 'WELCOME50',
            'discount_type' => CouponType::FLAT->value,
            'discount_value' => 500,
            'min_order_amount' => 1000,
            'is_active' => true,
        ]);

        $this->actingAs($this->customer)->post('/cart/add', [
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        // Apply coupon
        $applyRes = $this->actingAs($this->customer)->postJson('/checkout/apply-coupon', [
            'coupon_code' => 'WELCOME50',
        ]);

        $applyRes->assertStatus(200);
        $applyRes->assertJson([
            'success' => true,
            'coupon_code' => 'WELCOME50',
            'discount_amount' => 500,
        ]);

        // Remove coupon
        $removeRes = $this->actingAs($this->customer)->postJson('/checkout/remove-coupon');
        $removeRes->assertStatus(200);
        $removeRes->assertJson(['success' => true]);
    }

    /** 5. Wallet Integration Tests */
    public function test_customer_can_toggle_wallet_on_checkout(): void
    {
        $wallet = app(WalletService::class)->getOrCreateWallet($this->customer);
        $wallet->update(['balance' => 1000.00]);

        $this->actingAs($this->customer)->post('/cart/add', [
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($this->customer)->postJson('/checkout/toggle-wallet', [
            'use_wallet' => true,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'use_wallet' => true,
            'wallet_discount_raw' => 1000,
            'grand_total_raw' => 500, // 1500 - 1000 = 500
        ]);
    }

    /** 6. 100% Wallet Paid Order Flow Test */
    public function test_customer_can_place_order_100_percent_covered_by_wallet(): void
    {
        // Give customer 2000 balance (more than product price 1500)
        $wallet = app(WalletService::class)->getOrCreateWallet($this->customer);
        $wallet->update(['balance' => 2000.00]);

        $this->actingAs($this->customer)->post('/cart/add', [
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $orderPayload = [
            'use_wallet' => '1',
            'payment_method' => 'wallet',
            'shipping_name' => 'Test Customer',
            'shipping_email' => 'customer@example.com',
            'shipping_phone' => '9876543210',
            'shipping_address' => '123 MG Road',
            'shipping_city' => 'Bengaluru',
            'shipping_state' => 'Karnataka',
            'shipping_zip' => '560001',
            'shipping_country' => 'India',
            'terms_consent' => 'on',
        ];

        $response = $this->actingAs($this->customer)->postJson(route('checkout.place-order'), $orderPayload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'payment_mode' => 'wallet',
        ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->customer->id,
            'payment_method' => 'wallet',
            'payment_status' => 'paid',
            'status' => 'confirmed',
            'wallet_amount_used' => 1500.00,
            'total_amount' => 0.00,
        ]);

        // Wallet balance should now be 500 (2000 - 1500)
        $this->assertEquals(500.00, $wallet->fresh()->balance);
    }

    /** 7. Standard Cash on Delivery Order Flow Test */
    public function test_customer_can_place_cod_order(): void
    {
        $this->actingAs($this->customer)->post('/cart/add', [
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $orderPayload = [
            'payment_method' => 'cod',
            'shipping_name' => 'Test Customer',
            'shipping_email' => 'customer@example.com',
            'shipping_phone' => '9876543210',
            'shipping_address' => '123 MG Road',
            'shipping_city' => 'Bengaluru',
            'shipping_state' => 'Karnataka',
            'shipping_zip' => '560001',
            'shipping_country' => 'India',
            'terms_consent' => 'on',
        ];

        $response = $this->actingAs($this->customer)->postJson(route('checkout.place-order'), $orderPayload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'payment_mode' => 'cod',
        ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->customer->id,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'status' => 'confirmed',
        ]);
    }

    /** 8. Test Online Order Wallet Locking, Auto-Refund on Failure, and Double-Spending Prevention */
    public function test_pending_online_order_locks_wallet_balance_and_auto_refunds_on_failure(): void
    {
        $wallet = app(WalletService::class)->getOrCreateWallet($this->customer);
        $wallet->update(['balance' => 500.00]);

        $this->actingAs($this->customer)->post('/cart/add', [
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $orderPayload = [
            'use_wallet' => '1',
            'payment_method' => 'online',
            'shipping_name' => 'Test Customer',
            'shipping_email' => 'customer@example.com',
            'shipping_phone' => '9876543210',
            'shipping_address' => '123 MG Road',
            'shipping_city' => 'Bengaluru',
            'shipping_state' => 'Karnataka',
            'shipping_zip' => '560001',
            'shipping_country' => 'India',
            'terms_consent' => 'on',
        ];

        // Eager Pending Online Order Creation
        $order = app(\App\Services\CheckoutService::class)->createPendingOnlineOrder($orderPayload, $this->customer);

        // Wallet balance should immediately be debited (500 - 500 = 0) to prevent double spending
        $this->assertEquals(0.00, $wallet->fresh()->balance);
        $this->assertEquals(500.00, (float) $order->wallet_amount_used);

        // Simulate payment failure / network drop
        app(\App\Services\CheckoutService::class)->markOrderPaymentFailed($order, 'Network disconnected during payment authorization.');

        // Wallet balance should automatically be refunded back to 500.00
        $this->assertEquals(500.00, $wallet->fresh()->balance);
    }

    /** 9. Test Switching Failed Online Order to COD Re-Debits Wallet */
    public function test_switching_failed_online_order_to_cod_redeems_wallet(): void
    {
        $wallet = app(WalletService::class)->getOrCreateWallet($this->customer);
        $wallet->update(['balance' => 500.00]);

        $this->actingAs($this->customer)->post('/cart/add', [
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $orderPayload = [
            'use_wallet' => '1',
            'payment_method' => 'online',
            'shipping_name' => 'Test Customer',
            'shipping_email' => 'customer@example.com',
            'shipping_phone' => '9876543210',
            'shipping_address' => '123 MG Road',
            'shipping_city' => 'Bengaluru',
            'shipping_state' => 'Karnataka',
            'shipping_zip' => '560001',
            'shipping_country' => 'India',
            'terms_consent' => 'on',
        ];

        $order = app(\App\Services\CheckoutService::class)->createPendingOnlineOrder($orderPayload, $this->customer);
        app(\App\Services\CheckoutService::class)->markOrderPaymentFailed($order, 'Gateway error');

        // Customer clicks "Switch to COD"
        $response = $this->actingAs($this->customer)->post(route('checkout.switch_to_cod', $order));

        $response->assertStatus(302);

        $order->refresh();
        $this->assertEquals('cod', $order->payment_method);
        $this->assertEquals('confirmed', $order->status);

        // Wallet should be re-debited 500.00 for the confirmed COD order (500 - 500 = 0)
        $this->assertEquals(0.00, $wallet->fresh()->balance);
    }

    /** 10. Test Repeated Online Checkout Reuses Existing Pending Order Without Duplicates */
    public function test_repeated_online_checkout_reuses_existing_pending_order_without_duplicates(): void
    {
        $this->actingAs($this->customer)->post('/cart/add', [
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $orderPayload = [
            'payment_method' => 'online',
            'shipping_name' => 'Test Customer',
            'shipping_email' => 'customer@example.com',
            'shipping_phone' => '9876543210',
            'shipping_address' => '123 MG Road',
            'shipping_city' => 'Bengaluru',
            'shipping_state' => 'Karnataka',
            'shipping_zip' => '560001',
            'shipping_country' => 'India',
            'terms_consent' => 'on',
        ];

        $checkoutService = app(\App\Services\CheckoutService::class);

        // First click "Pay Online"
        $order1 = $checkoutService->createPendingOnlineOrder($orderPayload, $this->customer);

        // Second click "Pay Online" (simulating user retrying / clicking again)
        $order2 = $checkoutService->createPendingOnlineOrder($orderPayload, $this->customer);

        // Should return the EXACT same order instance (reused), NOT a new duplicate order
        $this->assertEquals($order1->id, $order2->id);
        $this->assertEquals($order1->order_number, $order2->order_number);

        // Total pending orders in database for customer should be 1 (zero duplicates)
        $pendingCount = \App\Models\Order::where('user_id', $this->customer->id)
            ->where('payment_status', 'pending')
            ->count();
        $this->assertEquals(1, $pendingCount);
    }
}
