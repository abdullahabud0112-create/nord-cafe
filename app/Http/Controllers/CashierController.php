<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\OrderStatusUpdated;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;

class CashierController extends Controller
{
    /**
     * Look up an order by numeric id or order_number (QR / barcode flow).
     * Rejects malformed identifiers up front so bad input can never reach
     * a database query, and never lets firstOrFail() bubble a raw 404.
     */
    private function findOrder(string $id): ?Order
    {
        if (! preg_match('/^[A-Za-z0-9][A-Za-z0-9\-]{0,63}$/', $id)) {
            return null;
        }

        return Order::with('items.product')
            ->where('id', $id)
            ->orWhere('order_number', $id)
            ->first();
    }

    private function orderNotFoundJson()
    {
        return response()->json([
            'success' => false,
            'status' => false,
            'message' => 'Pesanan tidak ditemukan.',
        ], 404);
    }

    private function orderNotFoundWeb()
    {
        return redirect()->route('cashier.incomingOrders')
            ->with('error', 'Pesanan yang dimaksud tidak ditemukan. Mungkin sudah dihapus.');
    }

    private function transitionRejectedJson(string $message)
    {
        return response()->json([
            'success' => false,
            'status' => false,
            'message' => $message,
        ], 409);
    }

    private function transitionRejectedWeb(Order $order, string $message)
    {
        return redirect()->route('cashier.editStatus', $order->id)
            ->with('error', $message);
    }

    // Screen: Cashier Incoming Orders (public/desain/cashier_incoming_orders)
    public function incomingOrders(Request $request)
    {
        $statusFilter = $request->get('status', 'active');
        $query = Order::with('items.product')->latest();

        if ($statusFilter === 'completed') {
            // Riwayat tab (Completed orders)
            $query->where('status', 'completed');
        } elseif ($statusFilter === 'active') {
            // Active orders tab (Sudah Dibayar, Sedang Diproses, Belum Dibayar)
            $query->whereIn('status', ['paid', 'preparing', 'ready', 'unpaid', 'pending']);
        }

        $orders = $query->get();

        $stats = [
            'active' => Order::whereIn('status', ['paid', 'preparing', 'ready', 'unpaid', 'pending'])->count(),
            'paid' => Order::where('status', 'paid')->count(),
            'preparing' => Order::whereIn('status', ['preparing', 'ready'])->count(),
            'completed' => Order::where('status', 'completed')->count(),
            'all' => Order::count(),
        ];

        return view('cashier.incoming-orders', compact('orders', 'statusFilter', 'stats'));
    }

    // Screen: Cashier Order Detail (public/desain/cashier_order_detail)
    public function orderDetail($id)
    {
        $order = $this->findOrder((string) $id);

        if (! $order) {
            return $this->orderNotFoundWeb();
        }

        return view('cashier.order-detail', compact('order'));
    }

    // Screen: Cashier Scan Barcode (public/desain/cashier_scan_barcode)
    public function scanBarcode()
    {
        $recentOrders = Order::latest()->take(5)->get();
        return view('cashier.scan-barcode', compact('recentOrders'));
    }

    // Process Barcode Search / Scan
    public function searchBarcode(Request $request)
    {
        $code = trim($request->get('code'));
        
        // Extract order identifier if scanned code is a full URL
        if (preg_match('/(?:order\/)([A-Za-z0-9\-]+)/', $code, $matches)) {
            $code = $matches[1];
        }

        $order = Order::where('order_number', $code)
            ->orWhere('id', $code)
            ->orWhere('barcode_hash', $code)
            ->orWhere('order_number', 'like', '%' . $code . '%')
            ->first();

        if ($order) {
            return response()->json([
                'found' => true,
                'redirect_url' => route('cashier.editStatus', $order->id),
            ]);
        }

        return response()->json([
            'found' => false,
            'message' => 'Pesanan dengan kode "' . $code . '" tidak ditemukan.',
        ]);
    }

    // Action: Confirm Payment (when clicking "Konfirmasi / Scan" on order-detail)
    public function confirmPayment(Request $request, $id)
    {
        $order = $this->findOrder((string) $id);

        if (! $order) {
            return $request->wantsJson() ? $this->orderNotFoundJson() : $this->orderNotFoundWeb();
        }

        $normalized = Order::normalizeStatus($order->status);

        if ($order->isTerminal()) {
            $message = 'Pesanan ' . $order->order_number . ' sudah ' . $order->status_label . ' dan tidak dapat diubah lagi.';
            return $request->wantsJson() ? $this->transitionRejectedJson($message) : $this->transitionRejectedWeb($order, $message);
        }

        if (in_array($normalized, ['preparing', 'ready'], true)) {
            $message = 'Pesanan ' . $order->order_number . ' sedang diproses dan tidak dapat dikonfirmasi pembayarannya lagi.';
            return $request->wantsJson() ? $this->transitionRejectedJson($message) : $this->transitionRejectedWeb($order, $message);
        }

        if ($normalized === 'paid') {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pembayaran pesanan ' . $order->order_number . ' sudah terkonfirmasi.',
                    'status' => $order->status,
                    'redirect_url' => route('cashier.incomingOrders'),
                ]);
            }

            return redirect()->route('cashier.incomingOrders')->with('success', 'Pembayaran pesanan ' . $order->order_number . ' sudah terkonfirmasi.');
        }

        $order->status = 'paid';
        $order->payment_status = 'paid';
        $order->save();

        event(new OrderStatusUpdated($order));

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pembayaran pesanan ' . $order->order_number . ' telah dikonfirmasi (SUDAH DIBAYAR).',
                'status' => $order->status,
                'redirect_url' => route('cashier.incomingOrders'),
            ]);
        }

        return redirect()->route('cashier.incomingOrders')->with('success', 'Pesanan ' . $order->order_number . ' telah dikonfirmasi (SUDAH DIBAYAR).');
    }

    // Screen: Cashier Update Status (public/desain/cashier_update_status)
    public function editStatus($id)
    {
        $order = $this->findOrder((string) $id);

        if (! $order) {
            return $this->orderNotFoundWeb();
        }

        return view('cashier.update-status', compact('order'));
    }

    // Action: Update Status from update-status screen or action buttons
    public function updateStatus(Request $request, $id)
    {
        $order = $this->findOrder((string) $id);

        if (! $order) {
            return $request->wantsJson() ? $this->orderNotFoundJson() : $this->orderNotFoundWeb();
        }

        $request->validate([
            'status' => 'required|in:unpaid,pending,paid,preparing,ready,completed,cancelled',
        ]);

        $newStatus = Order::normalizeStatus($request->status);

        // Terminal statuses (completed / cancelled) are locked forever.
        if ($order->isTerminal()) {
            $message = 'Pesanan ' . $order->order_number . ' sudah ' . $order->status_label . ' dan tidak dapat diubah lagi.';
            return $request->wantsJson() ? $this->transitionRejectedJson($message) : $this->transitionRejectedWeb($order, $message);
        }

        // Idempotent no-op: status unchanged (double click / stale form).
        if ($newStatus === $order->status) {
            $message = 'Status pesanan ' . $order->order_number . ' sudah ' . $order->status_label . '.';
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'status' => $order->status,
                ]);
            }

            return redirect()->route('cashier.incomingOrders')->with('success', $message);
        }

        // Forward-only transitions; resets back to unpaid are rejected.
        if (! $order->canTransitionTo($newStatus)) {
            $message = 'Transisi status dari "' . $order->status_label . '" ke "' . (new Order(['status' => $newStatus]))->status_label . '" tidak diizinkan.';
            return $request->wantsJson() ? $this->transitionRejectedJson($message) : $this->transitionRejectedWeb($order, $message);
        }

        $order->status = $newStatus;
        if (in_array($newStatus, ['paid', 'preparing', 'ready', 'completed'], true)) {
            $order->payment_status = 'paid';
        }
        $order->save();

        event(new OrderStatusUpdated($order));

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Status pesanan ' . $order->order_number . ' berhasil diubah menjadi ' . $order->status_label,
                'status' => $order->status,
            ]);
        }

        return redirect()->route('cashier.incomingOrders')->with('success', 'Status pesanan ' . $order->order_number . ' berhasil diubah menjadi ' . $order->status_label . '.');
    }

    // Clear all completed orders
    public function clearCompleted(Request $request)
    {
        $deletedCount = Order::where('status', 'completed')->delete();

        return redirect()->route('cashier.incomingOrders')->with('success', "Berhasil membersihkan {$deletedCount} pesanan selesai dari sistem.");
    }

    // Delete single order
    public function deleteOrder(Request $request, $id)
    {
        $order = $this->findOrder((string) $id);

        if (! $order) {
            return $request->wantsJson() ? $this->orderNotFoundJson() : $this->orderNotFoundWeb();
        }

        $orderNumber = $order->order_number;
        $order->delete();

        return redirect()->route('cashier.incomingOrders')->with('success', "Pesanan #{$orderNumber} telah dihapus.");
    }

    // Menu Management (CRUD)
    public function menuManager(Request $request)
    {
        $selectedType = $request->get('type', 'all');
        $selectedCategory = $request->get('category', 'all');
        $search = $request->get('search');

        $categories = Category::all();
        $query = Product::with('category');

        if ($selectedType !== 'all') {
            $query->whereHas('category', fn($q) => $q->where('type', $selectedType));
        }

        if ($selectedCategory !== 'all') {
            $query->whereHas('category', fn($q) => $q->where('slug', $selectedCategory));
        }

        if (!empty($search)) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $products = $query->orderBy('category_id')->orderBy('name')->paginate(20)->withQueryString();

        return view('cashier.menu-manager', compact('products', 'categories', 'selectedType', 'selectedCategory', 'search'));
    }

    public function toggleAvailability($id)
    {
        $product = Product::findOrFail($id);
        $product->is_available = !$product->is_available;
        $product->save();

        return back()->with('success', 'Status ketersediaan ' . $product->name . ' diperbarui.');
    }

    public function updateProduct(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'tags' => 'nullable|string|max:255',
        ]);

        $product->update([
            'name' => $request->name,
            'price' => $request->price,
            'category_id' => $request->category_id,
            'tags' => $request->tags,
            'description' => $request->description ?? '',
        ]);

        return back()->with('success', 'Menu ' . $product->name . ' berhasil diperbarui.');
    }

    public function storeProduct(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
        ]);

        $slug = \Illuminate\Support\Str::slug($request->name);
        if (Product::where('slug', $slug)->exists()) {
            $slug .= '-' . time();
        }

        Product::create([
            'name' => $request->name,
            'slug' => $slug,
            'price' => $request->price,
            'category_id' => $request->category_id,
            'tags' => $request->tags ?? 'New',
            'description' => $request->description ?? '',
            'image_url' => $request->image_url ?? 'https://images.unsplash.com/photo-1541167760496-1628856ab772?auto=format&fit=crop&w=600&q=80',
            'is_available' => true,
        ]);

        return back()->with('success', 'Menu baru berhasil ditambahkan.');
    }

    public function deleteProduct($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return back()->with('success', 'Menu berhasil dihapus.');
    }
}
