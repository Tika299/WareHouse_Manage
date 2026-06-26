<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseReturnDetail extends Model
{
    protected $fillable = [
        'purchase_return_id',
        'product_id',
        'quantity',
        'return_price',
        'return_value',
        'reason',
    ];

    protected $casts = [
        'return_price' => 'decimal:2',
        'return_value' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function purchaseReturn()
    {
        return $this->belongsTo(PurchaseReturn::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
