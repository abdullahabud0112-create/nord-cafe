<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;

class PrdMenuTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_catalog_loads_with_nord_categories_and_products(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Katalog Menu');
        $response->assertSee('NORD CAFE');
        $this->assertGreaterThan(0, Category::count());
        $this->assertGreaterThan(0, Product::count());
    }

    public function test_filter_by_type_minuman(): void
    {
        $response = $this->get('/?filter=kopi');
        $response->assertStatus(200);
        $response->assertSee('Katalog Menu');
        $response->assertSee('Kopi');
    }

    public function test_filter_by_type_makanan(): void
    {
        $response = $this->get('/?filter=makanan');
        $response->assertStatus(200);
        $response->assertSee('Katalog Menu');
        $response->assertSee('Makanan');
    }

    public function test_filter_by_subcategory_steak(): void
    {
        $response = $this->get('/?category=premium-steak');
        $response->assertStatus(200);
        $response->assertSee('Katalog Menu');
    }

    public function test_cashier_menu_manager_and_toggle_availability(): void
    {
        $product = Product::first();
        $this->assertTrue((bool)$product->is_available);

        $response = $this->get('/cashier/menu');
        $response->assertStatus(200);

        $toggle = $this->post("/cashier/menu/{$product->id}/toggle-availability");
        $toggle->assertRedirect();
        $this->assertFalse((bool)$product->fresh()->is_available);
    }

    public function test_cashier_can_access_order_detail_by_order_number_and_url_search(): void
    {
        $order = \App\Models\Order::create([
            'order_number' => 'ORD-TEST-999',
            'customer_name' => 'Budi',
            'status' => 'unpaid',
            'subtotal' => 30000,
            'tax' => 3000,
            'total' => 33000,
            'payment_method' => 'qris',
            'order_type' => 'dine_in',
            'table_number' => 'Meja 05',
        ]);

        // Access via order_number in URL
        $response = $this->get("/cashier/order/{$order->order_number}");
        $response->assertStatus(200);
        $response->assertSee('ORD-TEST-999');

        // Access update-status via order_number in URL
        $updateStatusRes = $this->get("/cashier/order/{$order->order_number}/update-status");
        $updateStatusRes->assertStatus(200);
        $updateStatusRes->assertSee('ORD-TEST-999');

        // Search barcode with full URL
        $searchRes = $this->postJson('/cashier/search-barcode', [
            'code' => "http://localhost:8000/cashier/order/{$order->order_number}",
        ]);
        $searchRes->assertStatus(200);
        $searchRes->assertJson([
            'found' => true,
        ]);
    }
}

