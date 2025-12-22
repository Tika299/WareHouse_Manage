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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->string('unit')->nullable();
            $table->decimal('cost_price', 15, 2)->default(0); // Giá vốn bình quân
            $table->decimal('markup_wholesale', 15, 2)->default(0); // Tiền lãi sỉ cộng thêm
            $table->decimal('markup_retail', 15, 2)->default(0);    // Tiền lãi lẻ cộng thêm
            $table->integer('stock_quantity')->default(0);
            $table->integer('min_stock')->default(5);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
