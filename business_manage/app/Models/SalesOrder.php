<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    protected $fillable = [
        'customer_id',
        'account_id',
        'shipping_unit_id',
        'shipping_fee',
        'shipping_payor',
        'total_product_amount',
        'total_final_amount',
        'paid_amount',
        'order_type'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function shippingUnit()
    {
        return $this->belongsTo(ShippingUnit::class);
    }
    public function details()
    {
        return $this->hasMany(SalesDetail::class);
    }
    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
