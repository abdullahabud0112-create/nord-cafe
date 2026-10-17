<?php

namespace Tests\Feature;

use App\Events\OrderStatusUpdated;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CashierOrderStatusTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(string $status = 'unpaid', array $extra = []): Order
    {
        return Order::create(array_merge([
            'order_number' => 'ORD-TST-' . uniqid(),
            'customer_name' => 'Tester',
            'status' => $status,
            'subtotal' => 30000,
            'tax' => 3000,
            'total' => 33000,
            'payment_method' => 'qris',
            'order_type' => 'dine_in',
            'table_number' => 'Meja 01',
        ], $extra));
    }

    public function test_edit_status_missing_order_redirects_with_friendly_error(): void
    {
        $this->get('/cashier/order/999999/update-status')
            ->assertRedirect(route('cashier.incomingOrders'))
            ->assertSessionHas('error');
    }

    public function test_update_status_missing_order_returns_json_404(): void
    {
        $this->postJson('/cashier/order/999999/update-status', ['status' => 'paid'])
            ->assertStatus(404)
            ->assertJson(['success' => false, 'status' => false]);
    }

    public function test_update_status_malformed_id_returns_json_404(): void
    {
        $this->postJson('/cashier/order/bad-id-@@@/update-status', ['status' => 'paid'])
            ->assertStatus(404)
            ->assertJson(['success' => false, 'status' => false]);
    }

    public function test_completed_order_is_locked_against_updates(): void
    {
        $order = $this->makeOrder('completed');

        $this->postJson(route('cashier.updateStatus', $order->id), ['status' => 'paid'])
            ->assertStatus(409)
            ->assertJson(['success' => false, 'status' => false]);

        $this->assertSame('completed', $order->fresh()->status);
    }

    public function test_cancelled_order_is_locked_against_updates(): void
    {
        $order = $this->makeOrder('cancelled');

        $this->postJson(route('cashier.updateStatus', $order->id), ['status' => 'preparing'])
            ->assertStatus(409)
            ->assertJson(['success' => false, 'status' => false]);

        $this->assertSame('cancelled', $order->fresh()->status);
    }

    public function test_completed_order_cannot_confirm_payment_again(): void
    {
        $order = $this->makeOrder('completed');

        $this->postJson(route('cashier.confirmPayment', $order->id))
            ->assertStatus(409)
            ->assertJson(['success' => false, 'status' => false]);

        $this->assertSame('completed', $order->fresh()->status);
    }

    public function test_valid_forward_transition_updates_status_and_fires_event(): void
    {
        Event::fake([OrderStatusUpdated::class]);
        $order = $this->makeOrder('paid');

        $this->postJson(route('cashier.updateStatus', $order->id), ['status' => 'preparing'])
            ->assertOk()
            ->assertJson(['success' => true, 'status' => 'preparing']);

        $this->assertSame('preparing', $order->fresh()->status);
        $this->assertSame('paid', $order->fresh()->payment_status);
        Event::assertDispatched(OrderStatusUpdated::class);
    }

    public function test_backward_transition_to_unpaid_is_rejected(): void
    {
        $order = $this->makeOrder('preparing');

        $this->postJson(route('cashier.updateStatus', $order->id), ['status' => 'unpaid'])
            ->assertStatus(409)
            ->assertJson(['success' => false, 'status' => false]);

        $this->assertSame('preparing', $order->fresh()->status);
    }

    public function test_same_status_resubmission_is_idempotent_success(): void
    {
        $order = $this->makeOrder('paid');

        $this->postJson(route('cashier.updateStatus', $order->id), ['status' => 'paid'])
            ->assertOk()
            ->assertJson(['success' => true, 'status' => 'paid']);

        $this->assertSame('paid', $order->fresh()->status);
    }

    public function test_pending_status_normalizes_to_unpaid(): void
    {
        $order = $this->makeOrder('unpaid');

        $this->postJson(route('cashier.updateStatus', $order->id), ['status' => 'pending'])
            ->assertOk()
            ->assertJson(['success' => true, 'status' => 'unpaid']);

        $this->assertSame('unpaid', $order->fresh()->status);
    }

    public function test_invalid_status_value_is_rejected(): void
    {
        $order = $this->makeOrder('paid');

        $this->postJson(route('cashier.updateStatus', $order->id), ['status' => 'foobar'])
            ->assertStatus(422);

        $this->assertSame('paid', $order->fresh()->status);
    }

    public function test_confirm_payment_marks_order_paid_and_fires_event(): void
    {
        Event::fake([OrderStatusUpdated::class]);
        $order = $this->makeOrder('unpaid');

        $this->postJson(route('cashier.confirmPayment', $order->id))
            ->assertOk()
            ->assertJson(['success' => true, 'status' => 'paid']);

        $fresh = $order->fresh();
        $this->assertSame('paid', $fresh->status);
        $this->assertSame('paid', $fresh->payment_status);
        Event::assertDispatched(OrderStatusUpdated::class);
    }

    public function test_terminal_web_update_redirects_back_with_error(): void
    {
        $order = $this->makeOrder('completed');

        $this->post(route('cashier.updateStatus', $order->id), ['status' => 'paid'])
            ->assertRedirect(route('cashier.editStatus', $order->id))
            ->assertSessionHas('error');

        $this->assertSame('completed', $order->fresh()->status);
    }

    public function test_guest_can_open_cashier_while_login_flow_not_built(): void
    {
        $this->get('/cashier')->assertOk();
    }

    public function test_customer_role_gets_403_on_cashier_routes(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $this->actingAs($user)->get('/cashier')->assertStatus(403);
        $this->actingAs($user)->get('/cashier/order/1/update-status')->assertStatus(403);
    }

    public function test_cashier_role_passes_soft_gate(): void
    {
        $user = User::factory()->create(['role' => 'cashier']);

        $this->actingAs($user)->get('/cashier')->assertOk();
    }

    public function test_unknown_route_returns_friendly_404_page(): void
    {
        $this->get('/halaman-tidak-ada')->assertStatus(404)->assertSee('404');
    }

    public function test_unknown_route_json_returns_json_404(): void
    {
        $this->getJson('/halaman-tidak-ada')
            ->assertStatus(404)
            ->assertJson(['status' => false]);
    }
}
