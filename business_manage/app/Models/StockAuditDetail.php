<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAuditDetail extends Model
{
    // Khai báo các cột được phép lưu dữ liệu
    protected $fillable = [
        'stock_audit_id', 
        'product_id', 
        'system_qty', 
        'actual_qty', 
        'diff_qty', 
        'cost_price'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}