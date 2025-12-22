<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('account_id')->constrained();
            $table->foreignId('shipping_unit_id')->constrained();
            $table->decimal('shipping_fee', 15, 2)->default(0);
            $table->enum('shipping_payor', ['customer', 'shop'])->default('customer');
            $table->decimal('total_product_amount', 15, 2);
            $table->decimal('total_final_amount', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->enum('order_type', ['sale', 'barter'])->default('sale');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
