<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffPortalsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic roles if not present
        $roles = [
            1 => 'Super Admin',
            2 => 'Admin',
            3 => 'Customer',
            4 => 'Product Manager',
            5 => 'Order Manager',
            6 => 'Customer Support',
            7 => 'Delivery Partner',
        ];

        foreach ($roles as $id => $name) {
            Role::firstOrCreate(['id' => $id], ['name' => $name, 'slug' => strtolower(str_replace(' ', '_', $name))]);
        }
    }

    /** 1. Super Admin Portal Access */
    public function test_super_admin_can_access_admin_dashboard_and_modules(): void
    {
        $superAdmin = User::factory()->create([
            'role_id' => User::ROLE_SUPER_ADMIN,
            'email' => 'superadmin@shopcalm.com',
        ]);

        $this->actingAs($superAdmin, 'admin')->get('https://hub.localhost/admin')->assertStatus(200);
        $this->actingAs($superAdmin, 'admin')->get('https://hub.localhost/admin/products')->assertStatus(200);
        $this->actingAs($superAdmin, 'admin')->get('https://hub.localhost/admin/orders')->assertStatus(200);
        $this->actingAs($superAdmin, 'admin')->get('https://hub.localhost/admin/categories')->assertStatus(200);
        $this->actingAs($superAdmin, 'admin')->get('https://hub.localhost/admin/coupons')->assertStatus(200);
        $this->actingAs($superAdmin, 'admin')->get('https://hub.localhost/admin/wallets')->assertStatus(200);
        $this->actingAs($superAdmin, 'admin')->get('https://hub.localhost/admin/settings')->assertStatus(200);
        $this->actingAs($superAdmin, 'admin')->get('https://hub.localhost/admin/integrations')->assertStatus(200);
    }

    /** 2. Admin Portal Access */
    public function test_admin_can_access_admin_dashboard_and_orders(): void
    {
        $admin = User::factory()->create([
            'role_id' => User::ROLE_ADMIN,
            'email' => 'admin@shopcalm.com',
        ]);

        $this->actingAs($admin, 'admin')->get('https://hub.localhost/admin')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('https://hub.localhost/admin/orders')->assertStatus(200);
        $this->actingAs($admin, 'admin')->get('https://hub.localhost/admin/products')->assertStatus(200);
    }

    /** 3. Product Manager Portal Access */
    public function test_product_manager_can_access_product_manager_portal(): void
    {
        $pm = User::factory()->create([
            'role_id' => User::ROLE_PRODUCT_MANAGER,
            'email' => 'pm@shopcalm.com',
        ]);

        $this->actingAs($pm, 'product_manager')->get('https://hub.localhost/product-manager/dashboard')->assertStatus(200);
        $this->actingAs($pm, 'product_manager')->get('https://hub.localhost/product-manager/products')->assertStatus(200);
        $this->actingAs($pm, 'product_manager')->get('https://hub.localhost/product-manager/stock')->assertStatus(200);
        $this->actingAs($pm, 'product_manager')->get('https://hub.localhost/product-manager/reviews')->assertStatus(200);
        $this->actingAs($pm, 'product_manager')->get('https://hub.localhost/product-manager/reports')->assertStatus(200);
    }

    /** 4. Order Manager Portal Access */
    public function test_order_manager_can_access_order_manager_portal(): void
    {
        $om = User::factory()->create([
            'role_id' => User::ROLE_ORDER_MANAGER,
            'email' => 'om@shopcalm.com',
        ]);

        $this->actingAs($om, 'order_manager')->get('https://hub.localhost/order-manager/dashboard')->assertStatus(200);
        $this->actingAs($om, 'order_manager')->get('https://hub.localhost/order-manager/orders')->assertStatus(200);
        $this->actingAs($om, 'order_manager')->get('https://hub.localhost/order-manager/alerts')->assertStatus(200);
        $this->actingAs($om, 'order_manager')->get('https://hub.localhost/order-manager/rider-performance')->assertStatus(200);
        $this->actingAs($om, 'order_manager')->get('https://hub.localhost/order-manager/settlements')->assertStatus(200);
    }

    /** 5. Customer Support Portal Access */
    public function test_customer_support_can_access_support_portal(): void
    {
        $support = User::factory()->create([
            'role_id' => User::ROLE_SUPPORT,
            'email' => 'support@shopcalm.com',
        ]);

        $this->actingAs($support, 'support')->get('https://hub.localhost/support/dashboard')->assertStatus(200);
        $this->actingAs($support, 'support')->get('https://hub.localhost/support/enquiries')->assertStatus(200);
        $this->actingAs($support, 'support')->get('https://hub.localhost/support/customers')->assertStatus(200);
        $this->actingAs($support, 'support')->get('https://hub.localhost/support/subscribers')->assertStatus(200);
    }

    /** 6. Delivery Partner / Rider Portal Access */
    public function test_delivery_partner_can_access_delivery_portal(): void
    {
        $rider = User::factory()->create([
            'role_id' => User::ROLE_DELIVERY_PARTNER,
            'email' => 'rider@shopcalm.com',
        ]);

        $this->actingAs($rider, 'delivery_partner')->get('https://hub.localhost/delivery/dashboard')->assertStatus(200);
        $this->actingAs($rider, 'delivery_partner')->get('https://hub.localhost/delivery/completed')->assertStatus(200);
        $this->actingAs($rider, 'delivery_partner')->get('https://hub.localhost/delivery/settlements')->assertStatus(200);
    }

    /** 7. Unauthenticated Access Control Redirects */
    public function test_unauthenticated_staff_redirects_to_login(): void
    {
        $this->get('https://hub.localhost/admin')->assertRedirect();
        $this->get('https://hub.localhost/product-manager/dashboard')->assertRedirect();
        $this->get('https://hub.localhost/order-manager/dashboard')->assertRedirect();
        $this->get('https://hub.localhost/support/dashboard')->assertRedirect();
        $this->get('https://hub.localhost/delivery/dashboard')->assertRedirect();
    }
}
