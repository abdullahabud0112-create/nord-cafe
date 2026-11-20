<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

/**
 * Shared helpers for the two operational departments (Cashier / Kitchen).
 * Each department may only read and mutate its own slice of an order.
 */
trait ResolvesDepartmentOrders
{
    protected function findDepartmentOrder(string $id, string $department): ?Order
    {
        if (! preg_match('/^[A-Za-z0-9][A-Za-z0-9\-]{0,63}$/', $id)) {
            return null;
        }

        return Order::with([
            'items' => fn ($query) => $query
                ->where('department', $department)
                ->with('product'),
        ])
            ->where(fn ($query) => $query->where('id', $id)->orWhere('order_number', $id))
            ->first();
    }

    protected function canManageDepartment(Request $request, string $department): bool
    {
        // Kiosk mode: no login system, direct access to both stations.
        // Backend validation in applyDepartmentStatus ensures department isolation.
        return true;
    }

    protected function departmentDeniedResponse(Request $request)
    {
        $message = 'Akses ditolak. Anda tidak dapat mengubah item bagian ini.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => $message,
            ], 403);
        }

        return response()->view('errors.403', [], 403);
    }

    /**
     * Apply a status to one item (when $itemId is given) or to every item of the
     * department. Returns a web redirect or JSON payload for the caller.
     */
    protected function applyDepartmentStatus(Request $request, Order $order, string $department, ?int $itemId = null)
    {
        if (! $this->canManageDepartment($request, $department)) {
            return $this->departmentDeniedResponse($request);
        }

        $request->validate([
            'status' => 'required|in:preparing,completed',
        ]);

        $targetStatus = $request->input('status');
        $departmentLabel = $department === OrderItem::DEPARTMENT_KITCHEN ? 'makanan' : 'minuman';

        if ($order->status === 'cancelled') {
            $message = 'Pesanan ' . $order->order_number . ' sudah dibatalkan dan tidak dapat diproses.';
            return $this->departmentRejectedResponse($request, $order, $message, 409);
        }

        if ($order->status === 'completed') {
            $message = 'Pesanan ' . $order->order_number . ' sudah selesai dan tidak dapat diubah lagi.';
            return $this->departmentRejectedResponse($request, $order, $message, 409);
        }

        $query = $order->items()->where('department', $department);

        if ($itemId !== null) {
            $query->where('id', $itemId);
        }

        $items = $query->get();

        if ($items->isEmpty()) {
            $message = $itemId !== null
                ? 'Item yang dimaksud tidak termasuk bagian ' . $departmentLabel . ' pada pesanan ini.'
                : 'Pesanan ini tidak memiliki item ' . $departmentLabel . '.';

            return $this->departmentRejectedResponse($request, $order, $message, 422);
        }

        $changed = 0;

        foreach ($items as $item) {
            if ($item->markAs($targetStatus)) {
                $changed++;
            }
        }

        $order->refresh();
        $statusChanged = $order->syncStatusFromItems();
        $order->refresh();

        if ($changed > 0 || $statusChanged) {
            event(new \App\Events\OrderStatusUpdated($order));
        }

        $message = $changed > 0
            ? 'Status ' . $departmentLabel . ' pesanan ' . $order->order_number . ' diperbarui.'
            : 'Status ' . $departmentLabel . ' pesanan ' . $order->order_number . ' sudah sesuai.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'department' => $department,
                'items_changed' => $changed,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    protected function departmentRejectedResponse(Request $request, Order $order, string $message, int $statusCode)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => $message,
            ], $statusCode);
        }

        return back()->with('error', $message);
    }
}
