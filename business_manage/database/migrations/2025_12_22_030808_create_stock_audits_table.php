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
        Schema::create('stock_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(); // Người thực hiện kiểm
            $table->text('note')->nullable();
            $table->decimal('total_diff_value', 15, 2)->default(0); // Tổng giá trị chênh lệch (âm là mất, dương là thừa)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_audits');
    }
};
