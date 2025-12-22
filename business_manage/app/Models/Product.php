<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['sku', 'name', 'unit', 'cost_price', 'markup_wholesale', 'markup_retail', 'stock_quantity', 'min_stock'];

    // Accessors để lấy giá bán tự động
    public function getRetailPriceAttribute()
    {
        return $this->cost_price + $this->markup_retail;
    }

    public function getWholesalePriceAttribute()
    {
        return $this->cost_price + $this->markup_wholesale;
    }

    public function stockLogs()
    {
        return $this->hasMany(StockLog::class);
    }
}
