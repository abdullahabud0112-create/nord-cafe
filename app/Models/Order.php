<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    /** Statuses that can never be changed again. */
    public const TERMINAL_STATUSES = ['completed', 'cancelled'];

    /** Forward targets allowed from any non-terminal status (unpaid/pending excluded as targets). */
    public const FORWARD_STATUSES = ['paid', 'preparing', 'ready', 'completed', 'cancelled'];

    protected $fillable = [
        'order_number',
        'customer_name',
        'table_number',
        'order_type',
        'status',
        'payment_status',
        'payment_method',
        'subtotal',
        'tax',
        'total',
        'barcode_hash',
        'notes'
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function itemsMinuman()
    {
        return $this->items()->where('department', OrderItem::DEPARTMENT_CASHIER);
    }

    public function itemsMakanan()
    {
        return $this->items()->where('department', OrderItem::DEPARTMENT_KITCHEN);
    }

    public static function normalizeStatus(string $status): string
    {
        return $status === 'pending' ? 'unpaid' : $status;
    }

    /**
     * Aggregate processing state for one department (Cashier / Kitchen).
     * Returns null when the order holds no item for that department, which is
     * how the operational screens avoid rendering empty work lists.
     */
    public function departmentStatus(string $department): ?string
    {
        $statuses = $this->items()
            ->where('department', $department)
            ->pluck('status');

        if ($statuses->isEmpty()) {
            return null;
        }

        if ($statuses->every(fn ($status) => $status === OrderItem::STATUS_COMPLETED)) {
            return OrderItem::STATUS_COMPLETED;
        }

        $started = $statuses->contains(
            fn ($status) => in_array($status, [OrderItem::STATUS_PREPARING, OrderItem::STATUS_COMPLETED], true)
        );

        return $started ? OrderItem::STATUS_PREPARING : OrderItem::STATUS_PENDING;
    }

    public function departmentStatusLabel(string $department): string
    {
        return match ($this->departmentStatus($department)) {
            OrderItem::STATUS_PREPARING => 'Diproses',
            OrderItem::STATUS_COMPLETED => 'Selesai',
            OrderItem::STATUS_PENDING => 'Menunggu',
            default => 'Tidak Ada Item',
        };
    }

    public function departmentStatusBadgeClass(string $department): string
    {
        return match ($this->departmentStatus($department)) {
            OrderItem::STATUS_PREPARING => 'pill-preparing',
            OrderItem::STATUS_COMPLETED => 'pill-done',
            default => 'pill-unpaid',
        };
    }

    public function departmentSubtotal(string $department): float
    {
        return (float) $this->items()
            ->where('department', $department)
            ->sum('subtotal');
    }

    public function departmentItemCount(string $department): int
    {
        return (int) $this->items()
            ->where('department', $department)
            ->sum('quantity');
    }

    public function isPaymentSettled(): bool
    {
        return $this->payment_status === 'paid'
            || in_array($this->status, ['paid', 'preparing', 'ready', 'completed'], true);
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return $this->isPaymentSettled() ? 'Sudah Dibayar' : 'Belum Dibayar';
    }

    public function getPaymentStatusBadgeClassAttribute(): string
    {
        return $this->isPaymentSettled() ? 'pill-paid' : 'pill-unpaid';
    }

    /**
     * Recompute the order-level operational status from its items.
     *
     * Payment and processing are deliberately separate: items may be processed
     * at any time, but the order only moves into preparing/completed once the
     * payment is settled. Cancelled orders are never revived automatically.
     */
    public function syncStatusFromItems(): bool
    {
        if ($this->status === 'cancelled') {
            return false;
        }

        $statuses = $this->items()->pluck('status');

        if ($statuses->isEmpty() || ! $this->isPaymentSettled()) {
            return false;
        }

        $allCompleted = $statuses->every(fn ($status) => $status === OrderItem::STATUS_COMPLETED);

        if ($allCompleted) {
            $newStatus = 'completed';
        } else {
            $started = $statuses->contains(
                fn ($status) => in_array($status, [OrderItem::STATUS_PREPARING, OrderItem::STATUS_COMPLETED], true)
            );
            $newStatus = $started ? 'preparing' : 'paid';
        }

        // Never downgrade a legacy "ready" order back to merely paid.
        if ($newStatus === 'paid' && $this->status === 'ready') {
            return false;
        }

        if ($this->status === $newStatus) {
            return false;
        }

        $this->status = $newStatus;
        $this->save();

        return true;
    }

    public function hasUnfinishedItems(): bool
    {
        return $this->items()
            ->where('status', '!=', OrderItem::STATUS_COMPLETED)
            ->exists();
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, self::TERMINAL_STATUSES, true);
    }

    public function canTransitionTo(string $newStatus): bool
    {
        if ($this->isTerminal()) {
            return false;
        }

        return in_array($newStatus, self::FORWARD_STATUSES, true);
    }

    // Helper to get Indonesian status title matching DESIGN.md
    public function getStatusLabelAttribute()
    {
        switch ($this->status) {
            case 'paid':
                return 'Sudah Dibayar';
            case 'preparing':
            case 'ready':
                return 'Sedang Diproses';
            case 'completed':
                return 'Selesai';
            case 'cancelled':
                return 'Dibatalkan';
            case 'unpaid':
            case 'pending':
            default:
                return 'Belum Dibayar';
        }
    }

    // Helper for status badge styling class
    public function getStatusBadgeClassAttribute()
    {
        switch ($this->status) {
            case 'paid':
                return 'bg-primary text-on-primary';
            case 'preparing':
            case 'ready':
                return 'bg-status-processing text-on-primary';
            case 'completed':
                return 'bg-status-completed/20 text-status-completed border border-status-completed/30';
            case 'cancelled':
                return 'border border-status-unpaid text-status-unpaid opacity-80';
            case 'unpaid':
            case 'pending':
            default:
                return 'border border-status-unpaid text-status-unpaid';
        }
    }
}
