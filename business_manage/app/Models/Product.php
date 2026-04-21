<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'parent_id', 'product_type', 'variant_label', 'attributes', 'sku', 'name', 
        'description', 'brand', 'unit', 'cost_price', 'factor_retail', 
        'factor_wholesale', 'factor_ctv', 'factor_eco_margin', 'factor_eco_fee',
        'manual_retail_price', 'manual_wholesale_price', 'manual_ctv_price', 
        'manual_ecommerce_price', 'stock_quantity', 'min_stock'
    ];

    protected $casts = [
        'attributes' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($product) {
            // Nếu SKU trống, tự động tạo mã SP0000x
            if (empty($product->sku)) {
                $product->sku = self::generateUniqueSku();
            }
        });
    }

    private static function generateUniqueSku()
    {
        $prefix = 'SP';
        $lastSku = self::where('sku', 'LIKE', $prefix . '%')->orderBy('sku', 'desc')->first();
        if (!$lastSku) return $prefix . '00001';
        $lastNumber = intval(substr($lastSku->sku, strlen($prefix)));
        return $prefix . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
    }

    /* 
    |--------------------------------------------------------------------------
    | LOGIC TÍNH GIÁ (ACCESSORS)
    |--------------------------------------------------------------------------
    | Ưu tiên giá Manual từ Excel. Nếu trống (0 hoặc NULL) mới tính theo Hệ Số.
    */

    public function getRetailPriceAttribute() {
        return ($this->manual_retail_price > 0) ? $this->manual_retail_price : ($this->cost_price * ($this->factor_retail ?? 1.5));
    }

    public function getWholesalePriceAttribute() {
        return ($this->manual_wholesale_price > 0) ? $this->manual_wholesale_price : ($this->cost_price * ($this->factor_wholesale ?? 1.1));
    }

    public function getCtvPriceAttribute() {
        return ($this->manual_ctv_price > 0) ? $this->manual_ctv_price : ($this->cost_price * ($this->factor_ctv ?? 1.2));
    }

    public function getEcommercePriceAttribute() {
        if ($this->manual_ecommerce_price > 0) return $this->manual_ecommerce_price;
        $tu = 1 + ($this->factor_eco_margin ?? 0.5);
        $mau = 1 - ($this->factor_eco_fee ?? 0.3);
        return ($mau > 0) ? ($this->cost_price * ($tu / $mau)) : 0;
    }

    /* RELATIONSHIPS */
    public function variants() { return $this->hasMany(Product::class, 'parent_id'); }
    public function parent() { return $this->belongsTo(Product::class, 'parent_id'); }
}