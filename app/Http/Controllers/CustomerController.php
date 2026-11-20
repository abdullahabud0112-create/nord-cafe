<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    // Screen: Menu Catalog (public/desain/menu_catalog)
    public function catalog(Request $request)
    {
        $selectedFilter = $request->get('filter', $request->get('type', 'all'));
        $activeChip = $request->get('chip'); // chip key → filtered page (one category at a time)

        // Flat 1-level category chips (horizontal scroll) — each is its own filtered view
        $chips = [
            ['key' => 'steak',            'label' => 'Steak',                'slugs' => ['premium-steak', 'steak-original', 'steak-crispy'], 'type' => 'makanan'],
            ['key' => 'rice-bowl',        'label' => 'Rice Bowl',            'slugs' => ['rice-bowl'], 'type' => 'makanan'],
            ['key' => 'baso-aci',         'label' => 'Baso Aci',             'slugs' => ['baso-aci'], 'type' => 'makanan'],
            ['key' => 'nasi-ayam',        'label' => 'Nasi Goreng & Ayam',   'slugs' => ['nasi-goreng', 'ayam-goreng'], 'type' => 'makanan'],
            ['key' => 'pasta',            'label' => 'Pasta',                'slugs' => ['pasta-series'], 'type' => 'makanan'],
            ['key' => 'burger',           'label' => 'Burger',               'slugs' => ['burger'], 'type' => 'makanan'],
            ['key' => 'indomie',          'label' => 'Indomie',              'slugs' => ['indomie-series'], 'type' => 'makanan'],
            ['key' => 'salad',            'label' => 'Salad',                'slugs' => ['salad-series'], 'type' => 'makanan'],
            ['key' => 'snack',            'label' => 'Snack',                'slugs' => ['snack'], 'type' => 'makanan'],
            ['key' => 'basic-coffee',     'label' => 'Basic Coffee',         'slugs' => ['basic-coffee'], 'type' => 'minuman'],
            ['key' => 'signature-coffee', 'label' => 'Signature Coffee',     'slugs' => ['signature'], 'type' => 'minuman'],
            ['key' => 'manual-brew',      'label' => 'Manual Brew',          'slugs' => ['manual-brew'], 'type' => 'minuman'],
            ['key' => 'bottle-glass',     'label' => 'Bottle & Glass',       'slugs' => ['by-bottle', 'by-glasses'], 'type' => 'minuman'],
            ['key' => 'non-coffee',       'label' => 'Non-Coffee',           'slugs' => ['non-coffee'], 'type' => 'minuman'],
            ['key' => 'yaqult',           'label' => 'Yaqult Series',        'slugs' => ['yaqult-series'], 'type' => 'minuman'],
            ['key' => 'tea',              'label' => 'Tea Series',           'slugs' => ['tea-series'], 'type' => 'minuman'],
        ];

        $activeChipDef = collect($chips)->firstWhere('key', $activeChip);
        if (!$activeChipDef) {
            $activeChip = null;
        }

        $query = Product::with('category')->where('is_available', true);

        // Active chip → only that category's products (separate filtered "page")
        if ($activeChipDef) {
            $query->whereHas('category', function ($q) use ($activeChipDef) {
                $q->whereIn('slug', $activeChipDef['slugs']);
            });
        } else {
            // Legacy filter params (tests & old URLs): filter=kopi|makanan|...
            if (in_array($selectedFilter, ['steak', 'rice-bowl', 'baso-aci', 'nasi-pasta', 'snack-burger', 'indomie', 'coffee', 'non-coffee', 'tea-yaqult'], true)) {
                $legacySlugs = [
                    'steak' => ['premium-steak', 'steak-original', 'steak-crispy'],
                    'rice-bowl' => ['rice-bowl'],
                    'baso-aci' => ['baso-aci'],
                    'nasi-pasta' => ['nasi-goreng', 'ayam-goreng', 'pasta-series'],
                    'snack-burger' => ['snack', 'burger', 'salad-series'],
                    'indomie' => ['indomie-series'],
                    'coffee' => ['basic-coffee', 'signature', 'by-glasses', 'by-bottle', 'manual-brew'],
                    'non-coffee' => ['non-coffee'],
                    'tea-yaqult' => ['tea-series', 'yaqult-series'],
                ];
                $query->whereHas('category', function ($q) use ($legacySlugs, $selectedFilter) {
                    $q->whereIn('slug', $legacySlugs[$selectedFilter]);
                });
            } elseif ($selectedFilter === 'kopi') {
                $query->whereHas('category', function ($q) {
                    $q->whereIn('slug', ['basic-coffee', 'signature', 'by-glasses', 'by-bottle', 'manual-brew']);
                });
            } elseif ($selectedFilter === 'non-kopi') {
                $query->whereHas('category', function ($q) {
                    $q->whereIn('slug', ['non-coffee', 'yaqult-series', 'tea-series']);
                });
            } elseif ($selectedFilter === 'minuman') {
                $query->whereHas('category', function ($q) {
                    $q->where('type', 'minuman');
                });
            } elseif ($selectedFilter === 'makanan') {
                $query->whereHas('category', function ($q) {
                    $q->where('type', 'makanan');
                });
            }

            if ($request->filled('category') && $request->category !== 'all') {
                $query->whereHas('category', function ($q) use ($request) {
                    $q->where('slug', $request->category);
                });
            }
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('tags', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('category_id')->get();
        $categories = Category::where('is_active', true)->get();

        // ONE section only: active chip page OR single default grid (never stacked categories)
        $sections = [];
        if ($activeChipDef) {
            $sections[] = [
                'key'   => $activeChipDef['key'],
                'label' => $activeChipDef['label'],
                'tab'   => $activeChipDef['type'] === 'makanan' ? 'Makanan' : 'Minuman',
                'type'  => $activeChipDef['type'],
                'items' => $products->values(),
            ];
        } elseif ($products->isNotEmpty()) {
            $types = $products->pluck('category.type')->filter()->unique()->values();
            $badgeType = $types->count() === 1 ? $types->first() : '';
            $sections[] = [
                'key'   => 'all',
                'label' => 'Menu',
                'tab'   => $badgeType === 'makanan' ? 'Makanan' : ($badgeType === 'minuman' ? 'Minuman' : ''),
                'type'  => $badgeType,
                'items' => $products->values(),
            ];
        }

        return view('customer.catalog', compact('products', 'categories', 'selectedFilter', 'chips', 'sections', 'activeChip'));
    }

    // Screen: Product Detail (public/desain/product_detail)
    public function productDetail($id)
    {
        $product = Product::with('category')->findOrFail($id);
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->take(3)
            ->get();

        return view('customer.product-detail', compact('product', 'relatedProducts'));
    }

    // Screen: Shopping Cart (public/desain/shopping_cart)
    public function cart()
    {
        // Same id => type map as checkout: cart splits "isi pesanan" into
        // Makanan/Minuman groups and routes the Checkout button accordingly.
        $productTypes = $this->productTypeMap();

        return view('customer.cart', compact('productTypes'));
    }

    /**
     * id => 'minuman' | 'makanan' for every product. Lets the client split the
     * localStorage cart (which predates category_type on some saved items)
     * without a network round trip.
     */
    private function productTypeMap(): array
    {
        return Product::query()
            ->with('category:id,type')
            // category_id must be selected — belongsTo needs it to match parents
            ->get(['id', 'category_id'])
            ->mapWithKeys(fn (Product $p): array => [
                (string) $p->id => ($p->category?->type === 'minuman') ? 'minuman' : 'makanan',
            ])
            ->all();
    }

    // Screen: Checkout (public/desain/checkout)
    // Order confirmation is split into 2 pages: Minuman (drink) → Makanan (food).
    // One order is still created at the end; the split is only a per-group review step.
    //
    // URLs:
    //   /checkout           — legacy entry, renders the Minuman page (?step=food still honored)
    //   /checkout/minuman   — canonical step 1 (customer.checkoutDrink)
    //   /checkout/makanan   — canonical step 2, payment + totals (customer.checkoutFood)
    public function checkout(Request $request)
    {
        $step = $request->query('step') === 'food' ? 'food' : 'drink';

        return $this->renderCheckout($step);
    }

    public function checkoutDrink()
    {
        return $this->renderCheckout('drink');
    }

    public function checkoutFood()
    {
        return $this->renderCheckout('food');
    }

    private function renderCheckout(string $step)
    {
        $productTypes = $this->productTypeMap();

        return view('customer.checkout', compact('step', 'productTypes'));
    }

    // Process Place Order (public/desain logic: creates order in 'unpaid' state, shows ticket)
    public function placeOrder(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.options' => 'nullable|array',
            'items.*.notes' => 'nullable|string|max:500',
            'items.*.item_notes' => 'nullable|string|max:500',
        ]);

        $products = Product::with('category')
            ->whereIn('id', collect($request->items)->pluck('product_id'))
            ->get()
            ->keyBy('id');

        // One order, one order number, one payment total — the Cashier/Kitchen
        // split happens only at item level via the department snapshot.
        $order = DB::transaction(function () use ($request, $products) {
            $subtotal = 0;
            $lineItems = [];

            foreach ($request->items as $item) {
                $product = $products->get($item['product_id']);

                if (! $product) {
                    abort(422, 'Produk pada pesanan tidak ditemukan.');
                }

                // Server-side pricing keeps the transaction integrity intact
                // even if the client tampers with unit_price / price.
                $unitPrice = (float) $product->price;
                $quantity = (int) $item['quantity'];
                $lineSubtotal = $unitPrice * $quantity;
                $subtotal += $lineSubtotal;

                $department = $product->category?->type;

                if (! in_array($department, OrderItem::DEPARTMENTS, true)) {
                    abort(422, 'Kategori produk ' . $product->name . ' tidak valid.');
                }

                $lineItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'options_json' => $item['options'] ?? [],
                    'item_notes' => $item['notes'] ?? $item['item_notes'] ?? null,
                    'department' => $department,
                    'status' => OrderItem::STATUS_PENDING,
                    'subtotal' => $lineSubtotal,
                ];
            }

            // Generate clean order number matching design (e.g. ORD-240815-012 or ORD-012)
            $latestId = Order::max('id') ?? 0;
            $orderSeq = sprintf('%03d', ($latestId + 1) % 1000);
            $orderNumber = 'ORD-' . date('ymd') . '-' . $orderSeq;

            $tax = (int) round($subtotal * 0.10);
            $total = $subtotal + $tax;

            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_name' => $request->customer_name ?: 'Pelanggan',
                'table_number' => $request->table_number ?: 'Meja 04',
                'order_type' => $request->order_type ?: 'dine_in',
                'status' => 'unpaid', // Initial state: Belum Dibayar (Menunggu Verifikasi Kasir)
                'payment_status' => 'unpaid',
                'payment_method' => $request->payment_method ?: 'qris',
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'barcode_hash' => $orderNumber,
                'notes' => $request->notes ?? null,
            ]);

            $order->items()->createMany($lineItems);

            return $order;
        });

        session(['last_order_number' => $order->order_number]);

        return response()->json([
            'success' => true,
            'order_number' => $order->order_number,
            'redirect_url' => route('customer.ticket', $order->order_number),
        ]);
    }

    // Screen: Order Barcode / QR Digital (public/desain/order_barcode_qr_digital)
    public function ticket($orderNumber)
    {
        $order = Order::with('items.product')->where('order_number', $orderNumber)->firstOrFail();
        return view('customer.ticket', compact('order'));
    }

    // Screen: Order Status Tracking (public/desain/order_status_tracking)
    public function trackStatus($orderNumber = null)
    {
        if (!$orderNumber) {
            $orderNumber = session('last_order_number');
        }

        if ($orderNumber) {
            $order = Order::with('items.product')->where('order_number', $orderNumber)->first();
        } else {
            // Pick latest active order for display/fallback
            $order = Order::with('items.product')->latest()->first();
        }

        if (!$order) {
            return redirect()->route('customer.catalog');
        }

        return view('customer.status-tracking', compact('order'));
    }

    // API to fetch real-time status update for tracking page
    public function getOrderStatusApi($orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        return response()->json([
            'status' => $order->status,
            'status_label' => $order->status_label,
            'is_paid' => in_array($order->status, ['paid', 'preparing', 'ready', 'completed']),
            'is_preparing' => in_array($order->status, ['preparing', 'ready']),
            'is_completed' => $order->status === 'completed',
            'updated_at' => $order->updated_at->format('H:i A'),
        ]);
    }
}
