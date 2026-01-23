<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'supplier_id',
        'account_id',
        'total_product_value',
        'extra_cost',
        'total_final_amount',
        'paid_amount'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function details() // giữ tên details để ám chỉ PurchaseDetail
    {
        return $this->hasMany(PurchaseDetail::class, 'purchase_order_id', 'id');
    }
}
