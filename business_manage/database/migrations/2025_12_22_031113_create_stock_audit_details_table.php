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
        Schema::create('stock_audit_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_audit_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained();
            $table->integer('system_qty'); // Tồn trên máy
            $table->integer('actual_qty'); // Tồn thực tế
            $table->integer('diff_qty');   // Chênh lệch
            $table->decimal('cost_price', 15, 2); // Giá vốn tại thời điểm kiểm
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_audit_details');
    }
};
