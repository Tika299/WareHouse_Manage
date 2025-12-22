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
        Schema::create('credit_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('target_type', ['customer', 'supplier']);
            $table->unsignedBigInteger('target_id');
            $table->enum('ref_type', ['order', 'voucher', 'barter']);
            $table->unsignedBigInteger('ref_id'); // ID của sales_orders hoặc cash_vouchers
            $table->decimal('change_amount', 15, 2); // + là tăng nợ, - là giảm nợ
            $table->decimal('new_balance', 15, 2);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_logs');
    }
};
