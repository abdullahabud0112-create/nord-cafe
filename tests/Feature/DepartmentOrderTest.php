<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Cashier (minuman) / Kitchen (makanan) department split.
 *
 * One order, one order code, one payment total — but each department owns an
 * independent processing slice and status.
 */
class DepartmentOrderTest extends TestCase
{
    use RefreshDatabase;

    private function makeCategory(string $type): Category
    {
        return Category::create([
            'name' => ucfirst($type),
            'slug' => $type . '-' . uniqid(),
            'type' => $type,
            'icon' => $type === 'minuman' ? 'local_cafe' : 'restaurant',
            'is_active' => true,
        ]);
    }

    private function makeProduct(Category $category, string $name, int $price = 20000): Product
    {
        return Product::create([
            'category_id' => $category->id,
            'name' => $name,
            'slug' => Str::slug($name) . '-' . uniqid(),
            'description' => 'Menu pengujian',
            'price' => $price,
            'image_url' => '/placeholder.svg',
            'is_available' => true,
        ]);
    }

    private function makeOrder(array $overrides = [], array $items = []): Order
    {
        $order = Order::create(array_merge([
            'order_number' => 'ORD-TST-' . Str::upper(Str::random(8)),
            'customer_name' => 'Pelanggan Uji',
            'table_number' => 'Meja 05',
            'order_type' => 'dine_in',
            'status' => 'paid',
            'payment_status' => 'paid',
            'payment_method' => 'qris',
            'subtotal' => 50000,
            'tax' => 5000,
            'total' => 55000,
        ], $overrides));

        foreach ($items as $item) {
            $quantity = $item['quantity'] ?? 1;
            $unitPrice = $item['unit_price'] ?? $item['product']->price;

            $order->items()->create([
                'product_id' => $item['product']->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'options_json' => $item['options'] ?? [],
                'item_notes' => $item['notes'] ?? null,
                'subtotal' => $unitPrice * $quantity,
            ]);
        }

        return $order->fresh();
    }

    private function drinkAndFood(): array
    {
        $drinkCategory = $this->makeCategory('minuman');
        $foodCategory = $this->makeCategory('makanan');

        return [
            $this->makeProduct($drinkCategory, 'Iced Caramel Latte', 23000),
            $this->makeProduct($foodCategory, 'Chicken Katsu Rice', 35000),
        ];
    }

    public function test_mixed_place_order_creates_one_order_with_split_departments(): void
    {
        [$drink, $food] = $this->drinkAndFood();

        $response = $this->postJson(route('customer.placeOrder'), [
            'customer_name' => 'Ayu',
            'table_number' => 'Meja 07',
            'order_type' => 'dine_in',
            'items' => [
                ['product_id' => $food->id, 'quantity' => 2, 'options' => ['spice' => 'Pedas'], 'notes' => 'Saus dipisah'],
                ['product_id' => $drink->id, 'quantity' => 3, 'options' => ['ice' => 'Less Ice', 'sugar' => 'Normal'], 'notes' => 'Tanpa gula'],
            ],
        ]);

        $response->assertOk()->assertJson(['success' => true]);

        $this->assertSame(1, Order::count(), 'Mixed items must stay inside one transaction.');

        $order = Order::with('items')->first();
        $foodItem = $order->items->firstWhere('product_id', $food->id);
        $drinkItem = $order->items->firstWhere('product_id', $drink->id);

        $this->assertNotNull($foodItem);
        $this->assertNotNull($drinkItem);
        $this->assertSame($order->id, $foodItem->order_id);
        $this->assertSame($order->id, $drinkItem->order_id);
        $this->assertSame('makanan', $foodItem->department);
        $this->assertSame('minuman', $drinkItem->department);
        $this->assertSame(OrderItem::STATUS_PENDING, $foodItem->status);
        $this->assertSame(OrderItem::STATUS_PENDING, $drinkItem->status);
        $this->assertSame($response->json('order_number'), $order->order_number);
        $this->assertSame(route('customer.ticket', $order->order_number), $response->json('redirect_url'));

        // Server side pricing: 2 x 35000 + 3 x 23000 = 139000, tax 13900.
        $this->assertSame(139000, (int) $order->subtotal);
        $this->assertSame(13900, (int) $order->tax);
        $this->assertSame(152900, (int) $order->total);
    }

    public function test_food_only_order_appears_in_kitchen_and_not_in_cashier_list(): void
    {
        [, $food] = $this->drinkAndFood();
        $order = $this->makeOrder([], [['product' => $food, 'quantity' => 1]]);

        $this->get(route('kitchen.index'))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee($food->name);

        $this->get(route('cashier.incomingOrders'))
            ->assertOk()
            ->assertDontSee($order->order_number)
            ->assertDontSee($food->name);
    }

    public function test_drink_only_order_appears_in_cashier_and_not_in_kitchen_list(): void
    {
        [$drink] = $this->drinkAndFood();
        $order = $this->makeOrder([], [['product' => $drink, 'quantity' => 2]]);

        $this->get(route('cashier.incomingOrders'))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee($drink->name);

        $this->get(route('kitchen.index'))
            ->assertOk()
            ->assertDontSee($order->order_number)
            ->assertDontSee($drink->name);
    }

    public function test_detail_pages_show_quantity_options_and_notes_for_their_own_department(): void
    {
        [$drink, $food] = $this->drinkAndFood();

        $order = $this->makeOrder([], [
            ['product' => $drink, 'quantity' => 2, 'options' => ['ice' => 'Less Ice', 'size' => 'Large'], 'notes' => 'Tanpa gula'],
            ['product' => $food, 'quantity' => 1, 'options' => ['spice' => 'Pedas'], 'notes' => 'Saus dipisah'],
        ]);

        $this->get(route('cashier.orderDetail', $order->id))
            ->assertOk()
            ->assertSee($drink->name)
            ->assertSee('Tanpa gula')
            ->assertSee('Less Ice')
            ->assertSee('Large')
            ->assertSee('23.000')
            ->assertDontSee($food->name)
            ->assertDontSee('Saus dipisah');

        $this->get(route('kitchen.show', $order->id))
            ->assertOk()
            ->assertSee($food->name)
            ->assertSee('Saus dipisah')
            ->assertSee('Pedas')
            ->assertSee('35.000')
            ->assertDontSee($drink->name)
            ->assertDontSee('Tanpa gula');
    }

    public function test_department_statuses_are_independent_and_order_completes_last(): void
    {
        [$drink, $food] = $this->drinkAndFood();
        $order = $this->makeOrder([], [
            ['product' => $drink, 'quantity' => 2],
            ['product' => $food, 'quantity' => 1],
        ]);

        // Kitchen starts cooking: drinks stay untouched.
        $this->postJson(route('kitchen.updateStatus', $order->id), ['status' => 'preparing'])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame(OrderItem::STATUS_PREPARING, $order->items()->where('department', 'makanan')->first()->status);
        $this->assertSame(OrderItem::STATUS_PENDING, $order->items()->where('department', 'minuman')->first()->status);
        $this->assertSame('preparing', $order->fresh()->status);

        // Cashier finishes all drinks: food keeps cooking and order is not done.
        $this->postJson(route('cashier.updateDepartmentStatus', $order->id), ['status' => 'completed'])
            ->assertOk();

        $this->assertSame(OrderItem::STATUS_COMPLETED, $order->items()->where('department', 'minuman')->first()->status);
        $this->assertSame(OrderItem::STATUS_PREPARING, $order->items()->where('department', 'makanan')->first()->status);
        $this->assertSame('preparing', $order->fresh()->status);
        $this->assertNotSame('completed', $order->fresh()->status);

        // Kitchen closes its slice: now the whole order is complete.
        $this->postJson(route('kitchen.updateStatus', $order->id), ['status' => 'completed'])
            ->assertOk();

        $this->assertSame(OrderItem::STATUS_COMPLETED, $order->items()->where('department', 'makanan')->first()->status);
        $this->assertSame('completed', $order->fresh()->status);
    }

    public function test_single_item_update_does_not_touch_sibling_items(): void
    {
        [$drink] = $this->drinkAndFood();
        $drinkCategory = $drink->category;
        $otherDrink = $this->makeProduct($drinkCategory, 'Americano', 18000);

        $order = $this->makeOrder([], [
            ['product' => $drink, 'quantity' => 1],
            ['product' => $otherDrink, 'quantity' => 1],
        ]);

        $first = $order->items()->where('product_id', $drink->id)->first();

        $this->postJson(route('cashier.updateItemStatus', [$order->id, $first->id]), ['status' => 'completed'])
            ->assertOk();

        $this->assertSame(OrderItem::STATUS_COMPLETED, $first->fresh()->status);
        $this->assertSame(
            OrderItem::STATUS_PENDING,
            $order->items()->where('product_id', $otherDrink->id)->first()->status
        );
        $this->assertSame('preparing', $order->fresh()->status);
    }

    public function test_department_isolation_enforced_at_item_level(): void
    {
        [$drink, $food] = $this->drinkAndFood();
        $order = $this->makeOrder([], [
            ['product' => $drink, 'quantity' => 1],
            ['product' => $food, 'quantity' => 1],
        ]);

        $user = User::factory()->create(['role' => 'customer']);

        // Any authenticated user can access both cashier and kitchen routes
        // (no login system, no role-based middleware).
        $this->actingAs($user)->get(route('cashier.incomingOrders'))->assertOk();
        $this->actingAs($user)->get(route('kitchen.index'))->assertOk();

        // But department isolation is enforced at the item level:
        // Cashier endpoints reject food items, Kitchen endpoints reject drink items.
        $foodItem = $order->items()->where('department', 'makanan')->first();
        $drinkItem = $order->items()->where('department', 'minuman')->first();

        $this->actingAs($user)
            ->postJson(route('cashier.updateItemStatus', [$order->id, $foodItem->id]), ['status' => 'completed'])
            ->assertStatus(422);

        $this->actingAs($user)
            ->postJson(route('kitchen.updateItemStatus', [$order->id, $drinkItem->id]), ['status' => 'completed'])
            ->assertStatus(422);

        // Items remain unchanged
        $this->assertSame(OrderItem::STATUS_PENDING, $foodItem->fresh()->status);
        $this->assertSame(OrderItem::STATUS_PENDING, $drinkItem->fresh()->status);

        // User can process their own department's items
        $this->actingAs($user)
            ->postJson(route('cashier.updateDepartmentStatus', $order->id), ['status' => 'preparing'])
            ->assertOk();

        $this->actingAs($user)
            ->postJson(route('kitchen.updateStatus', $order->id), ['status' => 'preparing'])
            ->assertOk();

        $this->actingAs($user)
            ->postJson(route('cashier.updateDepartmentStatus', $order->id), ['status' => 'completed'])
            ->assertOk();

        $this->actingAs($user)
            ->postJson(route('kitchen.updateStatus', $order->id), ['status' => 'completed'])
            ->assertOk();

        $this->assertSame('completed', $order->fresh()->status);
    }

    public function test_role_cannot_touch_the_other_departments_items_even_through_its_own_endpoint(): void
    {
        [$drink, $food] = $this->drinkAndFood();
        $order = $this->makeOrder([], [
            ['product' => $drink, 'quantity' => 1],
            ['product' => $food, 'quantity' => 1],
        ]);

        $cashier = User::factory()->create(['role' => 'cashier']);
        $kitchen = User::factory()->create(['role' => 'kitchen']);

        $foodItem = $order->items()->where('department', 'makanan')->first();
        $drinkItem = $order->items()->where('department', 'minuman')->first();

        $this->actingAs($cashier)
            ->postJson(route('cashier.updateItemStatus', [$order->id, $foodItem->id]), ['status' => 'completed'])
            ->assertStatus(422);

        $this->actingAs($kitchen)
            ->postJson(route('kitchen.updateItemStatus', [$order->id, $drinkItem->id]), ['status' => 'completed'])
            ->assertStatus(422);

        $this->assertSame(OrderItem::STATUS_PENDING, $foodItem->fresh()->status);
        $this->assertSame(OrderItem::STATUS_PENDING, $drinkItem->fresh()->status);
    }

    public function test_payment_and_processing_status_stay_separate_without_double_transaction(): void
    {
        [$drink, $food] = $this->drinkAndFood();
        $order = $this->makeOrder([
            'status' => 'unpaid',
            'payment_status' => 'unpaid',
        ], [
            ['product' => $drink, 'quantity' => 2],
            ['product' => $food, 'quantity' => 1],
        ]);

        $originalTotal = (int) $order->total;

        // Work may start while payment is pending, but the main status must
        // not pretend the order is paid/processing.
        $this->postJson(route('cashier.updateDepartmentStatus', $order->id), ['status' => 'completed'])
            ->assertOk();

        $order->refresh();
        $this->assertSame('unpaid', $order->status);
        $this->assertSame('unpaid', $order->payment_status);
        $this->assertSame($originalTotal, (int) $order->total);
        $this->assertSame(1, Order::count());

        // Cashier confirms the payment: payment_status flips, operational
        // status is recalculated from the existing items.
        $this->postJson(route('cashier.confirmPayment', $order->id))->assertOk();

        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('preparing', $order->status);
        $this->assertSame($originalTotal, (int) $order->total);
        $this->assertSame(1, Order::count());

        // Kitchen closes the remaining food; the order becomes complete while
        // the payment record remains paid.
        $this->postJson(route('kitchen.updateStatus', $order->id), ['status' => 'completed'])->assertOk();

        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('completed', $order->status);
        $this->assertSame($originalTotal, (int) $order->total);
        $this->assertSame(1, Order::count());
    }

    public function test_legacy_order_level_completion_is_blocked_while_items_are_unfinished(): void
    {
        [$drink] = $this->drinkAndFood();
        $order = $this->makeOrder([], [['product' => $drink, 'quantity' => 1]]);

        $this->postJson(route('cashier.updateStatus', $order->id), ['status' => 'completed'])
            ->assertStatus(409)
            ->assertJson(['success' => false, 'status' => false]);

        $this->assertSame('paid', $order->fresh()->status);
        $this->assertSame(OrderItem::STATUS_PENDING, $order->items()->first()->status);
    }

    public function test_scanned_ticket_qr_still_opens_cashier_verification_for_food_only_orders(): void
    {
        [, $food] = $this->drinkAndFood();
        $order = $this->makeOrder(['status' => 'unpaid', 'payment_status' => 'unpaid'], [
            ['product' => $food, 'quantity' => 1],
        ]);

        $ticketUrl = route('cashier.editStatus', $order->id);

        $this->postJson(route('cashier.searchBarcode'), ['code' => $ticketUrl])
            ->assertOk()
            ->assertJson(['found' => true, 'redirect_url' => $ticketUrl]);

        $this->get($ticketUrl)
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('Konfirmasi Pembayaran')
            ->assertSee('tidak memiliki item minuman');
    }

    public function test_kitchen_and_cashier_pages_are_reachable_in_kiosk_mode(): void
    {
        $this->get(route('kitchen.index'))->assertOk();
        $this->get(route('kitchen.feed'))->assertOk()->assertJsonStructure(['signature', 'pending', 'preparing']);
        $this->get(route('cashier.feed'))->assertOk()->assertJsonStructure(['signature', 'pending', 'preparing']);
    }
}
