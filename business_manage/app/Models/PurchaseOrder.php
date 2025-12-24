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
}
