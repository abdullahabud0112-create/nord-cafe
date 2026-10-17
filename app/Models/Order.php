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

    public static function normalizeStatus(string $status): string
    {
        return $status === 'pending' ? 'unpaid' : $status;
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
