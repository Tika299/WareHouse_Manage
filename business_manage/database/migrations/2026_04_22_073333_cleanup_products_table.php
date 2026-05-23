<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            // Xóa các trường không còn dùng tới
            $table->dropColumn(['product_type', 'brand', 'attributes']);

            // Tối ưu hóa: Đảm bảo các trường số không bị âm (unsigned)
            $table->decimal('cost_price', 15, 2)->unsigned()->default(0)->change();
            $table->integer('stock_quantity')->unsigned()->default(0)->change();
            $table->integer('min_stock')->unsigned()->default(5)->change();
        });
    }

    /**
     * Reverse the migrations.
     */


    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('product_type')->nullable();
            $table->string('brand')->nullable();
            $table->json('attributes')->nullable();
        });
    }
};
