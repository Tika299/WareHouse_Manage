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
        Schema::create('internal_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(); // Người lập phiếu
            $table->string('reason_type'); // Người nhà dùng, Dùng kinh doanh, Tặng, Hư hỏng...
            $table->text('note')->nullable();
            $table->decimal('total_cost_value', 15, 2)->default(0); // Tổng giá trị hàng tính theo giá vốn
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internal_exports');
    }
};
