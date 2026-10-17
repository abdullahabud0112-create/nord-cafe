<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->string('customer_name');
            $table->string('table_number')->nullable();
            $table->string('order_type')->default('dine_in'); // dine_in, takeaway
            $table->string('status')->default('pending'); // pending, preparing, ready, completed, cancelled
            $table->string('payment_status')->default('paid'); // paid, pending, failed
            $table->string('payment_method')->default('qris'); // qris, card, cash
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax', 10, 2);
            $table->decimal('total', 10, 2);
            $table->string('barcode_hash')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
