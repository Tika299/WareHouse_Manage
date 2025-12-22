<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockLog extends Model
{
    // Khai báo các cột cho phép hệ thống tự động lưu dữ liệu
    protected $fillable = [
        'product_id', 
        'ref_type', 
        'ref_id', 
        'change_qty', 
        'final_qty'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}