<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Checkout CSRF + session persistence.
 *
 * Production bug being guarded: the session was never persisted between the
 * GET /checkout render and the POST /order/place submit, so the token rendered
 * into the page no longer matched the fresh session created by the POST and
 * Laravel answered 419 "CSRF token mismatch". These tests pin the whole flow:
 * render -> token -> POST with token -> order created -> session kept for
 * tracking.
 *
 * Note on CSRF enforcement: Laravel's ValidateCsrfToken middleware bypasses
 * validation while running unit tests (runningUnitTests()), so ordinary
 * feature-level POSTs can never observe 419. The tests below therefore flip
 * the container "env" binding to "production" around each POST — the same
 * mechanism the framework itself uses via useEnvironment()/detectEnvironment()
 * — which disables ONLY the unit-test bypass and makes the real middleware
 * enforce token matching exactly like production.
 */
class CheckoutSessionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * POST with the framework's unit-test CSRF bypass disabled, then restore
     * the testing environment afterwards.
     */
    private function postWithCsrfEnforced(string $route, array $payload, array $headers = [])
    {
        $this->app['env'] = 'production';

        try {
            return $this->postJson($route, $payload, $headers);
        } finally {
            $this->app['env'] = 'testing';
        }
    }

    private function makeProduct(): Product
    {
        $category = Category::create([
            'name' => 'Coffee',
            'slug' => 'basic-coffee',
            'type' => 'minuman',
            'is_active' => true,
        ]);

        return Product::create([
            'category_id' => $category->id,
            'name' => 'Kopi Susu NORD',
            'slug' => 'kopi-susu-nord',
            'description' => 'Espresso, steamed milk, and a NORD signature.',
            'price' => 20000,
            'image_url' => 'images/menu/kopi-susu-nord.jpg',
            'is_available' => true,
        ]);
    }

    private function itemsPayload(Product $product): array
    {
        return [
            'items' => [[
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => 20000,
                'quantity' => 2,
                'options' => [],
            ]],
            'payment_method' => 'qris',
            'order_type' => 'dine_in',
        ];
    }

    public function test_checkout_page_renders_and_embeds_csrf_token(): void
    {
        $this->get(route('customer.checkout'))
            ->assertOk()
            ->assertSee('X-CSRF-TOKEN');
    }

    public function test_csrf_token_is_stable_across_consecutive_requests(): void
    {
        $this->get(route('customer.checkout'))->assertOk();
        $tokenOne = session()->token();

        $this->get(route('customer.checkout'))->assertOk();
        $tokenTwo = session()->token();

        $this->assertSame($tokenOne, $tokenTwo, 'Session must persist so the rendered CSRF token stays valid for the next request.');
    }

    public function test_place_order_with_rendered_csrf_token_succeeds_under_real_csrf_check(): void
    {
        $product = $this->makeProduct();

        // Render checkout so a real token exists in the session, like a browser visit.
        $this->get(route('customer.checkout'))->assertOk();
        $token = session()->token();

        // With real middleware enforcement, a legitimately rendered token must pass.
        $response = $this->postWithCsrfEnforced(
            route('customer.placeOrder'),
            $this->itemsPayload($product),
            ['X-CSRF-TOKEN' => $token],
        );

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonStructure(['order_number', 'redirect_url']);

        $order = Order::where('order_number', $response->json('order_number'))->first();
        $this->assertNotNull($order, 'Order must be created after passing CSRF validation.');
        $this->assertSame('unpaid', $order->status);
        // 2 x 20000 = 40000 subtotal, 10% tax = 4000, total 44000.
        $this->assertSame(40000, (int) $order->subtotal);
        $this->assertSame(4000, (int) $order->tax);
        $this->assertSame(44000, (int) $order->total);
        $this->assertCount(1, $order->items);
    }

    public function test_place_order_without_csrf_token_is_rejected_by_real_middleware(): void
    {
        $product = $this->makeProduct();

        $this->get(route('customer.checkout'))->assertOk();

        $this->postWithCsrfEnforced(route('customer.placeOrder'), $this->itemsPayload($product))
            ->assertStatus(419)
            ->assertJson(['message' => 'CSRF token mismatch.']);

        $this->assertSame(0, Order::count(), 'No order may be created when CSRF validation fails.');
    }

    public function test_place_order_with_tampered_csrf_token_is_rejected_by_real_middleware(): void
    {
        $product = $this->makeProduct();

        $this->get(route('customer.checkout'))->assertOk();

        $this->postWithCsrfEnforced(
            route('customer.placeOrder'),
            $this->itemsPayload($product),
            ['X-CSRF-TOKEN' => str_repeat('x', 40)],
        )->assertStatus(419);

        $this->assertSame(0, Order::count());
    }

    public function test_session_keeps_last_order_number_for_tracking(): void
    {
        $product = $this->makeProduct();

        $this->get(route('customer.checkout'))->assertOk();
        $token = session()->token();

        $response = $this->postWithCsrfEnforced(
            route('customer.placeOrder'),
            $this->itemsPayload($product),
            ['X-CSRF-TOKEN' => $token],
        )->assertOk();

        $orderNumber = $response->json('order_number');
        $this->assertSame($orderNumber, session('last_order_number'), 'Session must carry the order number across requests.');

        // A later request (no order number in the URL) falls back to the session value.
        $this->get(route('customer.trackStatus'))
            ->assertOk()
            ->assertSee($orderNumber);
    }

    public function test_cookie_session_driver_persists_token_and_passes_checkout_flow(): void
    {
        // The production configuration: sessions ride entirely in the encrypted
        // session cookie, no server-side storage involved.
        config(['session.driver' => 'cookie']);

        $product = $this->makeProduct();

        $this->get(route('customer.checkout'))->assertOk();
        $tokenOne = session()->token();

        // A "new visit" reading the cookie back must resolve the same token.
        $this->get(route('customer.checkout'))->assertOk();
        $tokenTwo = session()->token();
        $this->assertSame($tokenOne, $tokenTwo, 'Cookie-based session must keep the same CSRF token across requests.');

        $this->postWithCsrfEnforced(
            route('customer.placeOrder'),
            $this->itemsPayload($product),
            ['X-CSRF-TOKEN' => $tokenOne],
        )->assertOk()->assertJson(['success' => true]);

        $this->assertSame(1, Order::count());
    }
}
