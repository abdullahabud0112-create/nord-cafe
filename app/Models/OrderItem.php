<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class OrderItem extends Model
{
    use HasFactory;

    /** Processing statuses owned by the operational departments. */
    public const STATUS_PENDING = 'pending';

    public const STATUS_PREPARING = 'preparing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PREPARING,
        self::STATUS_COMPLETED,
    ];

    /** Departments map 1:1 to the category types used by the menu. */
    public const DEPARTMENT_CASHIER = 'minuman';

    public const DEPARTMENT_KITCHEN = 'makanan';

    public const DEPARTMENTS = [
        self::DEPARTMENT_CASHIER,
        self::DEPARTMENT_KITCHEN,
    ];

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
        'options_json',
        'item_notes',
        'department',
        'status',
        'subtotal'
    ];

    protected $casts = [
        'options_json' => 'array',
    ];

    protected static function booted(): void
    {
        // Defence in depth: whenever an item is created without an explicit
        // department, snapshot the product category type. This keeps the
        // Cashier/Kitchen split correct for any code path, not only checkout.
        static::creating(function (self $item): void {
            if (empty($item->department)) {
                $item->department = self::departmentForProduct($item->product_id);
            }

            if (empty($item->status)) {
                $item->status = self::STATUS_PENDING;
            }
        });
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeForDepartment($query, string $department)
    {
        return $query->where('department', $department);
    }

    public static function departmentForProduct(?int $productId): ?string
    {
        if (! $productId) {
            return null;
        }

        $type = Product::query()
            ->with('category:id,type')
            ->find($productId)
            ?->category
            ?->type;

        return in_array($type, self::DEPARTMENTS, true) ? $type : null;
    }

    /**
     * Forward-only item transition. Completed items are final, and a no-op
     * resubmission reports false so callers can count real changes.
     */
    public function markAs(string $status): bool
    {
        if (! in_array($status, [self::STATUS_PREPARING, self::STATUS_COMPLETED], true)) {
            return false;
        }

        if ($this->status === self::STATUS_COMPLETED || $this->status === $status) {
            return false;
        }

        $this->status = $status;
        $this->save();

        return true;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PREPARING => 'Diproses',
            self::STATUS_COMPLETED => 'Selesai',
            default => 'Menunggu',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PREPARING => 'pill-preparing',
            self::STATUS_COMPLETED => 'pill-done',
            default => 'pill-unpaid',
        };
    }

    public function getDepartmentLabelAttribute(): string
    {
        return match ($this->department) {
            self::DEPARTMENT_KITCHEN => 'Makanan',
            self::DEPARTMENT_CASHIER => 'Minuman',
            default => 'Lainnya',
        };
    }

    /**
     * Normalise options_json (legacy keys: ice, sugar, size, topping, ...) into
     * human readable "Label: value" lines for the operational detail screens.
     */
    public function getOptionsDisplayAttribute(): array
    {
        $options = $this->options_json;

        if (! is_array($options)) {
            return [];
        }

        $knownLabels = [
            'ice' => 'Es',
            'sugar' => 'Gula',
            'milk' => 'Susu',
            'size' => 'Ukuran',
            'temperature' => 'Suhu',
            'extra_shot' => 'Extra Shot',
            'topping' => 'Topping',
            'extras' => 'Tambahan',
            'spice' => 'Level Pedas',
            'sauce' => 'Saus',
        ];

        $lines = [];

        foreach ($options as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $nested) {
                    if ($nested === null || $nested === '' || $nested === false) {
                        continue;
                    }

                    $lines[] = is_string($nested) ? $nested : (string) $nested;
                }

                continue;
            }

            if ($value === null || $value === '' || $value === false) {
                continue;
            }

            if (! is_string($key)) {
                $lines[] = (string) $value;

                continue;
            }

            $label = $knownLabels[$key] ?? Str::headline(str_replace('_', ' ', $key));

            $lines[] = $value === true ? $label : $label . ': ' . $value;
        }

        return $lines;
    }

    public function getOptionsSummaryAttribute(): string
    {
        return implode(' · ', $this->options_display);
    }
}
