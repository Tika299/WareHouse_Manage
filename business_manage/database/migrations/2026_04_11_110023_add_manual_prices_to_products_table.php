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
        Schema::table('products', function (Blueprint $table) {
            // Các cột này dùng để nhập giá trực tiếp (ví dụ: muốn bán đúng 500k)
            $table->decimal('manual_retail_price', 15, 2)->nullable();
            $table->decimal('manual_wholesale_price', 15, 2)->nullable();
            $table->decimal('manual_ctv_price', 15, 2)->nullable();
            $table->decimal('manual_ecommerce_price', 15, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            //
        });
    }
};
