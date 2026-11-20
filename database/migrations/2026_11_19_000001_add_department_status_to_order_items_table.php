<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // Snapshot of the product's category type at order time. Kept on the
            // item so later menu re-categorisation never moves historical items
            // between Cashier and Kitchen.
            $table->string('department')->nullable()->after('item_notes');
            $table->string('status')->default('pending')->after('department');
            $table->index(['order_id', 'status']);
            $table->index(['order_id', 'department']);
        });

        // Backfill item processing status from the parent order so legacy
        // orders keep their existing meaning (completed stays completed).
        DB::table('order_items')
            ->whereIn('order_id', DB::table('orders')->where('status', 'completed')->select('id'))
            ->update(['status' => 'completed']);

        DB::table('order_items')
            ->whereIn('order_id', DB::table('orders')->whereIn('status', ['preparing', 'ready'])->select('id'))
            ->update(['status' => 'preparing']);

        // Backfill the department snapshot from the current product category.
        DB::table('order_items')->orderBy('id')->chunkById(200, function ($items) {
            $types = DB::table('products')
                ->join('categories', 'categories.id', '=', 'products.category_id')
                ->whereIn('products.id', $items->pluck('product_id')->unique()->all())
                ->pluck('categories.type', 'products.id');

            foreach ($items as $item) {
                $department = $types[$item->product_id] ?? null;

                if (in_array($department, ['minuman', 'makanan'], true)) {
                    DB::table('order_items')
                        ->where('id', $item->id)
                        ->update(['department' => $department]);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['order_id', 'status']);
            $table->dropIndex(['order_id', 'department']);
            $table->dropColumn(['department', 'status']);
        });
    }
};
