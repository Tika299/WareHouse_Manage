<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesDetail extends Model
{
    // Thêm các trường này để hệ thống được phép lưu chi tiết đơn hàng
    protected $fillable = [
        'sales_order_id', 
        'product_id', 
        'quantity', 
        'unit_price', 
        'cost_price_at_sale'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
