<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['sku', 'name', 'unit', 'cost_price', 'factor_retail', 'factor_wholesale', 'factor_ctv', 'factor_eco_margin', 'factor_eco_fee', 'stock_quantity', 'min_stock'];

    // 1. Giá Lẻ (Vốn * 1.5)
    public function getRetailPriceAttribute()
    {
        return $this->cost_price * $this->factor_retail;
    }

    // 2. Giá Sỉ (Vốn * 1.1)
    public function getWholesalePriceAttribute()
    {
        return $this->cost_price * $this->factor_wholesale;
    }

    // 3. Giá CTV (Vốn * 1.2)
    public function getCtvPriceAttribute()
    {
        return $this->cost_price * $this->factor_ctv;
    }

    // 4. Giá Sàn TMĐT (Vốn * (1 + 0.5) / (1 - 0.3))
    public function getEcommercePriceAttribute()
    {
        $tu_so = 1 + $this->factor_eco_margin;
        $mau_so = 1 - $this->factor_eco_fee;

        if ($mau_so <= 0) return 0; // Tránh lỗi chia cho 0

        return $this->cost_price * ($tu_so / $mau_so);
    }

    public function stockLogs()
    {
        return $this->hasMany(StockLog::class);
    }
}
