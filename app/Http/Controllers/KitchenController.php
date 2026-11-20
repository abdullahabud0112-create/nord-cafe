<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesDepartmentOrders;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class KitchenController extends Controller
{
    use ResolvesDepartmentOrders;

    private const DEPARTMENT = OrderItem::DEPARTMENT_KITCHEN;

    // Screen: Kitchen incoming orders (food only)
    public function index(Request $request)
    {
        $statusFilter = $request->get('status', 'active');

        $query = Order::query()
            ->where('status', '!=', 'cancelled')
            ->whereHas('items', fn ($items) => $items->where('department', self::DEPARTMENT))
            ->with([
                'items' => fn ($items) => $items
                    ->where('department', self::DEPARTMENT)
                    ->with('product'),
            ]);

        if ($statusFilter === 'completed') {
            $query->whereDoesntHave('items', fn ($items) => $items
                ->where('department', self::DEPARTMENT)
                ->whereIn('status', [OrderItem::STATUS_PENDING, OrderItem::STATUS_PREPARING]));
        } else {
            $query->whereHas('items', fn ($items) => $items
                ->where('department', self::DEPARTMENT)
                ->whereIn('status', [OrderItem::STATUS_PENDING, OrderItem::STATUS_PREPARING]));
        }

        $orders = $statusFilter === 'completed'
            ? $query->latest('updated_at')->get()
            : $query->oldest()->get();

        $stats = [
            'pending' => (int) OrderItem::where('department', self::DEPARTMENT)
                ->where('status', OrderItem::STATUS_PENDING)
                ->sum('quantity'),
            'preparing' => (int) OrderItem::where('department', self::DEPARTMENT)
                ->where('status', OrderItem::STATUS_PREPARING)
                ->sum('quantity'),
            'completed' => (int) OrderItem::where('department', self::DEPARTMENT)
                ->where('status', OrderItem::STATUS_COMPLETED)
                ->sum('quantity'),
        ];

        $feedSignature = $this->feedSignature();

        return view('kitchen.index', compact('orders', 'statusFilter', 'stats', 'feedSignature'));
    }

    // Screen: Kitchen order detail (food only)
    public function show($id)
    {
        $order = $this->findDepartmentOrder((string) $id, self::DEPARTMENT);

        if (! $order) {
            return redirect()->route('kitchen.index')
                ->with('error', 'Pesanan yang dimaksud tidak ditemukan.');
        }

        return view('kitchen.show', compact('order'));
    }

    // Action: update every food item of an order
    public function updateStatus(Request $request, $id)
    {
        $order = $this->findDepartmentOrder((string) $id, self::DEPARTMENT);

        if (! $order) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'status' => false, 'message' => 'Pesanan tidak ditemukan.'], 404)
                : redirect()->route('kitchen.index')->with('error', 'Pesanan yang dimaksud tidak ditemukan.');
        }

        return $this->applyDepartmentStatus($request, $order, self::DEPARTMENT);
    }

    // Action: update a single food item
    public function updateItemStatus(Request $request, $id, $itemId)
    {
        $order = $this->findDepartmentOrder((string) $id, self::DEPARTMENT);

        if (! $order) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'status' => false, 'message' => 'Pesanan tidak ditemukan.'], 404)
                : redirect()->route('kitchen.index')->with('error', 'Pesanan yang dimaksud tidak ditemukan.');
        }

        return $this->applyDepartmentStatus($request, $order, self::DEPARTMENT, (int) $itemId);
    }

    // Lightweight polling endpoint so new food orders appear without reloading manually.
    public function feed()
    {
        return response()->json([
            'signature' => $this->feedSignature(),
            'pending' => OrderItem::where('department', self::DEPARTMENT)
                ->where('status', OrderItem::STATUS_PENDING)
                ->count(),
            'preparing' => OrderItem::where('department', self::DEPARTMENT)
                ->where('status', OrderItem::STATUS_PREPARING)
                ->count(),
        ]);
    }

    private function feedSignature(): string
    {
        $orders = Order::query()
            ->whereHas('items', fn ($items) => $items->where('department', self::DEPARTMENT))
            ->get(['id', 'status', 'updated_at'])
            ->map(fn (Order $order) => 'o' . $order->id . ':' . $order->status . ':' . optional($order->updated_at)->timestamp);

        $items = OrderItem::query()
            ->where('department', self::DEPARTMENT)
            ->get(['id', 'status', 'updated_at'])
            ->map(fn (OrderItem $item) => 'i' . $item->id . ':' . $item->status . ':' . optional($item->updated_at)->timestamp);

        return md5($orders->concat($items)->implode('|'));
    }
}
