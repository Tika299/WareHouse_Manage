<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'is_combo',
        'parent_id',
        'category_id',
        'brand_id',
        'sku',
        'name',
        'variant_label',
        'description',
        'unit',
        'cost_price',
        'stock_quantity',
        'min_stock',
        'factor_retail',
        'factor_wholesale',
        'factor_ctv',
        'factor_eco_margin',
        'factor_eco_fee',
        'manual_retail_price',
        'manual_wholesale_price',
        'manual_ctv_price',
        'manual_ecommerce_price',
    ];

    /**
     * Tự động tạo SKU nếu để trống khi tạo mới
     */
    protected static function booted()
    {
        static::creating(function ($product) {
            if (empty($product->sku)) {
                $product->sku = 'SP-' . strtoupper(Str::random(8));
            }
        });
    }

    // =========================================================================
    // QUAN HỆ (RELATIONSHIPS)
    // =========================================================================

    /**
     * Thuộc về Ngành hàng (Category)
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id')->withDefault([
            'name' => 'Chưa phân loại'
        ]);
    }

    /**
     * Thuộc về Thương hiệu (Brand)
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id')->withDefault([
            'name' => 'Không có thương hiệu'
        ]);
    }

    /**
     * Danh sách các biến thể con (Nếu đây là sản phẩm cha)
     */
    public function variants()
    {
        return $this->hasMany(Product::class, 'parent_id', 'id');
    }

    /**
     * Sản phẩm cha (Nếu đây là biến thể con)
     */
    public function parent()
    {
        return $this->belongsTo(Product::class, 'parent_id');
    }

    // =========================================================================
    // LOGIC TÍNH GIÁ BÁN (ACCESSORS)
    // =========================================================================

    /**
     * Lấy giá bán lẻ (Ưu tiên giá ấn định thủ công)
     * Gọi bằng: $product->retail_price
     */
    public function getRetailPriceAttribute()
    {
        if ($this->manual_retail_price > 0) {
            return (float) $this->manual_retail_price;
        }
        return (float) ($this->cost_price * ($this->factor_retail ?: 1.5));
    }

    /**
     * Lấy giá bán sỉ (Ưu tiên giá ấn định thủ công)
     * Gọi bằng: $product->wholesale_price
     */
    public function getWholesalePriceAttribute()
    {
        if ($this->manual_wholesale_price > 0) {
            return (float) $this->manual_wholesale_price;
        }
        return (float) ($this->cost_price * ($this->factor_wholesale ?: 1.1));
    }

    /**
     * Lấy giá CTV (Ưu tiên giá ấn định thủ công)
     */
    public function getCtvPriceAttribute()
    {
        if ($this->manual_ctv_price > 0) {
            return (float) $this->manual_ctv_price;
        }
        return (float) ($this->cost_price * ($this->factor_ctv ?: 1.2));
    }

    /**
     * Lấy giá sàn Ecommerce (Công thức: Vốn * (1 + Lãi sàn) / (1 - Phí sàn))
     */
    public function getEcommercePriceAttribute()
    {
        if ($this->manual_ecommerce_price > 0) {
            return (float) $this->manual_ecommerce_price;
        }

        $cost = $this->cost_price;
        $margin = $this->factor_eco_margin ?: 0.5;
        $fee = $this->factor_eco_fee ?: 0.3;

        $denominator = (1 - $fee);
        if ($denominator <= 0) return 0;

        return (float) ($cost * (1 + $margin) / $denominator);
    }

    // =========================================================================
    // LOGIC KHO HÀNG (HELPERS)
    // =========================================================================

    /**
     * Kiểm tra sản phẩm có sắp hết hàng không
     */
    public function getIsLowStockAttribute()
    {
        return $this->stock_quantity > 0 && $this->stock_quantity <= $this->min_stock;
    }

    /**
     * Kiểm tra sản phẩm còn hàng không (Dùng cho việc lên đơn)
     */
    public function getIsAvailableAttribute()
    {
        if ($this->variants()->exists()) {
            return $this->variants()->sum('stock_quantity') > 0;
        }
        return $this->stock_quantity > 0;
    }

    /**
     * Lấy tổng tồn kho (Nếu là cha thì cộng dồn các con, nếu là đơn lẻ thì lấy chính nó)
     */
    public function getTotalStockAttribute()
    {
        if ($this->variants()->exists()) {
            return $this->variants()->sum('stock_quantity');
        }
        return $this->stock_quantity;
    }

    public function comboItems()
    {
        return $this->hasMany(ComboItem::class, 'combo_id');
    }

    /**
     * Accessor tự động tính tồn kho
     * Khi bạn gọi $product->stock_quantity, hàm này sẽ chạy
     */
    public function getStockQuantityAttribute($value)
    {
        // Kiểm tra chính xác cột is_combo trong DB
        if ($this->attributes['is_combo'] == 1) {
            // Lấy các thành phần, nếu không có thành phần nào thì trả về 0
            $items = $this->comboItems()->with('component')->get();

            if ($items->isEmpty()) {
                return 0;
            }

            $possibilities = [];
            foreach ($items as $item) {
                // Nếu thành phần bị xóa hoặc không tìm thấy
                if (!$item->component) continue;

                $stockOfComponent = $item->component->stock_quantity;
                $requiredQty = $item->quantity;

                if ($requiredQty <= 0) continue;

                $possibilities[] = floor($stockOfComponent / $requiredQty);
            }

            return count($possibilities) > 0 ? (int) min($possibilities) : 0;
        }

        // Nếu không phải combo, trả về giá trị thực (cột stock_quantity)
        return $value;
    }
}
