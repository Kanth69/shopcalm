<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductManagerProductTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_product_manager_can_access_create_product_page(): void
    {
        $pm = User::where('email', 'pm@shopcalm.com')->first();
        if (!$pm) {
            $pm = User::factory()->create(['role_id' => 4, 'email' => 'pm@shopcalm.com']);
        }

        $response = $this->actingAs($pm)->get('/product-manager/products/create');
        $response->assertStatus(200);
        $response->assertSee('Add New Product');
    }

    public function test_product_manager_can_create_product_with_initial_stock(): void
    {
        $pm = User::where('email', 'pm@shopcalm.com')->first();
        if (!$pm) {
            $pm = User::factory()->create(['role_id' => 4, 'email' => 'pm@shopcalm.com']);
        }

        $category = Category::first() ?? Category::create(['name' => 'Tech', 'slug' => 'tech', 'status' => 'Active']);
        $brand = Brand::first() ?? Brand::create(['name' => 'Sony', 'slug' => 'sony', 'status' => true]);

        $image = UploadedFile::fake()->image('product.jpg');

        $sku = 'TEST-' . uniqid();
        $response = $this->actingAs($pm)->post('/product-manager/products', [
            'name' => 'Automated Test Headset',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'sku' => $sku,
            'price' => 1999.00,
            'stock' => 25,
            'short_description' => 'Test short description',
            'description' => 'Detailed product description test',
            'main_image' => $image,
        ]);

        $response->assertRedirect('/product-manager/products/pending');

        $this->assertDatabaseHas('products', [
            'sku' => $sku,
            'status' => 'Pending_Approval',
            'stock' => 25,
            'submitted_by' => $pm->id,
        ]);
    }
}
